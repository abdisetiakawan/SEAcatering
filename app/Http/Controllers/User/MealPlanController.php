<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\MealPlan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MealPlanController extends Controller
{
    public function index(Request $request)
    {
        $query = MealPlan::with(['menuItems'])
            ->where('is_active', true);

        // Plan type filter
        if ($request->filled('plan_type') && $request->plan_type !== 'all') {
            $query->where('plan_type', $request->plan_type);
        }

        // Price range filter
        if ($request->filled('price_range') && $request->price_range !== 'all') {
            $priceRange = explode('-', $request->price_range);
            if (count($priceRange) === 2) {
                $query->whereBetween('price_per_meal', [$priceRange[0], $priceRange[1]]);
            } elseif (str_ends_with($request->price_range, '+')) {
                $minPrice = str_replace('+', '', $request->price_range);
                $query->where('price_per_meal', '>=', $minPrice);
            }
        }

        // Calories filter
        if ($request->filled('calories') && $request->calories !== 'all') {
            $caloriesRange = explode('-', $request->calories);
            if (count($caloriesRange) === 2) {
                $query->whereBetween('target_calories', [$caloriesRange[0], $caloriesRange[1]]);
            } elseif (str_ends_with($request->calories, '+')) {
                $minCalories = str_replace('+', '', $request->calories);
                $query->where('target_calories', '>=', $minCalories);
            }
        }

        $mealPlans = $query->withCount(['subscriptions', 'menuItems'])
            ->orderBy('name')
            ->paginate(9)
            ->withQueryString();

        // Transform data
        $mealPlans->getCollection()->transform(function ($plan) {
            $plan->subscribers_count = $plan->subscriptions_count;
            $plan->menu_items_count = $plan->menu_items_count;
            $plan->features = $plan->features ?? [];
            return $plan;
        });

        // Get plan types for filter
        $planTypes = [
            ['value' => 'diet', 'label' => 'Diet Plan'],
            ['value' => 'protein', 'label' => 'Protein Plan'],
            ['value' => 'royal', 'label' => 'Royal Plan'],
            ['value' => 'vegetarian', 'label' => 'Vegetarian'],
            ['value' => 'seafood', 'label' => 'Seafood'],
        ];

        // Calculate stats
        $stats = [
            'total_plans' => MealPlan::where('is_active', true)->count(),
            'active_subscribers' => \App\Models\Subscription::where('status', 'active')->count(),
            'starting_price' => MealPlan::where('is_active', true)->min('price_per_meal') ?? 0,
            'avg_rating' => 4.5, // This would come from reviews
        ];

        return Inertia::render('User/MealPlans/Index', [
            'mealPlans' => $mealPlans,
            'planTypes' => $planTypes,
            'stats' => $stats,
            'filters' => $request->only(['plan_type', 'price_range', 'calories']),
        ]);
    }

    public function show(MealPlan $mealPlan)
    {
        $mealPlan->load(['menuItems.category']);

        // Add additional data
        $mealPlan->menu_items_count = $mealPlan->menuItems()->count();
        $mealPlan->subscribers_count = $mealPlan->subscriptions()->where('status', 'active')->count();
        $mealPlan->features = json_decode($mealPlan->features ?? '[]', true);

        // Get user addresses for subscription
        $userAddresses = auth()->user()->addresses ?? [];

        return Inertia::render('User/MealPlans/Show', [
            'mealPlan' => $mealPlan,
            'userAddresses' => $userAddresses,
        ]);
    }
}
