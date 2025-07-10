<?php

namespace Database\Factories;

use App\Enums\WinType;
use App\Models\Championship;
use App\Models\TitleReign;
use App\Models\Wrestler;
use Illuminate\Database\Eloquent\Factories\Factory;

class TitleReignFactory extends Factory
{
    protected $model = TitleReign::class;

    public function definition(): array
    {
        $wonOn = $this->faker->dateTimeBetween('-10 years');
        $lostOn = $this->faker->optional(0.7)->dateTimeBetween($wonOn);

        return [
            'championship_id' => Championship::factory(),
            'wrestler_id' => Wrestler::factory(),

            'won_on' => $wonOn->format('Y-m-d'),
            'won_at' => $this->faker->sentence(2),

            'lost_on' => $lostOn?->format('Y-m-d'),
            'lost_at' => $lostOn ? $this->faker->sentence(2) : null,

            'win_type' => $this->faker->randomElement(WinType::cases())->value,
            'reign_number' => 1,
        ];
    }
}
