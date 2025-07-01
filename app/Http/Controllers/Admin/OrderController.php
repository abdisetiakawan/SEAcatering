<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\Driver;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with([
            'user',
            'subscription.user',
            'subscription.mealPlan',
            'orderItems.menuItem',
            'delivery.driver',
            'deliveryAddress'
        ]);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', '%' . $search . '%')
                    ->orWhere('customer_name', 'like', '%' . $search . '%')
                    ->orWhere('customer_email', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('subscription.user', function ($subUserQuery) use ($search) {
                        $subUserQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    });
            });
        }

        // Filter by status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by order type
        if ($request->filled('order_type') && $request->order_type !== 'all') {
            $query->where('order_type', $request->order_type);
        }

        // Filter by delivery date
        if ($request->filled('delivery_date')) {
            $query->whereDate('delivery_date', $request->delivery_date);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('delivery_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('delivery_date', '<=', $request->date_to);
        }

        $orders = $query->orderBy('delivery_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->through(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'order_type' => $order->order_type,
                    'customer_name' => $order->customer_name,
                    'customer_email' => $order->customer_email,
                    'order_source' => $order->order_source,
                    'delivery_date' => $order->delivery_date->format('Y-m-d'),
                    'delivery_time_slot' => $order->delivery_time_slot,
                    'total_amount' => $order->total_amount,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                    'plan_name' => $order->subscription && $order->subscription->mealPlan
                        ? $order->subscription->mealPlan->name
                        : 'Direct Order',
                ];
            });

        $stats = [
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'preparing_orders' => Order::where('status', 'preparing')->count(),
            'ready_orders' => Order::where('status', 'ready')->count(),
            'delivered_orders' => Order::where('status', 'delivered')->count(),
            'today_orders' => Order::whereDate('delivery_date', today())->count(),
            'subscription_orders' => Order::where('order_type', 'subscription')->count(),
            'direct_orders' => Order::where('order_type', 'one_time')->count(),
        ];

        return Inertia::render('Admin/OrderManagement', [
            'orders' => $orders,
            'filters' => $request->only(['search', 'status', 'order_type', 'delivery_date', 'date_from', 'date_to']),
            'stats' => $stats,
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
        $order->load([
            'user',
            'subscription.user',
            'subscription.mealPlan',
            'orderItems.menuItem',
            'delivery.driver',
            'payment',
            'deliveryAddress'
        ]);

        return Inertia::render('Admin/OrderDetail', [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'order_type' => $order->order_type,
                'customer_name' => $order->customer_name,
                'customer_email' => $order->customer_email,
                'order_source' => $order->order_source,
                'delivery_date' => $order->delivery_date->format('Y-m-d'),
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
                    'id' => $order->deliveryAddress->id,
                    'address_line_1' => $order->deliveryAddress->address_line_1,
                    'address_line_2' => $order->deliveryAddress->address_line_2,
                    'city' => $order->deliveryAddress->city,
                    'state' => $order->deliveryAddress->state,
                    'postal_code' => $order->deliveryAddress->postal_code,
                    'country' => $order->deliveryAddress->country,
                ] : null,
                'order_items' => $order->orderItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'menu_item' => $item->menuItem ? [
                            'id' => $item->menuItem->id,
                            'name' => $item->menuItem->name,
                            'description' => $item->menuItem->description,
                            'price' => $item->menuItem->price,
                        ] : null,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'total_price' => $item->total_price,
                        'special_instructions' => $item->special_instructions,
                    ];
                }),
                'payment' => $order->payment ? [
                    'id' => $order->payment->id,
                    'amount' => $order->payment->amount,
                    'status' => $order->payment->status,
                    'method' => $order->payment->method,
                    'transaction_id' => $order->payment->transaction_id,
                ] : null,
                'delivery' => $order->delivery ? [
                    'id' => $order->delivery->id,
                    'tracking_number' => $order->delivery->tracking_number,
                    'status' => $order->delivery->status,
                    'driver' => $order->delivery->driver ? [
                        'id' => $order->delivery->driver->id,
                        'name' => $order->delivery->driver->name,
                        'phone' => $order->delivery->driver->phone,
                    ] : null,
                    'picked_up_at' => $order->delivery->picked_up_at,
                    'delivered_at' => $order->delivery->delivered_at,
                ] : null,
            ]
        ]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,confirmed,preparing,ready,out_for_delivery,delivered,cancelled',
            'notes' => 'nullable|string'
        ]);

        $order->update([
            'status' => $validated['status']
        ]);

        // Create delivery record if status is out_for_delivery
        if ($validated['status'] === 'out_for_delivery' && !$order->delivery) {
            $order->delivery()->create([
                'driver_id' => $request->driver_id,
                'tracking_number' => 'TRK-' . strtoupper(uniqid()),
                'status' => 'in_transit',
            ]);
        }

        // Update delivered timestamp
        if ($validated['status'] === 'delivered') {
            $order->update(['delivered_at' => now()]);
            if ($order->delivery) {
                $order->delivery->update([
                    'status' => 'delivered',
                    'delivered_at' => now()
                ]);
            }
        }

        return redirect()->back()
            ->with('success', 'Order status updated successfully.');
    }

    public function assignDriver(Request $request, Order $order)
    {
        $validated = $request->validate([
            'driver_id' => 'required|exists:drivers,id'
        ]);

        if ($order->delivery) {
            $order->delivery->update($validated);
        } else {
            $order->delivery()->create([
                'driver_id' => $validated['driver_id'],
                'tracking_number' => 'TRK-' . strtoupper(uniqid()),
                'status' => 'assigned',
            ]);
        }

        return redirect()->back()
            ->with('success', 'Driver assigned successfully.');
    }

    public function bulkUpdateStatus(Request $request)
    {
        $validated = $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id',
            'status' => 'required|string|in:pending,confirmed,preparing,ready,out_for_delivery,delivered,cancelled'
        ]);

        Order::whereIn('id', $validated['order_ids'])
            ->update(['status' => $validated['status']]);

        return redirect()->back()
            ->with('success', count($validated['order_ids']) . ' orders updated successfully.');
    }
}
