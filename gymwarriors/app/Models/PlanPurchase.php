<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Plan;
use App\Models\User;

class PlanPurchase extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'plan_id',
        'start_date',
        'end_date',
        'allocated_tokens',
        'remaining_tokens',
        'purchase_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'purchase_date' => 'datetime',
        'allocated_tokens' => 'integer',
        'remaining_tokens' => 'integer',
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}