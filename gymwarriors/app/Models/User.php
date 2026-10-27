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
            'is_banned' => 'boolean',
            'banned_at' => 'datetime',
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

    public function friendshipsSent()
    {
        return $this->hasMany(Friendship::class, 'requester_id');
    }

    public function friendshipsReceived()
    {
        return $this->hasMany(Friendship::class, 'addressee_id');
    }

    public function supportConversations()
    {
        return $this->hasMany(SupportConversation::class);
    }
}
