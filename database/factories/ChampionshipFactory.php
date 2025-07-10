<?php

namespace Database\Factories;

use App\Models\Championship;
use App\Models\Promotion;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChampionshipFactory extends Factory
{
    protected $model = Championship::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->catchPhrase,
            'promotion_id' => Promotion::factory(),
            'introduced_at' => $this->faker->dateTimeBetween('-20 years'),
            'weight_class' => $this->faker->randomElement(['Heavyweight', 'Junior Heavyweight', 'Cruiserweight', 'Openweight', null]),
            'active' => $this->faker->boolean(80), // 80% chance active
        ];
    }
}
