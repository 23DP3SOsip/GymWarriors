<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Location;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        Location::create([
            'name' => 'GymWarriors Centrs',
            'address' => 'Brīvības iela 100',
            'city' => 'Rīga',
            'active' => true,
        ]);

        Location::create([
            'name' => 'GymWarriors Pārdaugava',
            'address' => 'Kārļa Ulmaņa gatve 50',
            'city' => 'Rīga',
            'active' => true,
        ]);

        Location::create([
            'name' => 'GymWarriors Jugla',
            'address' => 'Brīvības gatve 400',
            'city' => 'Rīga',
            'active' => true,
        ]);
    }
}