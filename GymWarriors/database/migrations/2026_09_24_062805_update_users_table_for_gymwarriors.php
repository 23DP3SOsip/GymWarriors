<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('name', 'first_name');
            $table->string('last_name', 100)->after('first_name');
            $table->string('phone', 15)->nullable()->after('password');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'email_verified_at',
                'remember_token',
                'updated_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('first_name', 'name');
            $table->dropColumn([
                'last_name',
                'phone',
            ]);
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamp('updated_at')->nullable();
        });
    }
};