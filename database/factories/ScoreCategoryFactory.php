<?php

namespace Database\Factories;

use App\Models\ScoreCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ScoreCategory>
 */
class ScoreCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'min_percentage' => 0.00,
            'max_percentage' => 100.00,
            'emoji' => '⭐',
            'color' => '#4F46E5',
            'description' => $this->faker->sentence(),
            'sort_order' => 1,
        ];
    }
}
