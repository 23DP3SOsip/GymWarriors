<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calorie_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('height_cm');
            $table->decimal('weight_kg', 5, 2);
            $table->unsignedTinyInteger('age');
            $table->string('sex', 10);
            $table->string('activity_level', 20);
            $table->string('goal', 20);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calorie_profiles');
    }
};