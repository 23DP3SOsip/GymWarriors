<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public $timestamps = false;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'role' => 'string',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function planPurchases()
    {
        return $this->hasMany(PlanPurchase::class);
    }

    public function bodyMetrics()
    {
        return $this->hasMany(BodyMetric::class);
    }

    public function calorieProfile()
    {
        return $this->hasOne(CalorieProfile::class);
    }

    public function workouts()
    {
        return $this->hasMany(UserWorkout::class);
    }
}