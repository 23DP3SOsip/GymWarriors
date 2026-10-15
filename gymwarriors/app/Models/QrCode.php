<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QrCode extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'purchase_id',
        'code',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function purchase()
    {
        return $this->belongsTo(PlanPurchase::class, 'purchase_id');
    }
}