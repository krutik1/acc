<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = ['company_id', 'name'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
