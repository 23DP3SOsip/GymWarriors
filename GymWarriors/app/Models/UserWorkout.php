<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserWorkout extends Model
{
    protected $fillable = ['workout_template_id', 'name', 'type', 'started_at', 'completed_at', 'notes'];

    protected $casts = ['started_at' => 'datetime', 'completed_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(WorkoutTemplate::class, 'workout_template_id');
    }

    public function exercises(): HasMany
    {
        return $this->hasMany(UserWorkoutExercise::class);
    }
}
