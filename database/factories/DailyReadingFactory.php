<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Admin;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DailyReading>
 */
class DailyReadingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

            $adminIds = Admin::pluck('id')->toArray();
            $status = $this->faker->randomElement(['draft', 'scheduled', 'published', 'archived']);

            // Dates cohérentes avec le statut
            $displayDate = $this->faker->dateTimeBetween('-1 year', '+1 year');

            if ($status === 'scheduled') {
                $displayDate = $this->faker->dateTimeBetween('+1 day', '+2 months');
            } elseif ($status === 'published') {
                $displayDate = $this->faker->dateTimeBetween('-6 months', 'now');
            }

            // Catégories liturgiques
            $liturgicalCategories = [
                'Temps ordinaire', 'Avent', 'Carême', 'Pâques',
                'Noël', 'Épiphanie', 'Pentecôte', 'Toussaint'
            ];

            // Références bibliques réalistes
            $books = ['Jn', 'Mt', 'Mc', 'Lc', 'Rm', '1 Co', '2 Co', 'Ga', 'Ep', 'Ph', 'Col', 'He'];
            $chapter = $this->faker->numberBetween(1, 20);
            $verseStart = $this->faker->numberBetween(1, 30);
            $verseEnd = $verseStart + $this->faker->numberBetween(0, 5);
        return [
            'display_date' => $displayDate->format('Y-m-d'),
            'verse' => $this->faker->sentence(15),
            'meditation' => $this->faker->paragraphs(3, true),
            'biblical_reference' => $this->faker->randomElement($books) . ' ' .
                                   $chapter . ':' . $verseStart . '-' . $verseEnd,
            'liturgical_category' => $this->faker->optional(0.8)->randomElement($liturgicalCategories),
            'status' => $status,
            'audio_url' => $this->faker->optional(0.3)->url(),
            'audio_duration' => function (array $attributes) {
                return $attributes['audio_url'] ? $this->faker->numberBetween(60, 600) : null;
            },
            'author_id' => !empty($adminIds) ? $this->faker->randomElement($adminIds) : null,
            'shares_count' => $this->faker->numberBetween(0, 200),
            'views_count' => $this->faker->numberBetween(0, 5000),
            'likes_count' => $this->faker->numberBetween(0, 1000),
        ];
    }
}
