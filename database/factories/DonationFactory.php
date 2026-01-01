<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Member;
use App\Models\Project;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Donation>
 */
class DonationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $memberIds = Member::pluck('id')->toArray();
        $projectIds = Project::pluck('id')->toArray();

        $status = $this->faker->numberBetween(0, 2);
        $isAnonymous = $this->faker->boolean(20); // 20% de dons anonymes
        return [
            'project_id' => !empty($projectIds) && $this->faker->boolean(70)
                ? $this->faker->randomElement($projectIds)
                : null,
            'name' => $isAnonymous ? 'Anonymous' : $this->faker->name(),
            'email' => $isAnonymous ? 'anonymous@example.com' : $this->faker->safeEmail(),
            'phone' => $this->faker->optional(0.6)->phoneNumber(),
            'is_anonymous' => $isAnonymous,
            'dedication' => $this->faker->optional(0.3)->sentence(),
            'method' => $this->faker->numberBetween(1, 3), // 1=Card, 2=Mobile, 3=Bank
            'amount' => $this->faker->randomElement([10, 20, 50, 100, 200, 500, 1000]),
            'transaction_id' => $status === 1 ? 'TXN' . $this->faker->unique()->numerify('##########') : null,
            'status' => $status,
            'donor_id' => !$isAnonymous && !empty($memberIds) && $this->faker->boolean(60)
                ? $this->faker->randomElement($memberIds)
                : null,
        ];
    }
}
