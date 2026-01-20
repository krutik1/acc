<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChallanItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'challan_id',
        'description',
        'quantity',
        'unit',
        'rate',
        'amount',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'quantity' => 'decimal:3',
        'rate' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    /**
     * Available units.
     */
    public const UNITS = [
        'pcs' => 'Pieces',
        'kg' => 'Kilograms',
        'g' => 'Grams',
        'ltr' => 'Liters',
        'ml' => 'Milliliters',
        'm' => 'Meters',
        'cm' => 'Centimeters',
        'ft' => 'Feet',
        'sqft' => 'Square Feet',
        'box' => 'Boxes',
        'pack' => 'Packs',
        'set' => 'Sets',
        'dozen' => 'Dozens',
        'ton' => 'Tons',
        'quintal' => 'Quintals',
    ];

    /**
     * Get the challan that owns the item.
     */
    public function challan(): BelongsTo
    {
        return $this->belongsTo(Challan::class);
    }

    /**
     * Calculate amount from quantity and rate.
     */
    public function calculateAmount(): float
    {
        return round($this->quantity * $this->rate, 2);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-calculate amount before saving
        static::saving(function ($item) {
            $item->amount = round($item->quantity * $item->rate, 2);
        });

        // Update challan subtotal after saving item
        static::saved(function ($item) {
            $item->challan->calculateSubtotal();
        });

        // Update challan subtotal after deleting item
        static::deleted(function ($item) {
            $item->challan->calculateSubtotal();
        });
    }
}
