<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'company_id',
        'payment_mode',
        'trip_rate',
        'pcs_rate',
        'fixed_salary', // Added
        'is_blocked',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }
    
    public function upaads()
    {
        return $this->hasMany(Upaad::class);
    }

    public function monthlySalaries()
    {
        return $this->hasMany(MonthlySalary::class);
    }

    public function hasRole($role)
    {
        return $this->role === $role;
    }

    public function isDriver()
    {
        return $this->hasRole('driver');
    }

    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
