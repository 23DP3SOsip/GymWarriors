<?php

use App\Support\PopularExerciseCatalog;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        PopularExerciseCatalog::seed();
    }

    public function down(): void
    {
        // Keep exercise rows that may already be used by saved workouts.
    }
};
