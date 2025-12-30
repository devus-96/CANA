<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Admin;
use App\Models\Category;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Activity>
 */
class ActivityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $adminIds = Admin::pluck('id')->toArray();
        $categoryIds = Category::pluck('id')->toArray();
        return [
            'responsable_id' => !empty($adminIds) ? $this->faker->randomElement($adminIds) : null,
            'author' => !empty($adminIds) ? $this->faker->randomElement($adminIds) : null,
            'description' => $this->faker->sentence(),
            'name' => $this->faker->words(3, true),
            'objectif' => $this->faker->paragraphs(3, true),
            'activity_image' => $this->faker->imageUrl(640, 480, 'activity', true),
            'active' => $this->faker->boolean(80), // 80% de chance d'être active
            'category_id' => !empty($categoryIds) ? $this->faker->randomElement($categoryIds) : null,
        ];
    }
}
