<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'license_number',
        'vehicle_type',
        'vehicle_number',
        'is_available',
        'rating',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'rating' => 'decimal:2',
    ];

    public function deliveries()
    {
        return $this->hasMany(Delivery::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }
}
