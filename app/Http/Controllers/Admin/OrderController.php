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
        $query = Order::with(['subscription.user', 'subscription.mealPlan', 'orderItems.menuItem', 'delivery.driver']);

        // Search
        if ($request->filled('search')) {
            $query->where('order_number', 'like', '%' . $request->search . '%')
                ->orWhereHas('subscription.user', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%');
                });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
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
            ->paginate(15);

        $stats = [
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'preparing_orders' => Order::where('status', 'preparing')->count(),
            'ready_orders' => Order::where('status', 'ready')->count(),
            'delivered_orders' => Order::where('status', 'delivered')->count(),
            'today_orders' => Order::whereDate('delivery_date', today())->count(),
        ];

        return Inertia::render('Admin/OrderManagement', [
            'orders' => $orders,
            'filters' => $request->only(['search', 'status', 'delivery_date', 'date_from', 'date_to']),
            'stats' => $stats,
            'statusOptions' => [
                'pending' => 'Pending',
                'confirmed' => 'Confirmed',
                'preparing' => 'Preparing',
                'ready' => 'Ready',
                'out_for_delivery' => 'Out for Delivery',
                'delivered' => 'Delivered',
                'cancelled' => 'Cancelled'
            ]
        ]);
    }

    public function show(Order $order)
    {
        $order->load([
            'subscription.user',
            'subscription.mealPlan',
            'orderItems.menuItem',
            'delivery.driver',
            'payment'
        ]);

        return Inertia::render('Admin/OrderDetail', [
            'order' => $order
        ]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,confirmed,preparing,ready,out_for_delivery,delivered,cancelled',
            'notes' => 'nullable|string'
        ]);

        $order->update($validated);

        // Create delivery record if status is out_for_delivery
        if ($validated['status'] === 'out_for_delivery' && !$order->delivery) {
            $order->delivery()->create([
                'driver_id' => $request->driver_id,
                'tracking_number' => 'TRK-' . strtoupper(uniqid()),
                'status' => 'in_transit',
                'estimated_delivery' => now()->addHours(2)
            ]);
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
                'estimated_delivery' => now()->addHours(2)
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
