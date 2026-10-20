<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exercises', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('category', 30);
            $table->string('muscle_group', 50);
            $table->text('description')->nullable();
            $table->string('equipment', 50)->nullable();
            $table->timestamps();
        });

        Schema::create('workout_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('type', 30);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('workout_template_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workout_template_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('sets')->default(3);
            $table->unsignedSmallInteger('reps')->nullable();
            $table->unsignedSmallInteger('duration_seconds')->nullable();
            $table->unsignedSmallInteger('rest_seconds')->default(90);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->unique(['workout_template_id', 'exercise_id']);
        });

        Schema::create('user_workouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('workout_template_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 100);
            $table->string('type', 30);
            $table->dateTime('started_at');
            $table->dateTime('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('user_workout_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_workout_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained()->restrictOnDelete();
            $table->unsignedTinyInteger('sets_completed')->default(0);
            $table->unsignedSmallInteger('reps_completed')->nullable();
            $table->decimal('weight_kg', 6, 2)->nullable();
            $table->unsignedSmallInteger('duration_seconds')->nullable();
            $table->text('notes')->nullable();
            $table->unique(['user_workout_id', 'exercise_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_workout_exercises');
        Schema::dropIfExists('user_workouts');
        Schema::dropIfExists('workout_template_exercises');
        Schema::dropIfExists('workout_templates');
        Schema::dropIfExists('exercises');
    }
};
