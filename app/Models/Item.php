<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'hsn_code',
        'rate',
        'unit',
        'status',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
