<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TokenTransaction extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'purchase_id',
        'change',
        'reason',
        'checkin_id',
        'date',
    ];

    protected $casts = [
        'change' => 'integer',
        'date' => 'datetime',
    ];
}
