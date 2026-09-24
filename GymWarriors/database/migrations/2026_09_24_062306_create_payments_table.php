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
    Schema::create('payments', function (Blueprint $table) {
        $table->id();

        $table->foreignId('purchase_id')
            ->constrained('plan_purchases')
            ->cascadeOnDelete();

        $table->decimal('amount', 10, 2);
        $table->timestamp('payment_date');
        $table->string('payment_method', 50);
        $table->string('status', 20);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
