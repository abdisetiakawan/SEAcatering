<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanMenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'meal_plan_id',
        'menu_item_id',
        'is_featured',
        'day_of_week',
        'meal_type',
        'sort_order',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function mealPlan()
    {
        return $this->belongsTo(MealPlan::class);
    }

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByDayOfWeek($query, $day)
    {
        return $query->where('day_of_week', $day);
    }

    public function scopeByMealType($query, $type)
    {
        return $query->where('meal_type', $type);
    }
}
