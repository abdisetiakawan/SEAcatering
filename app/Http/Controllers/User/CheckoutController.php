<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $cartRecords = Cart::with('menuItem')
            ->where('user_id', $user->id)
            ->get();

        if ($cartRecords->isEmpty()) {
            return redirect()->route('user.cart.index')
                ->with('error', 'Keranjang Anda kosong');
        }

        $cartItems = $cartRecords->map(function ($cart) {
            $menuItem = $cart->menuItem;
            $isAvailable = $menuItem && $menuItem->is_available;

            $menuItemData = [
                'id' => $menuItem ? $menuItem->id : null,
                'name' => $menuItem ? $menuItem->name : 'Item tidak tersedia',
                'image' => $menuItem ? $menuItem->image : null,
                'category' => $menuItem ? $menuItem->category : '-',
                'calories' => $menuItem ? $menuItem->calories : 0,
                'protein' => $menuItem ? $menuItem->protein : 0,
                'is_available' => $isAvailable,
            ];

            return [
                'id' => $cart->id,
                'menu_item' => $menuItemData,
                'quantity' => $cart->quantity,
                'price' => (float) $cart->unit_price,
                'subtotal' => (float) $cart->total_price,
                'is_available' => $isAvailable,
                'unavailable_reason' => ! $menuItem
                    ? 'Menu ini sudah tidak tersedia.'
                    : ($isAvailable ? null : 'Menu ini sedang tidak tersedia saat ini.'),
            ];
        });

        $availableRecords = $cartRecords->filter(function ($cart) {
            return $cart->menuItem && $cart->menuItem->is_available;
        });

        $hasUnavailableItems = $cartRecords->contains(function ($cart) {
            return ! $cart->menuItem || ! $cart->menuItem->is_available;
        });

        $unavailableItemCount = $cartRecords->count() - $availableRecords->count();

        $subtotal = $availableRecords->sum('total_price');
        $freeDeliveryThreshold = 100000; // Rp 100,000
        $deliveryFee = $subtotal >= $freeDeliveryThreshold ? 0 : 15000; // Rp 15,000
        $taxRate = 0.11; // 11%
        $taxAmount = $subtotal * $taxRate;
        $totalAmount = $subtotal + $deliveryFee + $taxAmount;

        $summary = [
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'free_delivery_threshold' => $freeDeliveryThreshold,
        ];

        $addresses = UserAddress::where('user_id', $user->id)
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($address) {
                return [
                    'id' => $address->id,
                    'label' => $address->label,
                    'recipient_name' => $address->recipient_name,
                    'phone_number' => $address->phone_number,
                    'full_address' => $address->full_address,
                    'delivery_instructions' => $address->delivery_instructions,
                    'is_default' => $address->is_default,
                ];
            });

        $paymentMethods = [
            'bank_transfer' => [
                'name' => 'Transfer Bank',
                'description' => 'Transfer ke rekening bank kami',
                'icon' => 'building-2',
                'fee' => 0,
            ],
            'e_wallet' => [
                'name' => 'E-Wallet',
                'description' => 'Bayar dengan GoPay, OVO, DANA',
                'icon' => 'smartphone',
                'fee' => 2500,
            ],
            'credit_card' => [
                'name' => 'Kartu Kredit',
                'description' => 'Visa, Mastercard, JCB',
                'icon' => 'credit-card',
                'fee' => 5000,
            ],
            'cash' => [
                'name' => 'Bayar di Tempat',
                'description' => 'Bayar saat makanan diantar',
                'icon' => 'banknote',
                'fee' => 0,
            ],
        ];

        $timeSlots = [
            '07:00-09:00' => 'Pagi (07:00 - 09:00)',
            '12:00-14:00' => 'Siang (12:00 - 14:00)',
            '18:00-20:00' => 'Malam (18:00 - 20:00)',
        ];

        $minDeliveryDate = now()->addDay()->format('Y-m-d');
        $maxDeliveryDate = now()->addDays(7)->format('Y-m-d');

        return Inertia::render('User/Checkout/Index', [
            'cartItems' => $cartItems,
            'addresses' => $addresses,
            'summary' => $summary,
            'paymentMethods' => $paymentMethods,
            'timeSlots' => $timeSlots,
            'minDeliveryDate' => $minDeliveryDate,
            'maxDeliveryDate' => $maxDeliveryDate,
            'hasUnavailableItems' => $hasUnavailableItems,
            'unavailableItemCount' => $unavailableItemCount,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'delivery_address_id' => 'required|exists:user_addresses,id',
            'delivery_date' => 'required|date|after:today|before:' . now()->addDays(8)->format('Y-m-d'),
            'delivery_time_slot' => 'required|in:07:00-09:00,12:00-14:00,18:00-20:00',
            'payment_method' => 'required|in:bank_transfer,e_wallet,credit_card,cash',
            'special_instructions' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();

        $address = UserAddress::where('id', $validated['delivery_address_id'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        $cartItems = Cart::with('menuItem')
            ->where('user_id', $user->id)
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('user.cart.index')
                ->with('error', 'Keranjang Anda kosong');
        }

        $unavailableItems = $cartItems->filter(function ($cartItem) {
            return ! $cartItem->menuItem || ! $cartItem->menuItem->is_available;
        });

        if ($unavailableItems->isNotEmpty()) {
            throw ValidationException::withMessages([
                'cart' => 'Beberapa item di keranjang Anda tidak tersedia. Silakan kembali ke keranjang untuk menghapus atau mengganti item tersebut.',
            ]);
        }

        DB::beginTransaction();

        try {
            $subtotal = $cartItems->sum('total_price');
            $freeDeliveryThreshold = 100000;
            $deliveryFee = $subtotal >= $freeDeliveryThreshold ? 0 : 15000;
            $taxRate = 0.11;
            $taxAmount = $subtotal * $taxRate;
            $totalAmount = $subtotal + $deliveryFee + $taxAmount;

            $order = Order::create([
                'user_id' => $user->id,
                'subscription_id' => null,
                'order_type' => 'one_time',
                'delivery_address_id' => $validated['delivery_address_id'],
                'delivery_date' => $validated['delivery_date'],
                'delivery_time_slot' => $validated['delivery_time_slot'],
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'delivery_fee' => $deliveryFee,
                'total_amount' => $totalAmount,
                'special_instructions' => $validated['special_instructions'],
                'status' => 'pending',
                'payment_status' => 'unpaid',
            ]);

            foreach ($cartItems as $cartItem) {
                $unitPrice = $cartItem->unit_price;
                $quantity = $cartItem->quantity;

                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $cartItem->menu_item_id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $unitPrice * $quantity,
                ]);
            }

            Payment::create([
                'order_id' => $order->id,
                'subscription_id' => null,
                'amount' => $totalAmount,
                'payment_method' => $validated['payment_method'],
                'status' => 'pending',
                'payment_date' => null,
                'notes' => 'Payment created automatically',
            ]);

            Cart::where('user_id', $user->id)->delete();

            DB::commit();

            return redirect()->route('user.orders.show', $order->id)
                ->with('success', 'Pesanan berhasil dibuat! Silakan lakukan pembayaran.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat membuat pesanan. Silakan coba lagi.');
        }
    }
}

