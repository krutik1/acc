<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'company_id',
        'party_id',
        'payment_number',
        'payment_date',
        'amount',
        'type',
        'mode',
        'reference_number',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    public static function generatePaymentNumber($companyId)
    {
        $prefix = 'PAY-';
        $lastPayment = self::where('company_id', $companyId)
            ->where('payment_number', 'like', $prefix . '%')
            ->orderByRaw('LENGTH(payment_number) desc')
            ->orderBy('payment_number', 'desc')
            ->first();

        if (!$lastPayment) {
            return $prefix . '00001';
        }

        $number = intval(substr($lastPayment->payment_number, strlen($prefix)));
        return $prefix . str_pad($number + 1, 5, '0', STR_PAD_LEFT);
    }
}
