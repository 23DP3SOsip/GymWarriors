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
    Schema::create('token_transactions', function (Blueprint $table) {
        $table->id();

        $table->foreignId('purchase_id')
            ->constrained('plan_purchases')
            ->cascadeOnDelete();

        $table->integer('change');
        $table->string('reason', 20);

        $table->foreignId('checkin_id')
            ->nullable()
            ->constrained('checkins')
            ->nullOnDelete();

        $table->timestamp('date');
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('token_transactions');
    }
};
