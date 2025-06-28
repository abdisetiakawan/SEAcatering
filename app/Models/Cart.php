<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
    'user_id',
        'menu_item_id',
        'quantity',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    /**
     * Get the user that owns the cart item.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the menu item for this cart item.
     */
    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    /**
     * Get the subtotal for this cart item.
     */
    public function getSubtotalAttribute(): float
    {
        return $this->price * $this->quantity;
    }

    /**
     * Get the total calories for this cart item.
     */
    public function getTotalCaloriesAttribute(): int
    {
        return $this->menuItem->calories * $this->quantity;
    }

    /**
     * Get the total protein for this cart item.
     */
    public function getTotalProteinAttribute(): float
    {
        return $this->menuItem->protein * $this->quantity;
    }

    /**
     * Scope to get cart items for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get cart items with menu item details.
     */
    public function scopeWithMenuItems($query)
    {
        return $query->with(['menuItem' => function ($query) {
            $query->select('id', 'name', 'description', 'image', 'category', 'calories', 'protein', 'carbs', 'fat', 'is_available');
        }]);
    }
}
