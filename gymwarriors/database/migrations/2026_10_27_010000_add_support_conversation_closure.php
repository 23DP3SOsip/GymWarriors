<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_conversations', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
            $table->index('user_id');
            $table->string('status', 20)->default('open');
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('support_conversations', function (Blueprint $table) {
            $table->dropForeign(['closed_by']);
            $table->dropIndex(['user_id']);
            $table->dropColumn(['status', 'closed_at', 'closed_by']);
            $table->unique('user_id');
        });
    }
};
