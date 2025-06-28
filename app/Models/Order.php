<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subscription_id',
        'order_number',
        'order_type',
        'delivery_address_id',
        'delivery_date',
        'delivery_time_slot',
        'subtotal',
        'tax_amount',
        'delivery_fee',
        'total_amount',
        'special_instructions',
        'status',
        'payment_status',
        'cancelled_at',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'cancelled_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function deliveryAddress()
    {
        return $this->belongsTo(UserAddress::class, 'delivery_address_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function delivery()
    {
        return $this->hasOne(Delivery::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('delivery_date', today());
    }

    public function scopeSubscriptionOrders($query)
    {
        return $query->where('order_type', 'subscription');
    }

    public function scopeDirectOrders($query)
    {
        return $query->where('order_type', 'direct');
    }

    // Accessors
    public function getFormattedTotalAttribute()
    {
        return 'Rp ' . number_format($this->total_amount, 0, ',', '.');
    }

    public function getCanBeCancelledAttribute()
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    public function getIsSubscriptionOrderAttribute()
    {
        return $this->order_type === 'subscription';
    }

    public function getIsDirectOrderAttribute()
    {
        return $this->order_type === 'direct';
    }

    public function getCustomerNameAttribute()
    {
        return $this->user->name ?? 'Unknown Customer';
    }

    public function getCustomerEmailAttribute()
    {
        return $this->user->email ?? 'Unknown Email';
    }

    public function getOrderSourceAttribute()
    {
        if ($this->is_subscription_order && $this->subscription) {
            return $this->subscription->mealPlan->name ?? 'Subscription Order';
        }
        return 'Direct Order';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (!$order->order_number) {
                $order->order_number = 'ORD-' . strtoupper(uniqid());
            }
        });
    }
}
