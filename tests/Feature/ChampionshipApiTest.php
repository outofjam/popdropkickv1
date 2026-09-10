<?php

namespace Tests\Feature;

use App\Models\Championship;
use App\Models\Promotion;
use App\Models\User;
use App\Models\Wrestler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChampionshipApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_championships_across_promotions(): void
    {
        $promotionA = Promotion::factory()->create(['name' => 'Promotion A']);
        $promotionB = Promotion::factory()->create(['name' => 'Promotion B']);

        $championshipA = Championship::factory()->create(['promotion_id' => $promotionA->id]);
        $championshipB = Championship::factory()->create(['promotion_id' => $promotionB->id]);

        $response = $this->getJson('/api/championships');

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $championshipA->id])
            ->assertJsonFragment(['id' => $championshipB->id]);
    }

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

    public function test_show_includes_distinct_title_holders_count(): void
    {
        $championship = Championship::factory()->create();
        $wrestlerA = Wrestler::factory()->create();
        $wrestlerB = Wrestler::factory()->create();

        // Two reigns for the same wrestler should count as one title holder.
        $championship->titleReigns()->create([
            'wrestler_id' => $wrestlerA->id,
            'won_on' => '2020-01-01',
            'lost_on' => '2020-01-06',
            'win_type' => 'pinfall',
            'reign_number' => 1,
        ]);
        $championship->titleReigns()->create([
            'wrestler_id' => $wrestlerA->id,
            'won_on' => '2020-02-01',
            'lost_on' => '2020-02-04',
            'win_type' => 'pinfall',
            'reign_number' => 2,
        ]);
        $championship->titleReigns()->create([
            'wrestler_id' => $wrestlerB->id,
            'won_on' => '2021-01-01',
            'lost_on' => '2021-01-10',
            'win_type' => 'pinfall',
            'reign_number' => 1,
        ]);

        $response = $this->getJson("/api/championships/{$championship->slug}");

        $response->assertStatus(200)
            ->assertJsonPath('meta.counts.title_reigns', 3)
            ->assertJsonPath('meta.counts.title_holders', 2);
    }

    public function test_show_includes_reign_statistics(): void
    {
        $championship = Championship::factory()->create();
        $frequentChamp = Wrestler::factory()->create();
        $longestReigning = Wrestler::factory()->create();

        // Two short reigns for the same wrestler -> most_reigns.
        $championship->titleReigns()->create([
            'wrestler_id' => $frequentChamp->id,
            'won_on' => '2020-01-01',
            'lost_on' => '2020-01-06', // 5 days
            'win_type' => 'pinfall',
            'reign_number' => 1,
        ]);
        $championship->titleReigns()->create([
            'wrestler_id' => $frequentChamp->id,
            'won_on' => '2020-02-01',
            'lost_on' => '2020-02-04', // 3 days -> shortest_reign
            'win_type' => 'pinfall',
            'reign_number' => 2,
        ]);

        // One long reign for a different wrestler -> longest_reign.
        $championship->titleReigns()->create([
            'wrestler_id' => $longestReigning->id,
            'won_on' => '2021-01-01',
            'lost_on' => '2021-03-01', // 59 days
            'win_type' => 'pinfall',
            'reign_number' => 1,
        ]);

        $response = $this->getJson("/api/championships/{$championship->slug}");

        $response->assertStatus(200)
            ->assertJsonPath('meta.reign_stats.longest_reign.wrestler.id', $longestReigning->id)
            ->assertJsonPath('meta.reign_stats.longest_reign.reign_length_in_days', 59)
            ->assertJsonPath('meta.reign_stats.shortest_reign.wrestler.id', $frequentChamp->id)
            ->assertJsonPath('meta.reign_stats.shortest_reign.reign_length_in_days', 3)
            ->assertJsonPath('meta.reign_stats.most_reigns.wrestler.id', $frequentChamp->id)
            ->assertJsonPath('meta.reign_stats.most_reigns.reign_count', 2);
    }

    public function test_show_reign_statistics_are_null_with_no_reigns(): void
    {
        $championship = Championship::factory()->create();

        $response = $this->getJson("/api/championships/{$championship->slug}");

        $response->assertStatus(200)
            ->assertJsonPath('meta.reign_stats.longest_reign', null)
            ->assertJsonPath('meta.reign_stats.shortest_reign', null)
            ->assertJsonPath('meta.reign_stats.most_reigns', null);
    }

    public function test_show_and_index_include_timestamps(): void
    {
        $championship = Championship::factory()->create();

        $this->getJson("/api/championships/{$championship->slug}")
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['created_at', 'updated_at']]);

        $this->getJson('/api/championships')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['*' => ['created_at', 'updated_at']]]);
    }

    public function test_show_includes_introduced_at(): void
    {
        $championship = Championship::factory()->create([
            'introduced_at' => '2001-03-15',
        ]);

        $response = $this->getJson("/api/championships/{$championship->slug}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'introduced_at' => '2001-03-15',
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
