<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::create([
            'name' => 'Parastais',
            'price' => 19.99,
            'max_locations' => 2,
        ]);

        Plan::create([
            'name' => 'Pro',
            'price' => 29.99,
            'max_locations' => null,
        ]);
    }
}