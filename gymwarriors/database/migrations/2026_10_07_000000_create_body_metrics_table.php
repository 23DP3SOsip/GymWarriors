<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('body_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('entry_date');
            $table->decimal('weight_kg', 5, 2)->nullable();
            $table->decimal('chest_cm', 5, 1)->nullable();
            $table->decimal('waist_cm', 5, 1)->nullable();
            $table->decimal('arm_cm', 5, 1)->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'entry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('body_metrics');
    }
};