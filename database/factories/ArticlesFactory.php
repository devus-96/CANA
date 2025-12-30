<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Admin;
use App\Models\Category;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class ArticlesFactory extends Factory
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

         $status = $this->faker->randomElement(['draft', 'published', 'archived', 'scheduled']);
        return [
             'category_id' => $this->faker->randomElement($categoryIds),
            'author_id' => $this->faker->randomElement($adminIds),
            'title' => $this->faker->sentence(),
            'article_image' => $this->faker->optional(0.7)->imageUrl(800, 600, 'article', true),
            'content' => $this->faker->paragraphs(rand(5, 15), true),
            'excerpt' => $this->faker->optional(0.8)->sentences(2, true),
            'slug' => $this->faker->unique()->slug(3),
            'status' => $status,
            'tags' => $this->faker->optional(0.6)->randomElements(['prière', 'jeunesse', 'évangélisation', 'bible', 'famille', 'spiritualité'], rand(1, 4)),
            'is_featured' => $this->faker->boolean(15),
            'shares_count' => $this->faker->numberBetween(0, 500),
            'views_count' => $this->faker->numberBetween(0, 10000),
            'likes_count' => $this->faker->numberBetween(0, 1000),
            'published_at' => $status === 'published' ? $this->faker->dateTimeBetween('-1 month', 'now') : null,
        ];
    }
}
