<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Actuality;

class ActualitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crée 20 actualities en utilisant la logique de votre Factory
        Actuality::factory()->count(20)->create();
    }
}
