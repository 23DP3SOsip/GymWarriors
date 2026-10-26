<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserWorkoutExercise extends Model
{
    public $timestamps = false;

    protected $fillable = ['exercise_id', 'sets_completed', 'reps_completed', 'weight_kg', 'duration_seconds', 'notes'];

    public function workout(): BelongsTo
    {
        return $this->belongsTo(UserWorkout::class, 'user_workout_id');
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }
}
