<?php

namespace Database\Seeders;

use App\Models\Activity;
use Illuminate\Database\Seeder;

class ActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crée 20 activités en utilisant la logique de votre Factory
        Activity::factory()->count(20)->create();
    }
}
