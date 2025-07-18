<?php

namespace Tests\Feature;

use App\Models\Championship;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChampionshipApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_championship_for_promotion(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $promotion = Promotion::factory()->create();

        $payload = [
            'name' => 'World Heavyweight Championship',
            'weight_class' => 'Heavyweight',
            'introduced_at' => '2023-01-01',
            'active' => true,
        ];

        $response = $this->postJson("/api/promotions/{$promotion->id}/championships", $payload);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'World Heavyweight Championship',
                'weight_class' => 'Heavyweight',
                'active' => true,
            ]);

        $this->assertDatabaseHas('championships', [
            'promotion_id' => $promotion->id,
            'name' => 'World Heavyweight Championship',
        ]);
    }

    public function test_can_update_championship(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $championship = Championship::factory()->create([
            'name' => 'Old Name',
            'active' => true,
        ]);

        $payload = [
            'name' => 'New Championship Name',
            'active' => false,
        ];

        $response = $this->patchJson("/api/championships/{$championship->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'New Championship Name',
                'active' => false,
            ]);

        $this->assertDatabaseHas('championships', [
            'id' => $championship->id,
            'name' => 'New Championship Name',
            'active' => false,
        ]);
    }

    public function test_can_toggle_championship_active_status_by_slug(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $championship = Championship::factory()->create([
            'slug' => 'test-championship',
            'active' => true,
        ]);

        $response = $this->patchJson("/api/championships/{$championship->slug}/toggle-active");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'active' => false,
            ]);

        $this->assertDatabaseHas('championships', [
            'id' => $championship->id,
            'active' => false,
        ]);
    }
}
