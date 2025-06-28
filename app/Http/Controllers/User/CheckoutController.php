<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Get cart items
        $cartItems = Cart::with(['menuItem.category'])
            ->where('user_id', $user->id)
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('user.cart.index')
                ->with('error', 'Keranjang Anda kosong. Silakan tambahkan item terlebih dahulu.');
        }

        // Check availability
        $unavailableItems = $cartItems->filter(function ($item) {
            return !$item->menuItem->is_available;
        });

        if ($unavailableItems->isNotEmpty()) {
            return redirect()->route('user.cart.index')
                ->with('error', 'Beberapa item di keranjang Anda tidak tersedia. Silakan hapus item tersebut.');
        }

        // Get user addresses
        $addresses = UserAddress::where('user_id', $user->id)
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate totals
        $subtotal = $cartItems->sum(function ($item) {
            return $item->quantity * $item->price;
        });

        $deliveryFee = $subtotal >= 100000 ? 0 : 15000; // Free delivery for orders >= 100k
        $taxAmount = $subtotal * 0.11; // 11% tax
        $totalAmount = $subtotal + $deliveryFee + $taxAmount;

        // Available delivery time slots
        $timeSlots = [
            '09:00-12:00' => 'Pagi (09:00 - 12:00)',
            '12:00-15:00' => 'Siang (12:00 - 15:00)',
            '15:00-18:00' => 'Sore (15:00 - 18:00)',
            '18:00-21:00' => 'Malam (18:00 - 21:00)',
        ];

        return Inertia::render('User/Checkout/Index', [
            'cartItems' => $cartItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'menu_item' => [
                        'id' => $item->menuItem->id,
                        'name' => $item->menuItem->name,
                        'image' => $item->menuItem->image,
                        'category' => $item->menuItem->category->name ?? 'Uncategorized',
                        'calories' => $item->menuItem->calories,
                        'protein' => $item->menuItem->protein,
                    ],
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'subtotal' => $item->quantity * $item->price,
                ];
            }),
            'addresses' => $addresses->map(function ($address) {
                return [
                    'id' => $address->id,
                    'label' => $address->label,
                    'recipient_name' => $address->recipient_name,
                    'phone_number' => $address->phone_number,
                    'full_address' => $address->address_line_1 .
                        ($address->address_line_2 ? ', ' . $address->address_line_2 : '') .
                        ', ' . $address->city . ', ' . $address->province . ' ' . $address->postal_code,
                    'delivery_instructions' => $address->delivery_instructions,
                    'is_default' => $address->is_default,
                ];
            }),
            'summary' => [
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'free_delivery_threshold' => 100000,
            ],
            'timeSlots' => $timeSlots,
            'minDeliveryDate' => now()->addDay()->format('Y-m-d'), // Next day minimum
            'maxDeliveryDate' => now()->addDays(7)->format('Y-m-d'), // Max 7 days ahead
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'delivery_address_id' => ['required', 'exists:user_addresses,id'],
            'delivery_date' => ['required', 'date', 'after:today', 'before_or_equal:' . now()->addDays(7)->format('Y-m-d')],
            'delivery_time_slot' => ['required', Rule::in(['09:00-12:00', '12:00-15:00', '15:00-18:00', '18:00-21:00'])],
            'special_instructions' => ['nullable', 'string', 'max:500'],
        ]);

        // Verify address belongs to user
        $address = UserAddress::where('id', $request->delivery_address_id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Get cart items
        $cartItems = Cart::with('menuItem')
            ->where('user_id', $user->id)
            ->get();

        if ($cartItems->isEmpty()) {
            return back()->withErrors(['cart' => 'Keranjang Anda kosong.']);
        }

        // Check availability again
        $unavailableItems = $cartItems->filter(function ($item) {
            return !$item->menuItem->is_available;
        });

        if ($unavailableItems->isNotEmpty()) {
            return back()->withErrors(['availability' => 'Beberapa item tidak tersedia.']);
        }

        try {
            DB::beginTransaction();

            // Calculate totals
            $subtotal = $cartItems->sum(function ($item) {
                return $item->quantity * $item->price;
            });

            $deliveryFee = $subtotal >= 100000 ? 0 : 15000;
            $taxAmount = $subtotal * 0.11;
            $totalAmount = $subtotal + $deliveryFee + $taxAmount;

            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'delivery_address_id' => $address->id,
                'delivery_date' => $request->delivery_date,
                'delivery_time_slot' => $request->delivery_time_slot,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'delivery_fee' => $deliveryFee,
                'total_amount' => $totalAmount,
                'special_instructions' => $request->special_instructions,
                'status' => 'pending',
                'payment_status' => 'pending',
            ]);

            // Create order items
            foreach ($cartItems as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $cartItem->menu_item_id,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $cartItem->price,
                    'total_price' => $cartItem->quantity * $cartItem->price,
                ]);
            }

            // Clear cart
            Cart::where('user_id', $user->id)->delete();

            DB::commit();

            return redirect()->route('user.orders.show', $order)
                ->with('success', 'Pesanan berhasil dibuat! Order #' . $order->order_number);
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Terjadi kesalahan saat memproses pesanan. Silakan coba lagi.']);
        }
    }
}
