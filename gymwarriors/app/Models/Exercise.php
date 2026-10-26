<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exercise extends Model
{
    protected $fillable = ['name', 'category', 'muscle_group', 'description', 'equipment'];

    public function workoutTemplates(): BelongsToMany
    {
        return $this->belongsToMany(WorkoutTemplate::class, 'workout_template_exercises')
            ->withPivot(['sets', 'reps', 'duration_seconds', 'rest_seconds', 'sort_order'])
            ->orderBy('sort_order');
    }

    public function userWorkoutExercises(): HasMany
    {
        return $this->hasMany(UserWorkoutExercise::class);
    }
}
