<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('checkins', function (Blueprint $table) {
        $table->id();

        $table->foreignId('purchase_id')
            ->constrained('plan_purchases')
            ->cascadeOnDelete();

        $table->foreignId('location_id')
            ->constrained('locations')
            ->cascadeOnDelete();

        $table->timestamp('time');
        $table->boolean('token_removed')->default(false);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkins');
    }
};
