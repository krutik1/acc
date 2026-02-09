<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'bank_name',
        'account_holder_name',
        'account_number',
        'ifsc_code',
        'account_type',
        'opening_balance',
        'opening_balance_date',
        'is_active',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'opening_balance_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class)->orderBy('transaction_date', 'asc');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get running balance up to a certain date or current.
     * Note: This can be heavy, usually better handled in query or view logic.
     */
    public function getCurrentBalanceAttribute()
    {
        $credits = $this->transactions()->where('type', 'credit')->sum('amount');
        $debits = $this->transactions()->where('type', 'debit')->sum('amount');

        return $this->opening_balance + $credits - $debits;
    }
}
