<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\MealPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        Log::info('User Dashboard accessed');
        $user = auth()->user();

        // Get user statistics
        $userStats = [
            'total_orders' => $user->orders()->count(),
            'total_spent' => $user->orders()->sum('total_amount'),
            'active_subscriptions' => $user->subscriptions()->where('status', 'active')->count(),
            'favorite_items' => 0, // This would come from a favorites/wishlist feature
        ];

        // Get recent orders (last 5)
        $recentOrders = $user->orders()
            ->with(['orderItems.menuItem'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'total_amount' => $order->total_amount,
                    'created_at' => $order->created_at->toISOString(),
                    'items_count' => $order->orderItems->count(),
                    'delivery_date' => $order->delivery_date?->toISOString(),
                ];
            });

        // Get active subscriptions
        $activeSubscriptions = $user->subscriptions()
            ->with(['mealPlan'])
            ->where('status', 'active')
            ->get()
            ->map(function ($subscription) {
                return [
                    'id' => $subscription->id,
                    'meal_plan' => [
                        'id' => $subscription->mealPlan->id,
                        'name' => $subscription->mealPlan->name,
                        'plan_type' => $subscription->mealPlan->plan_type,
                    ],
                    'status' => $subscription->status,
                    'start_date' => $subscription->start_date->toISOString(),
                    'end_date' => $subscription->end_date->toISOString(),
                    'next_delivery' => $subscription->next_delivery?->toISOString(),
                    'frequency' => $subscription->frequency,
                ];
            });

        // Get recommended items (based on user's order history or popular items)
        $recommendedItems = MenuItem::where('is_available', true)
            ->with(['category'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->orderBy('reviews_avg_rating', 'desc')
            ->take(6)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'price' => $item->price,
                    'image' => $item->image,
                    'category' => $item->category->name ?? 'Uncategorized',
                    'rating' => round($item->reviews_avg_rating ?? 0, 1),
                    'reviews_count' => $item->reviews_count ?? 0,
                ];
            });

        // Get popular meal plans
        $popularMealPlans = MealPlan::where('is_active', true)
            ->withCount('subscriptions')
            ->orderBy('subscriptions_count', 'desc')
            ->take(3)
            ->get()
            ->map(function ($plan) {
                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'description' => $plan->description,
                    'price_per_meal' => $plan->price_per_meal,
                    'image' => $plan->image,
                    'plan_type' => $plan->plan_type,
                    'target_calories' => $plan->target_calories,
                ];
            });

        return Inertia::render('User/Dashboard', [
            'user' => $user,
            'userStats' => $userStats,
            'recentOrders' => $recentOrders,
            'activeSubscriptions' => $activeSubscriptions,
            'recommendedItems' => $recommendedItems,
            'popularMealPlans' => $popularMealPlans,
        ]);
    }
}
