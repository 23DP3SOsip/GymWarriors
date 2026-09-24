<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'address',
        'city',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function purchaseLocations()
    {
        return $this->hasMany(PurchaseLocation::class);
    }

    public function checkins()
    {
        return $this->hasMany(Checkin::class);
    }
}