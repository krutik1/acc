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
        $query = static::query();

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        // Exclude old invoice numbers that start with "INV"
        // Find the latest invoice number that does NOT start with INV
        $lastInvoice = $query->where('invoice_number', 'NOT LIKE', 'INV%')
            ->orderByRaw('LENGTH(invoice_number) DESC')
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice && is_numeric($lastInvoice->invoice_number)) {
            $sequence = (int) $lastInvoice->invoice_number;
            $newSequence = $sequence + 1;
        } else {
            $newSequence = 1;
        }

        return str_pad((string) $newSequence, 3, '0', STR_PAD_LEFT);
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
