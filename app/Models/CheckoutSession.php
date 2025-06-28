<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CheckoutSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'cart_items',
        'delivery_address_id',
        'delivery_date',
        'delivery_time_slot',
        'subtotal',
        'delivery_fee',
        'tax_amount',
        'total_amount',
        'special_instructions',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'cart_items' => 'array',
        'delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'expires_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deliveryAddress()
    {
        return $this->belongsTo(UserAddress::class, 'delivery_address_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'pending')
            ->where('expires_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now())
            ->where('status', 'pending');
    }

    // Methods
    public function isExpired()
    {
        return $this->expires_at <= now() && $this->status === 'pending';
    }

    public function markAsCompleted()
    {
        $this->update(['status' => 'completed']);
    }

    public function markAsExpired()
    {
        $this->update(['status' => 'expired']);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($session) {
            $session->session_id = 'CHK-' . strtoupper(uniqid());
            $session->expires_at = now()->addMinutes(30); // 30 minutes to complete checkout
        });
    }
}
