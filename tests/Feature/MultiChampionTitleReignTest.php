<?php

namespace Tests\Feature;

use App\Models\Championship;
use App\Models\Team;
use App\Models\TitleReign;
use App\Models\User;
use App\Models\Wrestler;
use App\Models\WrestlerName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiChampionTitleReignTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_title_reign_with_participants_creates_co_champion_rows(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $wrestlerA = Wrestler::factory()->create();
        $wrestlerB = Wrestler::factory()->create();
        $championship = Championship::factory()->create();
        $team = Team::factory()->create(['name' => 'The New Day']);

        $response = $this->postJson("/api/wrestlers/{$wrestlerA->slug}/title-reigns", [
            'championship_id' => $championship->id,
            'team_id' => $team->id,
            'won_on' => '2021-01-01',
            'win_type' => 'pinfall',
            'participants' => [
                ['wrestler_id' => $wrestlerB->id],
            ],
        ]);

        $response->assertStatus(201);

        $reign = TitleReign::where('championship_id', $championship->id)->firstOrFail();

        $this->assertEquals($team->id, $reign->team_id);
        $this->assertCount(2, $reign->titleReignWrestlers);
        $this->assertEqualsCanonicalizing(
            [$wrestlerA->id, $wrestlerB->id],
            $reign->titleReignWrestlers->pluck('wrestler_id')->all()
        );
    }

    public function test_participant_alias_must_belong_to_that_participant(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $wrestlerA = Wrestler::factory()->create();
        $wrestlerB = Wrestler::factory()->create();
        $foreignAlias = WrestlerName::factory()->create(['wrestler_id' => $wrestlerA->id]);
        $championship = Championship::factory()->create();

        $response = $this->postJson("/api/wrestlers/{$wrestlerA->slug}/title-reigns", [
            'championship_id' => $championship->id,
            'won_on' => '2021-01-01',
            'win_type' => 'pinfall',
            'participants' => [
                ['wrestler_id' => $wrestlerB->id, 'wrestler_name_id_at_win' => $foreignAlias->id],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['participants.0.wrestler_name_id_at_win']);
    }

    public function test_participant_cannot_duplicate_the_route_wrestler(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $wrestler = Wrestler::factory()->create();
        $championship = Championship::factory()->create();

        $response = $this->postJson("/api/wrestlers/{$wrestler->slug}/title-reigns", [
            'championship_id' => $championship->id,
            'won_on' => '2021-01-01',
            'win_type' => 'pinfall',
            'participants' => [
                ['wrestler_id' => $wrestler->id],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['participants.0.wrestler_id']);
    }

    public function test_each_co_champion_gets_their_own_reign_number_for_the_championship(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $kofi = Wrestler::factory()->create();
        $woods = Wrestler::factory()->create();
        $championship = Championship::factory()->create();

        // Kofi's earlier solo reign with this championship.
        TitleReign::factory()->create([
            'wrestler_id' => $kofi->id,
            'championship_id' => $championship->id,
            'won_on' => '2019-01-01',
            'lost_on' => '2019-06-01',
        ]);

        // Now Kofi and Woods win it together as a tag reign.
        $this->postJson("/api/wrestlers/{$kofi->slug}/title-reigns", [
            'championship_id' => $championship->id,
            'won_on' => '2021-01-01',
            'win_type' => 'pinfall',
            'participants' => [
                ['wrestler_id' => $woods->id],
            ],
        ])->assertStatus(201);

        $tagReign = TitleReign::where('championship_id', $championship->id)
            ->whereDate('won_on', '2021-01-01')
            ->firstOrFail();

        $kofiParticipant = $tagReign->titleReignWrestlers->firstWhere('wrestler_id', $kofi->id);
        $woodsParticipant = $tagReign->titleReignWrestlers->firstWhere('wrestler_id', $woods->id);

        // Kofi's second reign with this title; Woods' first.
        $this->assertEquals(2, $kofiParticipant->reign_number);
        $this->assertEquals(1, $woodsParticipant->reign_number);

        // The legacy shared column still tracks the primary (route) wrestler's number.
        $this->assertEquals(2, $tagReign->fresh()->reign_number);
    }

    public function test_updating_participants_replaces_co_champions(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $primary = Wrestler::factory()->create();
        $original = Wrestler::factory()->create();
        $replacement = Wrestler::factory()->create();
        $championship = Championship::factory()->create();

        $reign = TitleReign::factory()->create([
            'wrestler_id' => $primary->id,
            'championship_id' => $championship->id,
        ]);
        $reign->titleReignWrestlers()->create([
            'wrestler_id' => $original->id,
            'is_primary' => false,
        ]);

        $response = $this->patchJson("/api/title-reigns/{$reign->id}", [
            'participants' => [
                ['wrestler_id' => $replacement->id],
            ],
        ]);

        $response->assertStatus(200);

        $wrestlerIds = $reign->fresh()->titleReignWrestlers->pluck('wrestler_id')->all();
        $this->assertEqualsCanonicalizing([$primary->id, $replacement->id], $wrestlerIds);
    }

    public function test_deleting_a_multi_champion_reign_renumbers_every_participant(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $kofi = Wrestler::factory()->create();
        $woods = Wrestler::factory()->create();
        $championship = Championship::factory()->create();

        $earlierReign = TitleReign::factory()->create([
            'wrestler_id' => $kofi->id,
            'championship_id' => $championship->id,
            'won_on' => '2019-01-01',
            'lost_on' => '2019-06-01',
        ]);

        $reignToDelete = TitleReign::factory()->create([
            'wrestler_id' => $kofi->id,
            'championship_id' => $championship->id,
            'won_on' => '2020-01-01',
            'lost_on' => '2020-06-01',
        ]);
        $reignToDelete->titleReignWrestlers()->create(['wrestler_id' => $woods->id]);

        $laterReign = TitleReign::factory()->create([
            'wrestler_id' => $kofi->id,
            'championship_id' => $championship->id,
            'won_on' => '2021-01-01',
        ]);

        $this->deleteJson("/api/title-reigns/{$reignToDelete->id}")->assertStatus(200);

        $this->assertEquals(1, $earlierReign->fresh()->reign_number);
        $this->assertEquals(2, $laterReign->fresh()->reign_number);
    }

    public function test_current_champions_lists_every_co_champion_and_the_team(): void
    {
        $wrestlerA = Wrestler::factory()->create();
        $wrestlerB = Wrestler::factory()->create();
        $championship = Championship::factory()->create();
        $team = Team::factory()->create(['name' => 'The New Day']);

        $reign = TitleReign::factory()->create([
            'wrestler_id' => $wrestlerA->id,
            'championship_id' => $championship->id,
            'team_id' => $team->id,
            'won_on' => now()->subMonth(),
            'lost_on' => null,
        ]);
        $reign->titleReignWrestlers()->create(['wrestler_id' => $wrestlerB->id]);

        $response = $this->getJson("/api/championships/{$championship->slug}");

        $response->assertStatus(200);

        $champions = $response->json('data.current_champions');

        $this->assertCount(2, $champions);
        $this->assertEqualsCanonicalizing(
            [$wrestlerA->id, $wrestlerB->id],
            array_column($champions, 'id')
        );
        $this->assertEquals('The New Day', $champions[0]['team']['name']);
    }

    public function test_current_champions_is_an_empty_array_when_vacant(): void
    {
        $championship = Championship::factory()->create();

        $response = $this->getJson("/api/championships/{$championship->slug}");

        $response->assertStatus(200)
            ->assertJsonPath('data.current_champions', []);
    }
}
