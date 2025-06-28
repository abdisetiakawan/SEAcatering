<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Get cart items
        $cartItems = Cart::with('menuItem')
            ->where('user_id', $user->id)
            ->get()
            ->map(function ($cart) {
                return [
                    'id' => $cart->id,
                    'menu_item' => [
                        'id' => $cart->menuItem->id,
                        'name' => $cart->menuItem->name,
                        'image' => $cart->menuItem->image,
                        'category' => $cart->menuItem->category,
                        'calories' => $cart->menuItem->calories,
                        'protein' => $cart->menuItem->protein,
                    ],
                    'quantity' => $cart->quantity,
                    'price' => $cart->price,
                    'subtotal' => $cart->subtotal,
                ];
            });

        if ($cartItems->isEmpty()) {
            return redirect()->route('user.cart.index')
                ->with('error', 'Keranjang Anda kosong');
        }

        // Get user addresses
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

        // Calculate summary
        $subtotal = $cartItems->sum('subtotal');
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

        // Time slots
        $timeSlots = [
            '07:00-09:00' => 'Pagi (07:00 - 09:00)',
            '12:00-14:00' => 'Siang (12:00 - 14:00)',
            '18:00-20:00' => 'Malam (18:00 - 20:00)',
        ];

        // Date constraints
        $minDeliveryDate = now()->addDay()->format('Y-m-d'); // Tomorrow
        $maxDeliveryDate = now()->addDays(7)->format('Y-m-d'); // 1 week from now

        return Inertia::render('User/Checkout/Index', [
            'cartItems' => $cartItems,
            'addresses' => $addresses,
            'summary' => $summary,
            'timeSlots' => $timeSlots,
            'minDeliveryDate' => $minDeliveryDate,
            'maxDeliveryDate' => $maxDeliveryDate,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'delivery_address_id' => 'required|exists:user_addresses,id',
            'delivery_date' => 'required|date|after:today|before:' . now()->addDays(8)->format('Y-m-d'),
            'delivery_time_slot' => 'required|in:07:00-09:00,12:00-14:00,18:00-20:00',
            'special_instructions' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();

        // Verify address belongs to user
        $address = UserAddress::where('id', $validated['delivery_address_id'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Get cart items
        $cartItems = Cart::with('menuItem')
            ->where('user_id', $user->id)
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('user.cart.index')
                ->with('error', 'Keranjang Anda kosong');
        }

        DB::beginTransaction();

        try {
            // Calculate totals
            $subtotal = $cartItems->sum('subtotal');
            $freeDeliveryThreshold = 100000;
            $deliveryFee = $subtotal >= $freeDeliveryThreshold ? 0 : 15000;
            $taxRate = 0.11;
            $taxAmount = $subtotal * $taxRate;
            $totalAmount = $subtotal + $deliveryFee + $taxAmount;

            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'subscription_id' => null, // This is a direct order
                'order_type' => 'direct',
                'delivery_address_id' => $validated['delivery_address_id'],
                'delivery_date' => $validated['delivery_date'],
                'delivery_time_slot' => $validated['delivery_time_slot'],
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'delivery_fee' => $deliveryFee,
                'total_amount' => $totalAmount,
                'special_instructions' => $validated['special_instructions'],
                'status' => 'pending',
                'payment_status' => 'pending',
            ]);

            // Create order items
            foreach ($cartItems as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $cartItem->menu_item_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->price,
                    'total_price' => $cartItem->subtotal,
                ]);
            }

            // Clear cart
            Cart::where('user_id', $user->id)->delete();

            DB::commit();

            return redirect()->route('user.orders.show', $order->id)
                ->with('success', 'Pesanan berhasil dibuat! Silakan lakukan pembayaran.');
        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat membuat pesanan. Silakan coba lagi.');
        }
    }
}
