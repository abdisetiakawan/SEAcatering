<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MealPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price_per_meal',
        'target_calories',
        'plan_type',
        'is_active',
        'image',
        'features',
    ];

    protected $casts = [
        'price_per_meal' => 'decimal:2',
        'is_active' => 'boolean',
        'features' => 'array',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function planMenuItems()
    {
        return $this->hasMany(PlanMenuItem::class);
    }

    public function menuItems()
    {
        return $this->belongsToMany(MenuItem::class, 'plan_menu_items')
            ->withPivot('is_featured')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
