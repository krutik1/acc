<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Challan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'company_id',
        'party_id',
        'challan_number',
        'challan_date',
        'subtotal',
        'is_invoiced',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'challan_date' => 'date',
        'subtotal' => 'decimal:2',
        'is_invoiced' => 'boolean',
    ];

    /**
     * Get the company that owns the challan.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the party that owns the challan.
     */
    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    /**
     * Get the items for the challan.
     */
    public function items(): HasMany
    {
        return $this->hasMany(ChallanItem::class);
    }

    /**
     * Get the invoices that include this challan.
     */
    public function invoices(): BelongsToMany
    {
        return $this->belongsToMany(Invoice::class, 'invoice_challans')
                    ->withTimestamps();
    }

    /**
     * Calculate and update the subtotal from items.
     */
    public function calculateSubtotal(): float
    {
        $subtotal = $this->items()->sum('amount');
        $this->update(['subtotal' => $subtotal]);
        return $subtotal;
    }

    /**
     * Generate a unique challan number.
     */
    public static function generateChallanNumber(?int $companyId = null): string
    {
        $prefix = 'CH';
        $year = date('Y');
        $query = static::whereYear('created_at', $year);
        
        if ($companyId) {
            $query->where('company_id', $companyId);
        }
        
        $lastChallan = $query->orderBy('id', 'desc')->first();
        
        if ($lastChallan) {
            // Extract the number from the last challan
            $lastNumber = (int) substr($lastChallan->challan_number, -6);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . $year . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }
}
