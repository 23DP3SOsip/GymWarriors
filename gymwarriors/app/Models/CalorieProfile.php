<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalorieProfile extends Model
{
    protected $fillable = [
        'height_cm',
        'weight_kg',
        'age',
        'sex',
        'activity_level',
        'goal',
    ];

    protected function casts(): array
    {
        return [
            'height_cm' => 'integer',
            'weight_kg' => 'decimal:2',
            'age' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}