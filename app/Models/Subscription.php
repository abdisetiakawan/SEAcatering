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
        'user_address_id',
        'subscription_number',
        'start_date',
        'end_date',
        'status',
        'frequency',
        'meals_per_day',
        'delivery_days',
        'delivery_time_preference',
        'total_price',
        'discount_amount',
        'special_requirements',
        'next_delivery_date',
        'auto_renew',
    ];

    protected $casts = [
        'delivery_days' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'next_delivery_date' => 'datetime',
        'total_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'meals_per_day' => 'integer',
        'auto_renew' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mealPlan()
    {
        return $this->belongsTo(MealPlan::class);
    }

    public function deliveryAddress()
    {
        return $this->belongsTo(UserAddress::class, 'user_address_id');
    }

    // Alias for backward compatibility
    public function address()
    {
        return $this->deliveryAddress();
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function latestOrder()
    {
        return $this->hasMany(Order::class);
    }

    public function getPricePerMealAttribute()
    {
        return $this->mealPlan ? $this->mealPlan->price_per_meal : 0;
    }

    public function getDeliveryFrequencyAttribute()
    {
        return $this->frequency;
    }

    public function getMonthlyAmountAttribute()
    {
        $dailyAmount = $this->price_per_meal * $this->meals_per_day;
        $weeklyAmount = $dailyAmount * count($this->delivery_days ?? []);
        return $weeklyAmount * 4.33; // Average weeks per month
    }

    public function getIsActiveAttribute()
    {
        return $this->status === 'active';
    }

    public function getCanBePausedAttribute()
    {
        return $this->status === 'active';
    }

    public function getCanBeResumedAttribute()
    {
        return $this->status === 'paused';
    }

    public function getCanBeCancelledAttribute()
    {
        return in_array($this->status, ['active', 'paused']);
    }
    // Scopes
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

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }


    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($subscription) {
            if (!$subscription->subscription_number) {
                $subscription->subscription_number = 'SUB-' . strtoupper(uniqid());
            }
        });
    }
}
