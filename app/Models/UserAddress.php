<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserAddress extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'label',
        'recipient_name',
        'phone_number',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'country',
        'latitude',
        'longitude',
        'is_default',
        'delivery_instructions',
        'address_type'
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'delivery_address_id');
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class, 'delivery_address_id');
    }

    // Scopes
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('address_type', $type);
    }

    // Mutators
    public function setIsDefaultAttribute($value)
    {
        if ($value) {
            // Set other addresses as non-default
            static::where('user_id', $this->user_id)
                ->where('id', '!=', $this->id ?? 0)
                ->update(['is_default' => false]);
        }

        $this->attributes['is_default'] = $value;
    }

    // Accessors
    public function getFullAddressAttribute()
    {
        return trim(implode(', ', array_filter([
            $this->address_line_1,
            $this->address_line_2,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country
        ])));
    }

    public function getFormattedPhoneAttribute()
    {
        return preg_replace('/(\d{3})(\d{3})(\d{4})/', '($1) $2-$3', $this->phone_number);
    }
}
