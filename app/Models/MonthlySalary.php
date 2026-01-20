<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlySalary extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'month',
        'salary_type',
        'fixed_salary',
        'piece_rate',
        'total_pieces',
        'total_amount',
        'total_upaad',
        'payable_amount',
        'status',
        'payment_date',
        'remarks',
    ];

    protected $casts = [
        'fixed_salary' => 'decimal:2',
        'piece_rate' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'total_upaad' => 'decimal:2',
        'payable_amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
