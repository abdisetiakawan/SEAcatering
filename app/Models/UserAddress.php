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

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'user_address_id');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
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
        if ($value && $this->user_id) {
            // Set other addresses as non-default for this user
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
        $phone = preg_replace('/\D/', '', $this->phone_number);

        if (strlen($phone) >= 10) {
            if (substr($phone, 0, 2) === '08') {
                return preg_replace('/(\d{4})(\d{4})(\d{4})/', '$1-$2-$3', $phone);
            } elseif (substr($phone, 0, 3) === '628') {
                return '+62 ' . preg_replace('/(\d{3})(\d{4})(\d{4})/', '$1-$2-$3', substr($phone, 2));
            }
        }

        return $this->phone_number;
    }

    // Boot method to handle model events
    protected static function boot()
    {
        parent::boot();

        // When creating a new address, if it's the first one for the user, make it default
        static::creating(function ($address) {
            if ($address->user_id && !$address->user->addresses()->exists()) {
                $address->is_default = true;
            }
        });

        // When deleting an address, if it was default, set another as default
        static::deleting(function ($address) {
            if ($address->is_default) {
                $nextAddress = $address->user->addresses()
                    ->where('id', '!=', $address->id)
                    ->first();

                if ($nextAddress) {
                    $nextAddress->update(['is_default' => true]);
                }
            }
        });
    }
}
