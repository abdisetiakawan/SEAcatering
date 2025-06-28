<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\MenuItem;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['orderItems.menuItem', 'deliveryAddress'])
            ->where('user_id', auth()->id());

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by order number or menu item name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('orderItems.menuItem', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Date range filter
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $orders = $query->latest()->paginate(10)->withQueryString();

        // Calculate stats
        $stats = [
            'pending' => Order::where('user_id', auth()->id())->where('status', 'pending')->count(),
            'preparing' => Order::where('user_id', auth()->id())->whereIn('status', ['confirmed', 'preparing'])->count(),
            'out_for_delivery' => Order::where('user_id', auth()->id())->where('status', 'out_for_delivery')->count(),
            'delivered' => Order::where('user_id', auth()->id())->where('status', 'delivered')->count(),
        ];

        $orderStatuses = [
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'preparing' => 'Preparing',
            'ready' => 'Ready',
            'out_for_delivery' => 'Out for Delivery',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled'
        ];
        \Illuminate\Support\Facades\Log::info($orders);

        return Inertia::render('User/Orders/Index', [
            'orders' => $orders,
            'stats' => $stats,
            'orderStatuses' => $orderStatuses,
            'filters' => $request->only(['status', 'search', 'from_date', 'to_date'])
        ]);
    }

    public function show(Order $order)
    {
        // Ensure user can only view their own orders
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load([
            'orderItems.menuItem.category',
            'deliveryAddress',
            'payment',
            'delivery.driver'
        ]);

        return Inertia::render('User/Orders/Show', [
            'order' => $order
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'delivery_address_id' => 'required|exists:user_addresses,id',
            'delivery_date' => 'required|date|after:today',
            'delivery_time' => 'required|string',
            'special_instructions' => 'nullable|string|max:500',
            'payment_method' => 'required|in:cash,card,bank_transfer'
        ]);

        // Verify address belongs to user
        $address = UserAddress::where('id', $request->delivery_address_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $cart = Session::get('cart', []);
        if (empty($cart)) {
            return back()->withErrors(['message' => 'Your cart is empty.']);
        }

        DB::beginTransaction();
        try {
            // Get menu items and calculate total
            $menuItemIds = array_keys($cart);
            $menuItems = MenuItem::whereIn('id', $menuItemIds)
                ->where('is_available', true)
                ->get()
                ->keyBy('id');

            $subtotal = 0;
            $orderItems = [];

            foreach ($cart as $itemId => $quantity) {
                if (!isset($menuItems[$itemId])) {
                    throw new \Exception("Some items in your cart are no longer available.");
                }

                $menuItem = $menuItems[$itemId];
                $itemTotal = $menuItem->price * $quantity;
                $subtotal += $itemTotal;

                $orderItems[] = [
                    'menu_item_id' => $itemId,
                    'quantity' => $quantity,
                    'price' => $menuItem->price,
                    'total' => $itemTotal
                ];
            }

            // Calculate delivery fee and tax
            $deliveryFee = 5000; // Fixed delivery fee
            $taxRate = 0.1; // 10% tax
            $tax = $subtotal * $taxRate;
            $totalAmount = $subtotal + $deliveryFee + $tax;

            // Create order
            $order = Order::create([
                'user_id' => auth()->id(),
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'delivery_address_id' => $request->delivery_address_id,
                'delivery_date' => $request->delivery_date,
                'delivery_time' => $request->delivery_time,
                'special_instructions' => $request->special_instructions,
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'tax' => $tax,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'payment_method' => $request->payment_method,
                'payment_status' => 'pending'
            ]);

            // Create order items
            foreach ($orderItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $item['menu_item_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['total']
                ]);
            }

            // Clear cart
            Session::forget('cart');

            DB::commit();

            return redirect()->route('user.orders.show', $order)
                ->with('success', 'Order placed successfully!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['message' => $e->getMessage()]);
        }
    }

    public function cancel(Order $order)
    {
        // Ensure user can only cancel their own orders
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        // Only allow cancellation for pending or confirmed orders
        if (!in_array($order->status, ['pending', 'confirmed'])) {
            return back()->withErrors(['message' => 'This order cannot be cancelled.']);
        }

        $order->update([
            'status' => 'cancelled',
            'cancelled_at' => now()
        ]);

        return back()->with('success', 'Order cancelled successfully!');
    }
    public function invoice(Order $order)
    {
        // Ensure user can only view their own order invoices
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load([
            'orderItems.menuItem.category',
            'deliveryAddress',
            'payment',
            'user'
        ]);

        return Inertia::render('User/Orders/Invoice', [
            'order' => $order
        ]);
    }
}
