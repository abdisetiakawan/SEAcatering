<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $query = MenuItem::query()
            ->where('is_available', true);

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Category filter
        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        // Price range filter
        if ($request->filled('price_range') && $request->price_range !== 'all') {
            $priceRange = explode('-', $request->price_range);
            if (count($priceRange) === 2) {
                $query->whereBetween('price', [$priceRange[0], $priceRange[1]]);
            } elseif (str_ends_with($request->price_range, '+')) {
                $minPrice = str_replace('+', '', $request->price_range);
                $query->where('price', '>=', $minPrice);
            }
        }

        // Sorting
        switch ($request->get('sort', 'name')) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'rating':
                $query->orderByDesc('average_rating');
                break;
            case 'popular':
                $query->withCount('orderItems')
                    ->orderByDesc('order_items_count');
                break;
            default:
                $query->orderBy('name', 'asc');
        }

        $menuItems = $query->paginate(12)->withQueryString();

        // Add average rating and review count
        $menuItems->getCollection()->transform(function ($item) {
            $item->average_rating = $item->reviews()->avg('rating') ?? 0;
            $item->review_count = $item->reviews()->count();
            return $item;
        });

        // Get unique categories from menu_items for filter
        $categories = MenuItem::whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->map(function ($category, $index) {
                return [
                    'id' => $index + 1,
                    'name' => $category,
                ];
            })
            ->values();

        // Get stats
        $stats = [
            'total_menu_items' => MenuItem::where('is_available', true)->count(),
            'categories_count' => $categories->count(),
            'average_rating' => MenuItem::join('reviews', 'menu_items.id', '=', 'reviews.menu_item_id')
                ->avg('reviews.rating') ?? 0,
            'popular_items' => MenuItem::whereExists(function ($query) {
                $query->select(DB::raw('1'))
                    ->from('order_items')
                    ->whereColumn('menu_items.id', 'order_items.menu_item_id')
                    ->groupBy('menu_item_id')
                    ->havingRaw('COUNT(*) > 10');
            })->count(),
        ];

        $cartItemsCount = Cart::where('user_id', auth()->id())->count();


        return Inertia::render('User/Menu/Index', [
            'menuItems' => $menuItems,
            'categories' => $categories,
            'stats' => $stats,
            'filters' => $request->only(['search', 'category', 'price_range', 'sort']),
            'cartItemsCount' => $cartItemsCount,
        ]);
    }

    public function show(MenuItem $menuItem)
    {
        $menuItem->load(['category', 'reviews.user']);

        // Calculate average rating
        $menuItem->average_rating = $menuItem->reviews()->avg('rating') ?? 0;
        $menuItem->review_count = $menuItem->reviews()->count();

        // Get related items
        $relatedItems = MenuItem::where('category_id', $menuItem->category_id)
            ->where('id', '!=', $menuItem->id)
            ->where('is_available', true)
            ->limit(4)
            ->get();

        return Inertia::render('User/Menu/Show', [
            'menuItem' => $menuItem,
            'relatedItems' => $relatedItems,
        ]);
    }
}
