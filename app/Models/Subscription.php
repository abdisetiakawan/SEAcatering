<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'meal_plan_id',
        'address_id',
        'meals_per_day',
        'days_per_week',
        'delivery_times',
        'start_date',
        'end_date',
        'status',
        'special_instructions',
    ];

    protected $casts = [
        'delivery_times' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mealPlan()
    {
        return $this->belongsTo(MealPlan::class);
    }

    public function address()
    {
        return $this->belongsTo(UserAddress::class, 'address_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getMonthlyAmountAttribute()
    {
        $dailyAmount = $this->mealPlan->price_per_meal * $this->meals_per_day;
        $weeklyAmount = $dailyAmount * $this->days_per_week;
        return $weeklyAmount * 4.33; // Average weeks per month
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePaused($query)
    {
        return $query->where('status', 'paused');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }
}
