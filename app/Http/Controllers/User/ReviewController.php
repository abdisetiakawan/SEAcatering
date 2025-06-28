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
}
