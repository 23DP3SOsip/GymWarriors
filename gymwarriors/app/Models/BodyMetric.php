<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BodyMetric extends Model
{
    protected $fillable = [
        'entry_date',
        'weight_kg',
        'chest_cm',
        'waist_cm',
        'arm_cm',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date:Y-m-d',
            'weight_kg' => 'decimal:2',
            'chest_cm' => 'decimal:1',
            'waist_cm' => 'decimal:1',
            'arm_cm' => 'decimal:1',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}