<?php

namespace Tests\Feature;

use App\Models\Promotion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChampionshipApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_championship_for_promotion(): void
    {
        // Create user and authenticate
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        // Create a promotion
        $promotion = Promotion::factory()->create();

        $payload = [
            'name' => 'World Heavyweight Championship',
            'weight_class' => 'Heavyweight',
            'introduced_at' => '2023-01-01',
            'active' => true,
        ];

        // Call the API endpoint
        $response = $this->postJson("/api/promotions/{$promotion->id}/championships", $payload);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'World Heavyweight Championship',
                'weight_class' => 'Heavyweight',
                'active' => true,
            ]);

        // Assert the championship exists in database
        $this->assertDatabaseHas('championships', [
            'promotion_id' => $promotion->id,
            'name' => 'World Heavyweight Championship',
        ]);
    }
}
