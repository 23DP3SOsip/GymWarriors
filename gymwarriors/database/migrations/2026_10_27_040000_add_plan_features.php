<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->json('features')->nullable();
        });

        DB::table('plans')->orderBy('id')->each(function (object $plan): void {
            DB::table('plans')
                ->where('id', $plan->id)
                ->update([
                    'features' => json_encode([
                        '30 DAYS MEMBERSHIP',
                        '30 TOKENS',
                        $plan->max_locations === null
                            ? 'UNLIMITED LOCATIONS'
                            : "ACCESS TO {$plan->max_locations} LOCATIONS",
                        'GYM CHECK-IN',
                    ], JSON_THROW_ON_ERROR),
                ]);
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('features');
        });
    }
};
