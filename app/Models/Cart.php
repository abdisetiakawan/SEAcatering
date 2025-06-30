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
        'unit_price',
        'total_price',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    // Scopes
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeWithMenuItems($query)
    {
        return $query->with('menuItem');
    }

    // Accessors
    public function getSubtotalAttribute()
    {
        return $this->total_price;
    }

    public function getTotalCaloriesAttribute()
    {
        return $this->menuItem ? $this->menuItem->calories * $this->quantity : 0;
    }

    public function getTotalProteinAttribute()
    {
        return $this->menuItem ? $this->menuItem->protein * $this->quantity : 0;
    }

    // Mutators
    public function setQuantityAttribute($value)
    {
        $this->attributes['quantity'] = $value;
        $this->updateTotalPrice();
    }

    public function setUnitPriceAttribute($value)
    {
        $this->attributes['unit_price'] = $value;
        $this->updateTotalPrice();
    }

    private function updateTotalPrice()
    {
        if (isset($this->attributes['quantity']) && isset($this->attributes['unit_price'])) {
            $this->attributes['total_price'] = $this->attributes['quantity'] * $this->attributes['unit_price'];
        }
    }

    // Boot method to handle model events
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($cart) {
            if (!$cart->total_price) {
                $cart->total_price = $cart->quantity * $cart->unit_price;
            }
        });

        static::updating(function ($cart) {
            if ($cart->isDirty(['quantity', 'unit_price'])) {
                $cart->total_price = $cart->quantity * $cart->unit_price;
            }
        });
    }
}
