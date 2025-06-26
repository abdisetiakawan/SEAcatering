<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MealPlan;
use App\Models\MenuItem;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class MealPlanController extends Controller
{
    public function index(Request $request)
    {
        $query = MealPlan::with(['menuItems', 'subscriptions']);

        // Search
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        // Filter by plan type
        if ($request->filled('plan_type')) {
            $query->where('plan_type', $request->plan_type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        $mealPlans = $query->orderBy('created_at', 'desc')->paginate(12);

        return Inertia::render('Admin/MealPlanManagement', [
            'mealPlans' => $mealPlans,
            'filters' => $request->only(['search', 'plan_type', 'status']),
            'planTypes' => [
                'diet' => 'Diet Plan',
                'protein' => 'Protein Plan',
                'royal' => 'Royal Plan',
                'vegetarian' => 'Vegetarian',
                'seafood' => 'Seafood'
            ]
        ]);
    }

    public function create()
    {
        $menuItems = MenuItem::where('is_available', true)
            ->with('category')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/CreateMealPlan', [
            'menuItems' => $menuItems,
            'planTypes' => [
                'diet' => 'Diet Plan',
                'protein' => 'Protein Plan',
                'royal' => 'Royal Plan',
                'vegetarian' => 'Vegetarian',
                'seafood' => 'Seafood'
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price_per_meal' => 'required|numeric|min:0',
            'target_calories' => 'required|integer|min:0',
            'plan_type' => 'required|string|in:diet,protein,royal,vegetarian,seafood',
            'features' => 'nullable|array',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
            'menu_items' => 'required|array|min:1',
            'menu_items.*' => 'exists:menu_items,id'
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('meal-plans', 'public');
        }

        $mealPlan = MealPlan::create($validated);

        // Attach menu items
        $mealPlan->menuItems()->attach($request->menu_items);

        return redirect()->route('admin.meal-plans.index')
            ->with('success', 'Meal plan created successfully.');
    }

    public function show(MealPlan $mealPlan)
    {
        $mealPlan->load(['menuItems.category', 'subscriptions.user']);

        return Inertia::render('Admin/MealPlanDetail', [
            'mealPlan' => $mealPlan
        ]);
    }

    public function edit(MealPlan $mealPlan)
    {
        $mealPlan->load('menuItems');
        $menuItems = MenuItem::where('is_available', true)
            ->with('category')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/EditMealPlan', [
            'mealPlan' => $mealPlan,
            'menuItems' => $menuItems,
            'planTypes' => [
                'diet' => 'Diet Plan',
                'protein' => 'Protein Plan',
                'royal' => 'Royal Plan',
                'vegetarian' => 'Vegetarian',
                'seafood' => 'Seafood'
            ]
        ]);
    }

    public function update(Request $request, MealPlan $mealPlan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price_per_meal' => 'required|numeric|min:0',
            'target_calories' => 'required|integer|min:0',
            'plan_type' => 'required|string|in:diet,protein,royal,vegetarian,seafood',
            'features' => 'nullable|array',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
            'menu_items' => 'required|array|min:1',
            'menu_items.*' => 'exists:menu_items,id'
        ]);

        if ($request->hasFile('image')) {
            // Delete old image
            if ($mealPlan->image) {
                Storage::disk('public')->delete($mealPlan->image);
            }
            $validated['image'] = $request->file('image')->store('meal-plans', 'public');
        }

        $mealPlan->update($validated);

        // Sync menu items
        $mealPlan->menuItems()->sync($request->menu_items);

        return redirect()->route('admin.meal-plans.index')
            ->with('success', 'Meal plan updated successfully.');
    }

    public function destroy(MealPlan $mealPlan)
    {
        if ($mealPlan->subscriptions()->exists()) {
            return redirect()->back()
                ->with('error', 'Cannot delete meal plan with active subscriptions.');
        }

        if ($mealPlan->image) {
            Storage::disk('public')->delete($mealPlan->image);
        }

        $mealPlan->menuItems()->detach();
        $mealPlan->delete();

        return redirect()->route('admin.meal-plans.index')
            ->with('success', 'Meal plan deleted successfully.');
    }

    public function toggleStatus(MealPlan $mealPlan)
    {
        $mealPlan->update([
            'is_active' => !$mealPlan->is_active
        ]);

        return redirect()->back()
            ->with('success', 'Meal plan status updated successfully.');
    }
}
