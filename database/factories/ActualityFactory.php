<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Activity;
use App\Models\Category;
use App\Models\Admin;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Actuality>
 */
class ActualityFactory extends Factory
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
        $activityIds = Activity::pluck('id')->toArray();

        $status = $this->faker->randomElement(['draft', 'published', 'archived', 'scheduled']);
        $publishedAt = null;

        // Si publié ou planifié, définir une date de publication
        if (in_array($status, ['published', 'scheduled'])) {
            $publishedAt = $status === 'published'
                ? $this->faker->dateTimeBetween('-1 month', 'now')
                : $this->faker->dateTimeBetween('+1 day', '+1 month');
        }

        return [
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraphs(rand(3, 8), true),
            'excerpt' => $this->faker->boolean(70) ? $this->faker->sentences(2, true) : null,
            'actuality_image' => $this->faker->optional(0.8)->imageUrl(800, 600, 'news', true),
            'slug' => $this->faker->unique()->slug(3),
            'status' => $status,
            'is_pinned' => $this->faker->boolean(20), // 20% de chance d'être épinglé
            'pin_order' => function (array $attributes) {
                return $attributes['is_pinned'] ? $this->faker->numberBetween(1, 10) : 0;
            },
            'share_count' => $this->faker->numberBetween(0, 1000),
            'views_count' => $this->faker->numberBetween(0, 5000),
            'like_count' => $this->faker->numberBetween(0, 500),
            'activity_id' => !empty($activityIds) && $this->faker->boolean(30)
                ? $this->faker->randomElement($activityIds)
                : null,
            'author_id' => !empty($adminIds) ? $this->faker->randomElement($adminIds) : null,
            'category_id' => !empty($categoryIds) && $this->faker->boolean(80)
                ? $this->faker->randomElement($categoryIds)
                : null,
            'published_at' => $publishedAt,
        ];
    }
}
