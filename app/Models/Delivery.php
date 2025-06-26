<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'driver_id',
        'tracking_number',
        'pickup_time',
        'estimated_delivery',
        'actual_delivery',
        'status',
        'delivery_notes',
    ];

    protected $casts = [
        'pickup_time' => 'datetime',
        'estimated_delivery' => 'datetime',
        'actual_delivery' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($delivery) {
            $delivery->tracking_number = 'TRK-' . strtoupper(uniqid());
        });
    }
}
