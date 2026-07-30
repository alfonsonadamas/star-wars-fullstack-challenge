<?php

namespace Database\Factories;

use App\Models\Starship;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Starship>
 */
class StarshipFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'swapi_id' => fake()->numberBetween(1, 100),
            'name' => fake()->unique()->words(2, true),
            'max_atmosphering_speed' => fake()->numberBetween(100, 2000),
            'cargo_capacity' => fake()->numberBetween(100, 1000000),
        ];
    }
}
