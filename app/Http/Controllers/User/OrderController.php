<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\MenuItem;
use App\Models\UserAddress;
use App\Models\Cart;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Order::with(['orderItems.menuItem', 'delivery.driver', 'payment', 'deliveryAddress'])
            ->where('user_id', $user->id);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by order type
        if ($request->filled('order_type')) {
            $query->where('order_type', $request->order_type);
        }

        // Search by order number
        if ($request->filled('search')) {
            $query->where('order_number', 'like', '%' . $request->search . '%');
        }

        // Date range filter
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $orders = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->through(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'order_type' => $order->order_type,
                    'order_source' => $order->order_source,
                    'delivery_date' => $order->delivery_date?->format('Y-m-d'),
                    'delivery_time' => $order->delivery_time_slot,
                    'subtotal' => $order->subtotal,
                    'tax_amount' => $order->tax_amount,
                    'delivery_fee' => $order->delivery_fee,
                    'total_amount' => $order->total_amount,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                    'special_instructions' => $order->special_instructions,
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                    'can_be_cancelled' => $order->can_be_cancelled,
                    'can_pay' => $order->payment_status === 'unpaid',
                    'order_items' => $order->orderItems->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'quantity' => $item->quantity,
                            'unit_price' => $item->unit_price,
                            'total_price' => $item->total_price,
                            'menu_item' => [
                                'id' => $item->menuItem->id,
                                'name' => $item->menuItem->name,
                                'image' => $item->menuItem->image,
                            ]
                        ];
                    }),
                    'delivery_address' => $order->deliveryAddress ? [
                        'id' => $order->deliveryAddress->id,
                        'recipient_name' => $order->deliveryAddress->recipient_name,
                        'phone_number' => $order->deliveryAddress->phone_number,
                        'address_line_1' => $order->deliveryAddress->address_line_1,
                        'address_line_2' => $order->deliveryAddress->address_line_2,
                        'city' => $order->deliveryAddress->city,
                        'province' => $order->deliveryAddress->province,
                        'postal_code' => $order->deliveryAddress->postal_code,
                        'full_address' => $order->deliveryAddress->full_address,
                        'delivery_instructions' => $order->deliveryAddress->delivery_instructions,
                    ] : null,
                    'payment' => $order->payment ? [
                        'id' => $order->payment->id,
                        'payment_number' => $order->payment->payment_number,
                        'amount' => $order->payment->amount,
                        'status' => $order->payment->status,
                        'payment_method' => $order->payment->payment_method,
                        'payment_date' => $order->payment->payment_date?->format('Y-m-d H:i:s'),
                        'transaction_id' => $order->payment->transaction_id,
                    ] : null,
                ];
            });

        // Calculate stats
        $stats = [
            'pending' => Order::where('user_id', $user->id)->where('status', 'pending')->count(),
            'preparing' => Order::where('user_id', $user->id)->whereIn('status', ['confirmed', 'preparing'])->count(),
            'out_for_delivery' => Order::where('user_id', $user->id)->where('status', 'out_for_delivery')->count(),
            'delivered' => Order::where('user_id', $user->id)->where('status', 'delivered')->count(),
        ];

        return Inertia::render('User/Orders/Index', [
            'orders' => $orders,
            'stats' => $stats,
            'filters' => $request->only(['status', 'order_type', 'search', 'from_date', 'to_date']),
            'statusOptions' => [
                'pending' => 'Pending',
                'confirmed' => 'Confirmed',
                'preparing' => 'Preparing',
                'ready' => 'Ready',
                'out_for_delivery' => 'Out for Delivery',
                'delivered' => 'Delivered',
                'cancelled' => 'Cancelled'
            ],
            'orderTypeOptions' => [
                'one_time' => 'Direct Order',
                'subscription' => 'Subscription Order'
            ]
        ]);
    }

    public function show(Order $order)
    {
        // Ensure user can only view their own orders
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load([
            'orderItems.menuItem',
            'delivery.driver',
            'payment',
            'deliveryAddress'
        ]);

        return Inertia::render('User/Orders/Show', [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'order_type' => $order->order_type,
                'delivery_date' => $order->delivery_date?->format('Y-m-d'),
                'delivery_time_slot' => $order->delivery_time_slot,
                'subtotal' => $order->subtotal,
                'tax_amount' => $order->tax_amount,
                'delivery_fee' => $order->delivery_fee,
                'total_amount' => $order->total_amount,
                'special_instructions' => $order->special_instructions,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'can_be_cancelled' => $order->can_be_cancelled,
                'can_pay' => $order->payment_status === 'unpaid',
                'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                'delivery_address' => $order->deliveryAddress ? [
                    'id' => $order->deliveryAddress->id,
                    'recipient_name' => $order->deliveryAddress->recipient_name,
                    'phone_number' => $order->deliveryAddress->phone_number,
                    'address_line_1' => $order->deliveryAddress->address_line_1,
                    'address_line_2' => $order->deliveryAddress->address_line_2,
                    'city' => $order->deliveryAddress->city,
                    'province' => $order->deliveryAddress->province,
                    'postal_code' => $order->deliveryAddress->postal_code,
                    'full_address' => $order->deliveryAddress->full_address,
                    'delivery_instructions' => $order->deliveryAddress->delivery_instructions,
                ] : null,
                'order_items' => $order->orderItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'total_price' => $item->total_price,
                        'menu_item' => [
                            'id' => $item->menuItem->id,
                            'name' => $item->menuItem->name,
                            'description' => $item->menuItem->description,
                            'image' => $item->menuItem->image,
                            'category' => $item->menuItem->category,
                        ]
                    ];
                }),
                'payment' => $order->payment ? [
                    'id' => $order->payment->id,
                    'payment_number' => $order->payment->payment_number,
                    'amount' => $order->payment->amount,
                    'status' => $order->payment->status,
                    'payment_method' => $order->payment->payment_method,
                    'payment_date' => $order->payment->payment_date?->format('Y-m-d H:i:s'),
                    'transaction_id' => $order->payment->transaction_id,
                    'notes' => $order->payment->notes,
                ] : null,
                'delivery' => $order->delivery ? [
                    'id' => $order->delivery->id,
                    'status' => $order->delivery->status,
                    'tracking_number' => $order->delivery->tracking_number,
                    'estimated_delivery' => $order->delivery->estimated_delivery?->format('Y-m-d H:i:s'),
                    'delivered_at' => $order->delivery->delivered_at?->format('Y-m-d H:i:s'),
                    'driver' => $order->delivery->driver ? [
                        'name' => $order->delivery->driver->name,
                        'phone' => $order->delivery->driver->phone,
                    ] : null,
                ] : null,
            ]
        ]);
    }

    public function paymentPage(Order $order)
    {
        // Ensure user can only pay for their own orders
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        // Check if payment exists and order payment status is unpaid
        if (!$order->payment || $order->payment_status !== 'unpaid') {
            return redirect()->route('user.orders.index')
                ->with('error', 'Order tidak dapat dibayar atau sudah dibayar');
        }

        $order->load([
            'orderItems.menuItem',
            'payment',
            'deliveryAddress'
        ]);

        return Inertia::render('User/Orders/Payment', [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'order_type' => $order->order_type,
                'delivery_date' => $order->delivery_date?->format('Y-m-d'),
                'delivery_time_slot' => $order->delivery_time_slot,
                'subtotal' => $order->subtotal,
                'tax_amount' => $order->tax_amount,
                'delivery_fee' => $order->delivery_fee,
                'total_amount' => $order->total_amount,
                'special_instructions' => $order->special_instructions,
                'status' => $order->status,
                'can_pay' => $order->payment_status === 'unpaid',
                'payment_status' => $order->payment_status,
                'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                'delivery_address' => $order->deliveryAddress ? [
                    'recipient_name' => $order->deliveryAddress->recipient_name,
                    'phone_number' => $order->deliveryAddress->phone_number,
                    'address_line_1' => $order->deliveryAddress->address_line_1,
                    'address_line_2' => $order->deliveryAddress->address_line_2,
                    'city' => $order->deliveryAddress->city,
                    'province' => $order->deliveryAddress->province,
                    'postal_code' => $order->deliveryAddress->postal_code,
                    'full_address' => $order->deliveryAddress->full_address,
                ] : null,
                'order_items' => $order->orderItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'total_price' => $item->total_price,
                        'menu_item' => [
                            'name' => $item->menuItem->name,
                            'description' => $item->menuItem->description,
                            'image' => $item->menuItem->image,
                        ]
                    ];
                }),
                'payment' => [
                    'id' => $order->payment->id,
                    'payment_number' => $order->payment->payment_number,
                    'amount' => $order->payment->amount,
                    'status' => $order->payment->status,
                    'payment_method' => $order->payment->payment_method,
                ]
            ]
        ]);
    }

    public function processPayment(Request $request, Order $order)
    {
        // Ensure user can only pay for their own orders
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        // Check if payment exists and order payment status is unpaid
        if (!$order->payment || $order->payment_status !== 'unpaid') {
            return redirect()->route('user.orders.index')
                ->with('error', 'Order tidak dapat dibayar atau sudah dibayar');
        }

        $request->validate([
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $paymentMethod = $order->payment->payment_method;
            $transactionId = '';

            // Simulate payment method integration
            switch ($paymentMethod) {
                case 'cash_on_delivery':
                    $transactionId = 'COD-' . strtoupper(uniqid());
                    $gatewayResponse = [
                        'message' => 'Pembayaran cash on delivery berhasil',
                        'status' => 'success'
                    ];
                    break;

                case 'bank_transfer':
                    $transactionId = 'BT-' . strtoupper(uniqid());
                    $gatewayResponse = [
                        'virtual_account' => '1234567890123456',
                        'bank_name' => 'Bank Mandiri',
                        'account_name' => 'SEA Catering',
                        'expired_at' => now()->addHours(24)->toISOString()
                    ];
                    break;

                case 'credit_card':
                    $transactionId = 'CC-' . strtoupper(uniqid());
                    $gatewayResponse = [
                        'message' => 'Pembayaran credit card berhasil',
                        'status' => 'success'
                    ];
                    break;

                case 'e_wallet':
                    $transactionId = 'EW-' . strtoupper(uniqid());
                    $gatewayResponse = [
                        'qr_code' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==',
                        'deep_link' => 'gojek://pay?amount=' . $order->payment->amount,
                        'expired_at' => now()->addMinutes(15)->toISOString()
                    ];
                    break;

                default:
                    throw new \Exception('Metode pembayaran tidak dikenali');
            }

            // Update payment with consistent structure
            $order->payment->update([
                'transaction_id' => $transactionId,
                'gateway' => $paymentMethod,
                'gateway_response' => $gatewayResponse,
                'status' => 'completed',
                'notes' => $request->notes,
            ]);

            // Update order payment status to paid
            $order->update([
                'payment_status' => 'paid',
            ]);

            DB::commit();

            return redirect()->route('user.orders.show', $order->id)
                ->with('success', 'Pembayaran berhasil diproses');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat memproses pembayaran: ' . $e->getMessage());
        }
    }


    public function cancel(Order $order)
    {
        // Ensure user can only cancel their own orders
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if (!$order->can_be_cancelled) {
            return redirect()->back()
                ->with('error', 'Order tidak dapat dibatalkan');
        }

        $order->update([
            'status' => 'cancelled',
            'payment_status' => 'refunded',
        ]);

        // Cancel payment if exists
        if ($order->payment) {
            $order->payment->update([
                'status' => 'refunded'
            ]);
        }

        return redirect()->back()
            ->with('success', 'Order berhasil dibatalkan');
    }

    public function reorder(Order $order)
    {
        // Ensure user can only reorder their own orders
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        // Add order items to cart
        foreach ($order->orderItems as $item) {
            Cart::updateOrCreate(
                [
                    'user_id' => auth()->id(),
                    'menu_item_id' => $item->menu_item_id,
                ],
                [
                    'quantity' => $item->quantity,
                    'unit_price' => $item->menuItem->price, // Use current price
                    'total_price' => $item->quantity * $item->menuItem->price,
                ]
            );
        }

        return redirect()->route('user.cart.index')
            ->with('success', 'Items berhasil ditambahkan ke keranjang');
    }

    public function invoice(Order $order)
    {
        // Ensure user can only view their own order invoices
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load([
            'orderItems.menuItem',
            'deliveryAddress',
            'payment'
        ]);

        return Inertia::render('User/Orders/Invoice', [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'order_type' => $order->order_type,
                'delivery_date' => $order->delivery_date?->format('Y-m-d'),
                'delivery_time_slot' => $order->delivery_time_slot,
                'subtotal' => $order->subtotal,
                'tax_amount' => $order->tax_amount,
                'delivery_fee' => $order->delivery_fee,
                'total_amount' => $order->total_amount,
                'special_instructions' => $order->special_instructions,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                'delivery_address' => $order->deliveryAddress ? [
                    'recipient_name' => $order->deliveryAddress->recipient_name,
                    'phone_number' => $order->deliveryAddress->phone_number,
                    'full_address' => $order->deliveryAddress->full_address,
                ] : null,
                'order_items' => $order->orderItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'total_price' => $item->total_price,
                        'menu_item' => [
                            'name' => $item->menuItem->name,
                            'description' => $item->menuItem->description,
                        ]
                    ];
                }),
                'payment' => $order->payment ? [
                    'amount' => $order->payment->amount,
                    'status' => $order->payment->status,
                    'payment_method' => $order->payment->payment_method,
                    'payment_date' => $order->payment->payment_date?->format('Y-m-d H:i:s'),
                ] : null,
            ]
        ]);
    }
}
