<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'price',
        'max_locations',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'max_locations' => 'integer',
    ];
}