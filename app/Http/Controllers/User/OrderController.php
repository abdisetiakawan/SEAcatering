<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\MenuItem;
use App\Models\UserAddress;
use App\Models\Cart;
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
                    'delivery_date' => $order->delivery_date?->format('Y-m-d'),
                    'delivery_time' => $order->delivery_time_slot,
                    'total_amount' => $order->total_amount,
                    'status' => $order->status,
                    'special_instructions' => $order->special_instructions,
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                    'can_be_cancelled' => $order->can_be_cancelled,
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
                        'full_address' => $order->deliveryAddress->full_address,
                    ] : null,
                    'payment' => $order->payment ? [
                        'id' => $order->payment->id,
                        'amount' => $order->payment->amount,
                        'status' => $order->payment->status,
                        'payment_method' => $order->payment->payment_method,
                        'payment_date' => $order->payment->payment_date?->format('Y-m-d H:i:s'),
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
                'can_be_cancelled' => $order->can_be_cancelled,
                'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                'delivery_address' => $order->deliveryAddress ? [
                    'id' => $order->deliveryAddress->id,
                    'recipient_name' => $order->deliveryAddress->recipient_name,
                    'phone_number' => $order->deliveryAddress->phone_number,
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
            'cancelled_at' => now()
        ]);

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
