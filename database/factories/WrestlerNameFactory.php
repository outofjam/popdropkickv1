<?php

namespace Database\Factories;

use App\Models\Wrestler;
use App\Models\WrestlerName;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WrestlerName>
 */
class WrestlerNameFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = WrestlerName::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'is_primary' => false, // Default to false, set true explicitly when needed
            'started_at' => $this->faker->dateTimeBetween('-5 years'),
            'ended_at' => null, // Most names are still active
            'wrestler_id' => Wrestler::factory(),
        ];
    }

    /**
     * Indicate that the wrestler name is a primary name.
     */
    public function primary(): static
    {
        return $this->state(static fn (array $attributes) => [
            'is_primary' => true,
        ]);
    }

    /**
     * Indicate that the wrestler name is retired/ended.
     */
    public function retired(): static
    {
        return $this->state(fn (array $attributes) => [
            'ended_at' => $this->faker->dateTimeBetween($attributes['started_at']),
        ]);
    }

    /**
     * Create a wrestling ring name.
     */
    public function ringName(): static
    {
        $wrestlingNames = [
            'The Rock', 'Stone Cold Steve Austin', 'The Undertaker', 'Triple H',
            'John Cena', 'CM Punk', 'Daniel Bryan', 'AJ Styles', 'Roman Reigns',
            'Seth Rollins', 'Dean Ambrose', 'Jon Moxley', 'Kenny Omega', 'Cody Rhodes',
            'Brock Lesnar', 'Randy Orton', 'Edge', 'Christian', 'Jeff Hardy',
            'Matt Hardy', 'Rey Mysterio', 'Eddie Guerrero', 'Chris Jericho',
        ];

        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->randomElement($wrestlingNames),
        ]);
    }
}
