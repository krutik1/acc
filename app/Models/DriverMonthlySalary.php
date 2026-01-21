<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverMonthlySalary extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'user_id',
        'month',
        'total_trips',
        'total_quantity',
        'total_amount',
        'fixed_trip_amount',
        'pcs_trip_amount',
        'total_upaad',
        'total_driver_payment',
        'bonus',
        'deduction',
        'payable_amount',
        'status',
        'payment_date',
        'remarks',
    ];

    protected $casts = [
        'total_quantity' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'total_upaad' => 'decimal:2',
        'total_driver_payment' => 'decimal:2',
        'bonus' => 'decimal:2',
        'deduction' => 'decimal:2',
        'payable_amount' => 'decimal:2',
        'payment_date' => 'date',
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
