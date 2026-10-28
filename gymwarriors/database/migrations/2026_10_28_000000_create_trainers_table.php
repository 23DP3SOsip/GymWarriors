<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainers', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('specialty', 150);
            $table->string('experience', 150);
            $table->text('description');
            $table->string('email');
            $table->string('phone', 50);
            $table->string('image', 2048);
            $table->timestamps();
        });

        $now = now();
        DB::table('trainers')->insert([
            [
                'first_name' => 'Anna',
                'last_name' => 'Kalniņa',
                'specialty' => 'STRENGTH & CONDITIONING',
                'experience' => '8 years of coaching experience',
                'description' => 'Anna helps people build strength with structured, sustainable training. Her sessions focus on confident technique, steady progress, and plans that fit real life.',
                'email' => 'anna.kalnina@example.com',
                'phone' => '+371 2000 0001',
                'image' => '/images/trainers/trainer-1.jpg',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'first_name' => 'Mārtiņš',
                'last_name' => 'Ozols',
                'specialty' => 'PERSONAL TRAINING',
                'experience' => '10 years of coaching experience',
                'description' => 'Mārtiņš creates practical one-to-one training plans for all experience levels. He specializes in strength fundamentals, movement quality, and long-term consistency.',
                'email' => 'martins.ozols@example.com',
                'phone' => '+371 2000 0002',
                'image' => '/images/trainers/trainer-2.jpg',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'first_name' => 'Laura',
                'last_name' => 'Bērziņa',
                'specialty' => 'MOBILITY & FITNESS',
                'experience' => '6 years of coaching experience',
                'description' => 'Laura combines mobility, conditioning, and recovery-focused coaching to help clients move well and feel stronger. Her approach is supportive, adaptable, and goal-oriented.',
                'email' => 'laura.berzina@example.com',
                'phone' => '+371 2000 0003',
                'image' => '/images/trainers/trainer-3.jpg',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'first_name' => 'Rihards',
                'last_name' => 'Liepiņš',
                'specialty' => 'FUNCTIONAL FITNESS',
                'experience' => '7 years of coaching experience',
                'description' => 'Rihards helps clients improve their strength, coordination, and endurance through adaptable functional training. He focuses on approachable coaching and progress that lasts.',
                'email' => 'rihards.liepins@example.com',
                'phone' => '+371 2000 0004',
                'image' => '/images/trainers/trainer-4.jpg',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('trainers');
    }
};
