<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_item_id',
        'current_stock',
        'minimum_stock',
        'maximum_stock',
        'unit',
        'cost_per_unit',
        'supplier',
        'last_restocked_at',
        'expiry_date',
        'status'
    ];

    protected $casts = [
        'current_stock' => 'integer',
        'minimum_stock' => 'integer',
        'maximum_stock' => 'integer',
        'cost_per_unit' => 'decimal:2',
        'last_restocked_at' => 'datetime',
        'expiry_date' => 'date'
    ];

    // Relationships
    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }

    // Scopes
    public function scopeLowStock($query)
    {
        return $query->whereRaw('current_stock <= minimum_stock');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('current_stock', 0);
    }

    public function scopeExpiringSoon($query, $days = 7)
    {
        return $query->where('expiry_date', '<=', now()->addDays($days));
    }

    // Accessors
    public function getStockStatusAttribute()
    {
        if ($this->current_stock == 0) {
            return 'out_of_stock';
        } elseif ($this->current_stock <= $this->minimum_stock) {
            return 'low_stock';
        } elseif ($this->current_stock >= $this->maximum_stock) {
            return 'overstock';
        }
        return 'in_stock';
    }

    public function getStockPercentageAttribute()
    {
        if ($this->maximum_stock == 0) return 0;
        return ($this->current_stock / $this->maximum_stock) * 100;
    }

    // Methods
    public function updateStock($quantity, $type = 'add')
    {
        if ($type === 'add') {
            $this->current_stock += $quantity;
        } else {
            $this->current_stock = max(0, $this->current_stock - $quantity);
        }

        $this->save();
        return $this;
    }
}
