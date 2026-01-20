<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    protected $fillable = [
        'user_id',
        'company_id',
        'pickup_location',
        'drop_location',
        'trip_date',
        'description',
        'quantity',
        'unit',
        'status',
        'payment_mode',
        'trip_rate',
        'pcs_rate',
        'driver_commission',
        'pcs',
    ];

    public function calculateCommission()
    {
        $mode = $this->payment_mode ?? $this->user->payment_mode ?? 'trip';

        if ($mode === 'trip') {
            return $this->trip_rate ?? $this->user->trip_rate ?? 0;
        }

        // pcs mode
        $rate = $this->pcs_rate ?? $this->user->pcs_rate ?? 0;
        return $rate * ($this->quantity ?? 0);
    }

    public function getEffectivePaymentModeAttribute()
    {
        return $this->payment_mode ?? $this->user->payment_mode ?? 'trip';
    }

    protected $casts = [
        'trip_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
