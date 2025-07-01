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
        'customer_name',
        'customer_email',
        'customer_phone',
        'delivery_address_id',
        'delivery_date',
        'delivery_time_slot',
        'subtotal',
        'tax_amount',
        'delivery_fee',
        'discount_amount',
        'total_amount',
        'special_instructions',
        'confirmed_at',
        'delivered_at',
        'status',
        'payment_status',
        'payment_method',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];
    protected $appends = ['can_pay'];

    // Relationships
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

    public function scopeDirect($query)
    {
        return $query->where('order_type', 'one_time');
    }

    public function scopeSubscription($query)
    {
        return $query->where('order_type', 'subscription');
    }

    // Accessors
    public function getFormattedTotalAttribute()
    {
        return 'Rp ' . number_format($this->total_amount, 0, ',', '.');
    }

    public function getCanBeCancelledAttribute()
    {
        return in_array($this->status, ['pending', 'confirmed']) &&
            in_array($this->payment_status, ['unpaid', 'pending']);
    }


    public function     getCustomerNameAttribute()
    {
        // Prioritize direct customer name, then subscription user, then order user
        if (!empty($this->attributes['customer_name'])) {
            return $this->attributes['customer_name'];
        }

        if ($this->order_type === 'subscription' && $this->subscription && $this->subscription->user) {
            return $this->subscription->user->name;
        }

        if ($this->user) {
            return $this->user->name;
        }

        return 'Unknown Customer';
    }

    public function getCustomerEmailAttribute()
    {
        // Prioritize direct customer email, then subscription user, then order user
        if (!empty($this->attributes['customer_email'])) {
            return $this->attributes['customer_email'];
        }

        if ($this->order_type === 'subscription' && $this->subscription && $this->subscription->user) {
            return $this->subscription->user->email;
        }

        if ($this->user) {
            return $this->user->email;
        }

        return 'unknown@email.com';
    }


    public function getIsDirectOrderAttribute()
    {
        return $this->order_type === 'one_time';
    }

    public function getIsSubscriptionOrderAttribute()
    {
        return $this->order_type === 'subscription';
    }

    public function getCanPayAttribute()
    {
        return $this->payment_status === 'unpaid';
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (!$order->order_number) {
                $order->order_number = 'ORD-' . strtoupper(uniqid());
            }

            // Set customer info from user if not provided
            if (!$order->customer_name && $order->user) {
                $order->customer_name = $order->user->name;
                $order->customer_email = $order->user->email;
            }

            // Set default payment status
            if (!$order->payment_status) {
                $order->payment_status = 'unpaid';
            }
        });
    }
}
