<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseLocation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'purchase_id',
        'location_id',
    ];

    public function purchase()
    {
        return $this->belongsTo(PlanPurchase::class, 'purchase_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }
}