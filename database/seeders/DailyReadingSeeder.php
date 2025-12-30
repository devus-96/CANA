<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\DailyReading;

class DailyReadingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crée 20 actualities en utilisant la logique de votre Factory
        DailyReading::factory()->count(20)->create();
    }
}
