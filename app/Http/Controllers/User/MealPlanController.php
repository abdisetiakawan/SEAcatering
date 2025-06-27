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
        $query = MealPlan::with(['menuItems.category'])
            ->where('is_active', true);

        // Filter by plan type
        if ($request->filled('plan_type')) {
            $query->where('plan_type', $request->plan_type);
        }

        // Filter by price range
        if ($request->filled('min_price')) {
            $query->where('price_per_meal', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price_per_meal', '<=', $request->max_price);
        }

        // Filter by calories
        if ($request->filled('max_calories')) {
            $query->where('target_calories', '<=', $request->max_calories);
        }

        // Sort options
        $sortBy = $request->get('sort', 'name');
        $sortOrder = $request->get('order', 'asc');

        switch ($sortBy) {
            case 'price':
                $query->orderBy('price_per_meal', $sortOrder);
                break;
            case 'calories':
                $query->orderBy('target_calories', $sortOrder);
                break;
            default:
                $query->orderBy('name', $sortOrder);
        }

        $mealPlans = $query->paginate(9)->withQueryString();

        // Get unique plan types for filter
        $planTypes = MealPlan::where('is_active', true)
            ->distinct()
            ->pluck('plan_type')
            ->map(function ($type) {
                return [
                    'value' => $type,
                    'label' => ucfirst($type)
                ];
            });

        return Inertia::render('User/MealPlans/Index', [
            'mealPlans' => $mealPlans,
            'planTypes' => $planTypes,
            'filters' => $request->only(['plan_type', 'min_price', 'max_price', 'max_calories', 'sort', 'order'])
        ]);
    }

    public function show(MealPlan $mealPlan)
    {
        $mealPlan->load([
            'menuItems.category',
            'subscriptions' => function ($query) {
                $query->where('user_id', auth()->id());
            }
        ]);

        // Check if user has active subscription to this plan
        $hasActiveSubscription = $mealPlan->subscriptions
            ->where('status', 'active')
            ->isNotEmpty();

        return Inertia::render('User/MealPlans/Show', [
            'mealPlan' => $mealPlan,
            'hasActiveSubscription' => $hasActiveSubscription
        ]);
    }
}
