<?php

namespace Database\Factories;

use App\Models\Wrestler;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class WrestlerFactory extends Factory
{
    protected $model = Wrestler::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'ring_name' => $this->faker->name(),
            'real_name' => $this->faker->name(),
            'debut_date' => $this->faker->date(),
            'country' => $this->faker->country(),
        ];
    }
}
