<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'address',
        'gst_number',
        'state_code',
        'mobile_numbers',
        'bank_name',
        'ifsc_code',
        'account_number',
        'terms_conditions',
        'is_default',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Get the default company.
     */
    public static function getDefault()
    {
        return static::where('is_default', true)->first() ?? static::first();
    }
}
