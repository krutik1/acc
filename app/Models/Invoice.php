<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Invoice extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'company_id',
        'party_id',
        'invoice_number',
        'invoice_date',
        'subtotal',
        'gst_percent',
        'gst_amount',
        'tds_percent',
        'tds_amount',
        'discount_type',
        'discount_value',
        'discount_amount',
        'final_amount',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'invoice_date' => 'date',
        'subtotal' => 'decimal:2',
        'gst_percent' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'tds_percent' => 'decimal:2',
        'tds_amount' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
    ];

    /**
     * Discount types.
     */
    public const DISCOUNT_TYPES = [
        'fixed' => 'Fixed Amount',
        'percentage' => 'Percentage',
    ];

    /**
     * Get the company that owns the invoice.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the party that owns the invoice.
     */
    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    /**
     * Get the challans included in this invoice.
     */
    public function challans(): BelongsToMany
    {
        return $this->belongsToMany(Challan::class, 'invoice_challans')
                    ->withTimestamps();
    }

    /**
     * Calculate all amounts based on subtotal.
     * 
     * @param float $subtotal The base subtotal amount
     * @param float $gstPercent GST percentage
     * @param float $tdsPercent TDS percentage
     * @param string $discountType 'fixed' or 'percentage'
     * @param float $discountValue Discount value
     * @return array Calculated amounts
     */
    public static function calculateAmounts(
        float $subtotal,
        float $gstPercent = 0,
        float $tdsPercent = 0,
        string $discountType = 'fixed',
        float $discountValue = 0
    ): array {
        // Step 1: Calculate discount first
        $discountAmount = 0;
        if ($discountType === 'percentage') {
            $discountAmount = round($subtotal * ($discountValue / 100), 2);
        } else {
            $discountAmount = round(min($discountValue, $subtotal), 2);
        }
        
        // Step 2: Apply discount to get discounted subtotal
        $discountedSubtotal = round($subtotal - $discountAmount, 2);
        
        // Step 3: Calculate GST on the discounted amount (after discount)
        $gstAmount = round($discountedSubtotal * ($gstPercent / 100), 2);
        
        // Step 4: Calculate TDS on the discounted amount
        $tdsAmount = round($discountedSubtotal * ($tdsPercent / 100), 2);
        
        // Step 5: Final amount = Discounted Subtotal + GST - TDS
        $finalAmount = round($discountedSubtotal + $gstAmount - $tdsAmount, 2);
        
        return [
            'subtotal' => $subtotal,
            'gst_amount' => $gstAmount,
            'tds_amount' => $tdsAmount,
            'discount_amount' => $discountAmount,
            'final_amount' => max(0, $finalAmount),
        ];
    }

    /**
     * Generate a unique invoice number.
     */
    /**
     * Generate a unique invoice number based on Financial Year.
     * Format: INV<FY_START_YEAR>001
     */
    public static function generateInvoiceNumber(?int $companyId = null): string
    {
        $prefix = 'INV';
        
        // Calculate Financial Year
        // If current month is Jan(1), Feb(2), Mar(3), then FY started in previous year.
        // If current month is >= April(4), then FY started in current year.
        $currentMonth = (int) date('m');
        $currentYear = (int) date('Y');
        
        if ($currentMonth < 4) {
            $fyStartYear = $currentYear - 1;
        } else {
            $fyStartYear = $currentYear;
        }
        
        // Define FY Date Range for querying
        $fyStartDate = "{$fyStartYear}-04-01 00:00:00";
        $fyEndDate = ($fyStartYear + 1) . "-03-31 23:59:59";
        
        $query = static::whereBetween('invoice_date', [$fyStartDate, $fyEndDate]);
        
        if ($companyId) {
             $query->where('company_id', $companyId);
        }
        
        // Get last invoice of this FY
        $lastInvoice = $query->orderBy('invoice_number', 'desc')->first();
        
        if ($lastInvoice) {
            // Extract sequence from format INV<YEAR><SEQUENCE> (e.g., INV2025001)
            // Length of "INV" + "2025" is 3+4=7 characters.
            // Remaining part is sequence.
            $lastSequence = (int) substr($lastInvoice->invoice_number, 7);
            $newSequence = $lastSequence + 1;
        } else {
            $newSequence = 1;
        }
        
        // Padded to 3 digits as requested (001)
        return $prefix . $fyStartYear . str_pad($newSequence, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get all items from associated challans.
     */
    public function getAllItems()
    {
        return $this->challans->flatMap(function ($challan) {
            return $challan->items;
        });
    }
}
