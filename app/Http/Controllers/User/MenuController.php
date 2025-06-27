<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $query = MenuItem::with(['category', 'reviews'])
            ->where('is_available', true);

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Search by name or description
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by price range
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Sort options
        $sortBy = $request->get('sort', 'name');
        $sortOrder = $request->get('order', 'asc');

        switch ($sortBy) {
            case 'price':
                $query->orderBy('price', $sortOrder);
                break;
            case 'rating':
                $query->withAvg('reviews', 'rating')
                    ->orderBy('reviews_avg_rating', $sortOrder);
                break;
            case 'popularity':
                $query->withCount('orderItems')
                    ->orderBy('order_items_count', $sortOrder);
                break;
            default:
                $query->orderBy('name', $sortOrder);
        }

        $menuItems = $query->paginate(12)->withQueryString();

        // Add average rating and review count to each item
        $menuItems->getCollection()->transform(function ($item) {
            $item->average_rating = $item->reviews->avg('rating') ?? 0;
            $item->review_count = $item->reviews->count();
            return $item;
        });

        $categories = Category::orderBy('name')->get();

        return Inertia::render('User/Menu/Index', [
            'menuItems' => $menuItems,
            'categories' => $categories,
            'filters' => $request->only(['category', 'search', 'min_price', 'max_price', 'sort', 'order'])
        ]);
    }

    public function show(MenuItem $menuItem)
    {
        $menuItem->load([
            'category',
            'reviews' => function ($query) {
                $query->where('is_published', true)
                    ->with('user')
                    ->latest();
            }
        ]);

        // Calculate average rating and review count
        $menuItem->average_rating = $menuItem->reviews->avg('rating') ?? 0;
        $menuItem->review_count = $menuItem->reviews->count();

        // Get related menu items from same category
        $relatedItems = MenuItem::where('category_id', $menuItem->category_id)
            ->where('id', '!=', $menuItem->id)
            ->where('is_available', true)
            ->with('category')
            ->limit(4)
            ->get();

        return Inertia::render('User/Menu/Show', [
            'menuItem' => $menuItem,
            'relatedItems' => $relatedItems
        ]);
    }
}
