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
use Illuminate\Support\Facades\Session;
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

        $orders = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->through(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'order_type' => $order->order_type,
                    'order_source' => $order->order_source,
                    'delivery_date' => $order->delivery_date,
                    'delivery_time_slot' => $order->delivery_time_slot,
                    'total_amount' => $order->total_amount,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                    'can_be_cancelled' => $order->can_be_cancelled,
                    'created_at' => $order->created_at,
                    'items_count' => $order->orderItems->count(),
                ];
            });

        return Inertia::render('User/Orders/Index', [
            'orders' => $orders,
            'filters' => $request->only(['status', 'order_type']),
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
                'direct' => 'Direct Order',
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
                'order_source' => $order->order_source,
                'delivery_date' => $order->delivery_date,
                'delivery_time_slot' => $order->delivery_time_slot,
                'subtotal' => $order->subtotal,
                'tax_amount' => $order->tax_amount,
                'delivery_fee' => $order->delivery_fee,
                'total_amount' => $order->total_amount,
                'special_instructions' => $order->special_instructions,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'can_be_cancelled' => $order->can_be_cancelled,
                'created_at' => $order->created_at,
                'delivery_address' => $order->deliveryAddress,
                'order_items' => $order->orderItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'menu_item' => $item->menuItem,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'total_price' => $item->total_price,
                    ];
                }),
                'payment' => $order->payment,
                'delivery' => $order->delivery,
            ]
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
                    'price' => $item->menuItem->price, // Use current price
                    'subtotal' => $item->quantity * $item->menuItem->price,
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
                'order_source' => $order->order_source,
                'delivery_date' => $order->delivery_date,
                'delivery_time_slot' => $order->delivery_time_slot,
                'subtotal' => $order->subtotal,
                'tax_amount' => $order->tax_amount,
                'delivery_fee' => $order->delivery_fee,
                'total_amount' => $order->total_amount,
                'special_instructions' => $order->special_instructions,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'created_at' => $order->created_at,
                'customer_name' => $order->customer_name,
                'customer_email' => $order->customer_email,
                'delivery_address' => $order->deliveryAddress,
                'order_items' => $order->orderItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'menu_item' => $item->menuItem,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'total_price' => $item->total_price,
                    ];
                }),
                'payment' => $order->payment,
            ]
        ]);
    }
}
