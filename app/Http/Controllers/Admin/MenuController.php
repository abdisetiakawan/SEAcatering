<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $query = MenuItem::query();

        // Search
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by status
        if ($request->filled('status')) {
            $isAvailable = $request->status === 'active';
            $query->where('is_available', $isAvailable);
        }

        $menuItems = $query->orderBy('created_at', 'desc')->paginate(12);

        return Inertia::render('Admin/MenuManagement', [
            'menuItems' => $menuItems,
            'filters' => $request->only(['search', 'category', 'status']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'category' => 'required|string|in:diet,protein,royal,vegetarian,seafood',
            'calories' => 'required|integer|min:0',
            'protein' => 'required|numeric|min:0',
            'carbs' => 'required|numeric|min:0',
            'fat' => 'required|numeric|min:0',
            'ingredients' => 'nullable|string',
            'allergens' => 'nullable',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_available' => 'in:true,false',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('menu-items', 'public');
        }

        if ($request->filled('allergens')) {
            $validated['allergens'] = json_decode($request->allergens, true);
        }

        MenuItem::create($validated);

        return redirect()->route('admin.menu.index')->with('success', 'Menu item created successfully.');
    }


    public function update(Request $request, MenuItem $menu)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'category' => 'required|string|in:diet,protein,royal,vegetarian,seafood',
            'calories' => 'required|integer|min:0',
            'protein' => 'required|numeric|min:0',
            'carbs' => 'required|numeric|min:0',
            'fat' => 'required|numeric|min:0',
            'ingredients' => 'nullable|string',
            'allergens' => 'nullable|json',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_available' => 'in:true,false',
        ]);
        if ($request->hasFile('image')) {
            // Delete old image
            if ($menu->image) {
                Storage::disk('public')->delete($menu->image);
            }
            $validated['image'] = $request->file('image')->store('menu-items', 'public');
        }

        if ($request->filled('allergens')) {
            $validated['allergens'] = json_decode($request->allergens, true);
        }

        $menu->update($validated);

        return redirect()->route('admin.menu.index')
            ->with('success', 'Menu item updated successfully.');
    }

    public function destroy(MenuItem $menu)
    {
        if ($menu->image) {
            Storage::disk('public')->delete($menu->image);
        }

        $menu->delete();

        return redirect()->route('admin.menu.index')
            ->with('success', 'Menu item deleted successfully.');
    }

    public function toggleStatus(MenuItem $menuItem)
    {
        $menuItem->update([
            'is_available' => !$menuItem->is_available
        ]);

        return redirect()->back()
            ->with('success', 'Menu status updated successfully.');
    }
}
