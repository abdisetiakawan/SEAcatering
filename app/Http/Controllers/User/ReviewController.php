<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Order;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::with(['menuItem', 'order'])
            ->where('user_id', auth()->id());

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'published') {
                $query->where('is_published', true);
            } elseif ($request->status === 'draft') {
                $query->where('is_published', false);
            }
        }

        // Filter by rating
        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        $reviews = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('User/Reviews/Index', [
            'reviews' => $reviews,
            'filters' => $request->only(['status', 'rating'])
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'menu_item_id' => 'required|exists:menu_items,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000'
        ]);

        // Verify order belongs to user and is delivered
        $order = Order::where('id', $request->order_id)
            ->where('user_id', auth()->id())
            ->where('status', 'delivered')
            ->firstOrFail();

        // Verify menu item was in the order
        $orderItem = $order->orderItems()
            ->where('menu_item_id', $request->menu_item_id)
            ->first();

        if (!$orderItem) {
            return back()->withErrors(['message' => 'You can only review items from your delivered orders.']);
        }

        // Check if review already exists
        $existingReview = Review::where('user_id', auth()->id())
            ->where('order_id', $request->order_id)
            ->where('menu_item_id', $request->menu_item_id)
            ->first();

        if ($existingReview) {
            return back()->withErrors(['message' => 'You have already reviewed this item.']);
        }

        Review::create([
            'user_id' => auth()->id(),
            'order_id' => $request->order_id,
            'menu_item_id' => $request->menu_item_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'is_published' => true
        ]);

        return back()->with('success', 'Review submitted successfully!');
    }

    public function update(Request $request, Review $review)
    {
        // Ensure user can only update their own reviews
        if ($review->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000'
        ]);

        $review->update([
            'rating' => $request->rating,
            'comment' => $request->comment
        ]);

        return back()->with('success', 'Review updated successfully!');
    }

    public function destroy(Review $review)
    {
        // Ensure user can only delete their own reviews
        if ($review->user_id !== auth()->id()) {
            abort(403);
        }

        $review->delete();

        return back()->with('success', 'Review deleted successfully!');
    }
    public function create(Order $order, MenuItem $menuItem)
    {
        // Ensure user can only review their own orders
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to order');
        }

        // Verify order is delivered
        if ($order->status !== 'delivered') {
            return redirect()->route('user.orders.show', $order->id)
                ->with('error', 'You can only review items from delivered orders.');
        }

        // Verify menu item was in the order
        $orderItem = $order->orderItems()
            ->where('menu_item_id', $menuItem->id)
            ->first();

        if (!$orderItem) {
            return redirect()->route('user.orders.show', $order->id)
                ->with('error', 'This item was not in your order.');
        }

        // Check if review already exists
        $existingReview = Review::where('user_id', auth()->id())
            ->where('order_id', $order->id)
            ->where('menu_item_id', $menuItem->id)
            ->first();

        if ($existingReview) {
            return redirect()->route('user.orders.show', $order->id)
                ->with('error', 'You have already reviewed this item.');
        }

        // Load menu item with category and recent reviews
        $menuItem->load(['category']);

        // Get recent reviews for this menu item
        $recentReviews = Review::where('menu_item_id', $menuItem->id)
            ->where('is_published', true)
            ->with('user:id,name')
            ->latest()
            ->take(5)
            ->get();

        // Calculate average rating
        $averageRating = Review::where('menu_item_id', $menuItem->id)
            ->where('is_published', true)
            ->avg('rating') ?: 0;

        $totalReviews = Review::where('menu_item_id', $menuItem->id)
            ->where('is_published', true)
            ->count();

        return Inertia::render('User/Reviews/Create', [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'delivery_date' => $order->delivery_date?->format('Y-m-d'),
                'status' => $order->status,
            ],
            'menuItem' => [
                'id' => $menuItem->id,
                'name' => $menuItem->name,
                'description' => $menuItem->description,
                'image' => $menuItem->image,
                'price' => $menuItem->price,
                'category' => $menuItem->category ? [
                    'name' => $menuItem->category,
                ] : null,
                'average_rating' => round($averageRating, 1),
                'total_reviews' => $totalReviews,
            ],
            'orderItem' => [
                'id' => $orderItem->id,
                'quantity' => $orderItem->quantity,
                'unit_price' => $orderItem->unit_price,
                'total_price' => $orderItem->total_price,
            ],
            'recentReviews' => $recentReviews->map(function ($review) {
                return [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'created_at' => $review->created_at->format('d M Y'),
                    'user' => [
                        'name' => $review->user->name,
                    ]
                ];
            })
        ]);
    }
    public function canReview(Order $order, MenuItem $menuItem)
    {
        // Check if user owns the order
        if ($order->user_id !== auth()->id()) {
            return false;
        }

        // Check if order is delivered
        if ($order->status !== 'delivered') {
            return false;
        }

        // Check if menu item was in the order
        $orderItem = $order->orderItems()
            ->where('menu_item_id', $menuItem->id)
            ->first();

        if (!$orderItem) {
            return false;
        }

        // Check if review already exists
        $existingReview = Review::where('user_id', auth()->id())
            ->where('order_id', $order->id)
            ->where('menu_item_id', $menuItem->id)
            ->first();

        return !$existingReview;
    }

    /**
     * Get review for a specific order item
     */
    public function getReview(Order $order, MenuItem $menuItem)
    {
        return Review::where('user_id', auth()->id())
            ->where('order_id', $order->id)
            ->where('menu_item_id', $menuItem->id)
            ->first();
    }
}
