<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CartController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $cartItems = Cart::forUser($user->id)
            ->withMenuItems()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($cartItem) {
                return [
                    'id' => $cartItem->id,
                    'menu_item_id' => $cartItem->menu_item_id,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $cartItem->unit_price,
                    'total_price' => $cartItem->total_price,
                    'subtotal' => $cartItem->subtotal,
                    'total_calories' => $cartItem->total_calories,
                    'total_protein' => $cartItem->total_protein,
                    'created_at' => $cartItem->created_at,
                    'menu_item' => [
                        'id' => $cartItem->menuItem->id,
                        'name' => $cartItem->menuItem->name,
                        'description' => $cartItem->menuItem->description,
                        'image' => $cartItem->menuItem->image,
                        'category' => $cartItem->menuItem->category,
                        'price' => $cartItem->menuItem->price,
                        'calories' => $cartItem->menuItem->calories,
                        'protein' => $cartItem->menuItem->protein,
                        'carbs' => $cartItem->menuItem->carbs,
                        'fat' => $cartItem->menuItem->fat,
                        'is_available' => $cartItem->menuItem->is_available,
                    ],
                ];
            });

        $recommendedItems = $this->getRecommendedItems($user->id);

        return Inertia::render('User/Cart/Index', [
            'cartItems' => $cartItems,
            'recommendedItems' => $recommendedItems,
            'deliveryFee' => $this->calculateDeliveryFee($cartItems),
            'freeDeliveryThreshold' => 100000,
            'cartStats' => $this->getCartStats($cartItems),
        ]);
    }

    public function add(Request $request)
    {
        $request->validate([
            'menu_item_id' => 'required|exists:menu_items,id',
            'quantity' => 'required|integer|min:1|max:10',
        ]);

        $user = Auth::user();
        $menuItem = MenuItem::findOrFail($request->menu_item_id);

        if (!$menuItem->is_available) {
            return back()->withErrors(['message' => 'This item is currently unavailable.']);
        }

        try {
            DB::beginTransaction();

            $cartItem = Cart::where('user_id', $user->id)
                ->where('menu_item_id', $request->menu_item_id)
                ->first();

            if ($cartItem) {
                // Update existing cart item
                $newQuantity = min($cartItem->quantity + $request->quantity, 10); // Max 10 items
                $cartItem->update([
                    'quantity' => $newQuantity,
                    'unit_price' => $menuItem->price, // Update price in case it changed
                ]);
            } else {
                // Create new cart item
                Cart::create([
                    'user_id' => $user->id,
                    'menu_item_id' => $request->menu_item_id,
                    'quantity' => $request->quantity,
                    'unit_price' => $menuItem->price,
                    'total_price' => $menuItem->price * $request->quantity,
                ]);
            }

            DB::commit();

            return back()->with('success', 'Item added to cart successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['message' => 'Failed to add item to cart. Please try again.']);
        }
    }

    public function update(Request $request, Cart $cart)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:10',
        ]);

        // Ensure user owns this cart item
        if ($cart->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Check if menu item is still available
        if (!$cart->menuItem->is_available) {
            return back()->withErrors(['message' => 'This item is no longer available.']);
        }

        try {
            $cart->update([
                'quantity' => $request->quantity,
                'unit_price' => $cart->menuItem->price, // Update price in case it changed
            ]);

            return back()->with('success', 'Cart updated successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Failed to update cart. Please try again.']);
        }
    }

    public function remove(Cart $cart)
    {
        // Ensure user owns this cart item
        if ($cart->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $cart->delete();
            return back()->with('success', 'Item removed from cart!');
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Failed to remove item from cart. Please try again.']);
        }
    }

    public function clear()
    {
        $user = Auth::user();

        try {
            Cart::where('user_id', $user->id)->delete();
            return back()->with('success', 'Cart cleared successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Failed to clear cart. Please try again.']);
        }
    }

    public function count()
    {
        $user = Auth::user();
        $totalItems = Cart::where('user_id', $user->id)->sum('quantity');

        return response()->json(['count' => $totalItems]);
    }

    public function applyPromo(Request $request)
    {
        $request->validate([
            'promo_code' => 'required|string|max:50',
        ]);

        // Here you would implement promo code validation logic
        // For now, we'll just return a success response

        return back()->with('success', 'Promo code applied successfully!');
    }

    private function getRecommendedItems($userId)
    {
        // Get items that are not in user's cart
        $cartItemIds = Cart::where('user_id', $userId)->pluck('menu_item_id');

        return MenuItem::where('is_available', true)
            ->whereNotIn('id', $cartItemIds)
            ->inRandomOrder()
            ->limit(4)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'price' => $item->price,
                    'image' => $item->image,
                    'category' => $item->category,
                    'rating' => 4.5, // You can calculate this from reviews
                    'calories' => $item->calories,
                ];
            });
    }

    private function calculateDeliveryFee($cartItems)
    {
        $subtotal = $cartItems->sum('subtotal');

        // Free delivery for orders over 100,000 IDR
        return $subtotal >= 100000 ? 0 : 15000;
    }

    private function getCartStats($cartItems)
    {
        return [
            'total_items' => $cartItems->count(),
            'total_quantity' => $cartItems->sum('quantity'),
            'subtotal' => $cartItems->sum('subtotal'),
            'total_calories' => $cartItems->sum('total_calories'),
            'total_protein' => $cartItems->sum('total_protein'),
        ];
    }
}
