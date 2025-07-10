<?php

// tests/Feature/PromotionApiTest.php

namespace Tests\Feature;

use App\Models\Championship;
use App\Models\Promotion;
use App\Models\User;
use App\Models\Wrestler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromotionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_all_promotions_returns_expected_structure(): void
    {
        // Arrange: create promotion and wrestler with relationship
        $promotion = Promotion::factory()->create();
        $wrestler  = Wrestler::factory()->create();
        $promotion->wrestlers()->attach($wrestler);
        $promotion->activeWrestlers()->attach($wrestler);

        // Act
        $response = $this->getJson('/api/promotions');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [ // array of promotions
                        'id',
                        'name',
                        'abbreviation',
                        'country',

                        'active_wrestlers_count',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'meta' => ['status', 'timestamps'],
            ]);
    }

    public function test_show_promotion_by_id_returns_expected_structure(): void
    {
        $promotion = Promotion::factory()->create();

        $response = $this->getJson('/api/promotions/'.$promotion->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'abbreviation',
                    'country',

                    'active_wrestlers',

                ],
                'meta' => ['status', 'timestamps'],
            ]);
    }

    public function test_show_promotion_by_slug_returns_expected_structure(): void
    {
        $promotion = Promotion::factory()->create([
            'slug' => 'all-elite-wrestling',
        ]);

        $response = $this->getJson('/api/promotions/all-elite-wrestling');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'abbreviation',
                    'country',

                    'active_wrestlers',

                ],
                'meta' => ['status', 'timestamps'],
            ]);
    }

    public function test_show_promotion_not_found_returns_404(): void
    {
        $response = $this->getJson('/api/promotions/non-existent-slug');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Promotion not found',
                'meta' => ['status' => 404],
            ]);
    }

    public function test_can_store_new_promotion(): void
    {

        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum'); // or just actingAs($user) if no guard

        // Arrange: build valid promotion data
        $payload = [
            'name' => 'Ring of Honor',
            'abbreviation' => 'ROH',
            'country' => 'USA',
        ];

        // Act: send POST request to store endpoint
        $response = $this->postJson('/api/promotions', $payload);

        // Assert: check status and response structure
        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'abbreviation',
                    'country',
                    'active_wrestlers',

                ],
                'meta' => [
                    'status',
                    'timestamps',
                ],
            ]);

        $this->assertDatabaseHas('promotions', [
            'name' => 'Ring of Honor',
            'abbreviation' => 'ROH',
            'country' => 'USA',
        ]);
    }

    public function test_promotion_index_includes_active_championships_and_current_champion(): void
    {
        $promotion    = Promotion::factory()->create();
        $championship = Championship::factory()->create([
            'promotion_id' => $promotion->id,
            'active' => true,
        ]);
        $wrestler = Wrestler::factory()->create();

        $championship->titleReigns()->create([
            'wrestler_id' => $wrestler->id,
            'won_on' => now()->subYear(),
            'won_at' => 'WrestleMania',
            'reign_number' => 1,
            'win_type' => 'pinfall',
        ]);

        $response = $this->getJson('/api/promotions');

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $championship->id,
            'name' => $championship->name,
        ]);

        $response->assertJsonPath('data.0.active_championships.0.current_champion.id', $wrestler->id);
    }

    public function test_active_championship_with_no_current_reign_returns_vacant_status(): void
    {
        $promotion = Promotion::factory()->create();

        // Create active championship with no current reign (vacant)
        $championship = Championship::factory()->create([
            'promotion_id' => $promotion->id,
            'active' => true,
        ]);

        // Create a past (ended) reign, so no current reign exists
        $wrestler = Wrestler::factory()->create();
        $championship->titleReigns()->create([
            'wrestler_id' => $wrestler->id,
            'won_on' => now()->subYears(2),
            'won_at' => 'Past Event',
            'lost_on' => now()->subYear(),
            'lost_at' => 'Past Venue',
            'reign_number' => 1,
            'win_type' => 'pinfall',
        ]);

        $response = $this->getJson('/api/promotions');

        $response->assertStatus(200);

        $response->assertJsonPath('data.0.active_championships.0.current_champion.status', 'vacant');
    }

    public function test_promotion_index_with_no_active_championships_returns_empty_array(): void
    {
        $promotion = Promotion::factory()->create();
        Championship::factory()->create([
            'promotion_id' => $promotion->id,
            'active' => false, // not active
        ]);

        $response = $this->getJson('/api/promotions');

        $response->assertStatus(200);
        $response->assertJsonPath('data.0.active_championships', []);
    }
}
