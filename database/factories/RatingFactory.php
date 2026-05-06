<?php

namespace Database\Factories;

use App\Enums\Unit;
use App\Models\Cocktail;
use App\Models\Ingredient;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Rating>
 */
class RatingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'rating' => 1,
            'comment' => fake()->word,
            'user_id' => User::factory()->create()->id,
            'cocktail_id' => Cocktail::factory()->create()->id,
        ];
    }
}
