<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Party extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'company_id',
        'name',
        'address',
        'contact_number',
        'gst_number',
    ];

    /**
     * Get the challans for the party.
     */
    public function challans(): HasMany
    {
        return $this->hasMany(Challan::class);
    }

    /**
     * Get the invoices for the party.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get uninvoiced challans for the party.
     */
    public function uninvoicedChallans(): HasMany
    {
        return $this->hasMany(Challan::class)->where('is_invoiced', false);
    }

    /**
     * Get the company that owns the party.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
