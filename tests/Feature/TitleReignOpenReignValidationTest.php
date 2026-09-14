<?php

namespace Tests\Feature;

use App\Models\Championship;
use App\Models\TitleReign;
use App\Models\User;
use App\Models\Wrestler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TitleReignOpenReignValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_create_an_open_reign_when_one_already_exists_for_the_championship(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $championship = Championship::factory()->create();
        TitleReign::factory()->create([
            'championship_id' => $championship->id,
            'won_on' => '2024-01-01',
            'lost_on' => null,
        ]);

        $challenger = Wrestler::factory()->create();

        $response = $this->postJson("/api/wrestlers/{$challenger->slug}/title-reigns", [
            'championship_id' => $championship->id,
            'won_on' => '2024-06-01',
            'win_type' => 'pinfall',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['lost_on']);
    }

    public function test_can_backfill_a_closed_historical_reign_even_when_an_open_reign_exists(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $championship = Championship::factory()->create();
        TitleReign::factory()->create([
            'championship_id' => $championship->id,
            'won_on' => '2024-01-01',
            'lost_on' => null,
        ]);

        $historicalChamp = Wrestler::factory()->create();

        $response = $this->postJson("/api/wrestlers/{$historicalChamp->slug}/title-reigns", [
            'championship_id' => $championship->id,
            'won_on' => '2010-01-01',
            'lost_on' => '2010-06-01',
            'win_type' => 'pinfall',
        ]);

        $response->assertStatus(201);
    }

    public function test_can_create_an_open_reign_when_the_championship_has_no_open_reign(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $championship = Championship::factory()->create();
        $wrestler = Wrestler::factory()->create();

        $response = $this->postJson("/api/wrestlers/{$wrestler->slug}/title-reigns", [
            'championship_id' => $championship->id,
            'won_on' => '2024-06-01',
            'win_type' => 'pinfall',
        ]);

        $response->assertStatus(201);
    }

    public function test_cannot_reopen_a_reign_when_another_open_reign_already_exists(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $championship = Championship::factory()->create();
        TitleReign::factory()->create([
            'championship_id' => $championship->id,
            'won_on' => '2024-01-01',
            'lost_on' => null,
        ]);

        $closedReign = TitleReign::factory()->create([
            'championship_id' => $championship->id,
            'won_on' => '2020-01-01',
            'lost_on' => '2020-06-01',
        ]);

        $response = $this->patchJson("/api/title-reigns/{$closedReign->id}", [
            'lost_on' => null,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['lost_on']);
    }

    public function test_can_close_the_currently_open_reign(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $championship = Championship::factory()->create();
        $openReign = TitleReign::factory()->create([
            'championship_id' => $championship->id,
            'won_on' => '2024-01-01',
            'lost_on' => null,
        ]);

        $response = $this->patchJson("/api/title-reigns/{$openReign->id}", [
            'lost_on' => '2024-08-01',
            'lost_at' => 'Some Event',
        ]);

        $response->assertStatus(200);
    }

    public function test_moving_an_open_reign_to_a_championship_that_already_has_one_fails(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $otherChampionship = Championship::factory()->create();
        TitleReign::factory()->create([
            'championship_id' => $otherChampionship->id,
            'won_on' => '2024-01-01',
            'lost_on' => null,
        ]);

        $thisChampionship = Championship::factory()->create();
        $movingReign = TitleReign::factory()->create([
            'championship_id' => $thisChampionship->id,
            'won_on' => '2024-03-01',
            'lost_on' => null,
        ]);

        $response = $this->patchJson("/api/title-reigns/{$movingReign->id}", [
            'championship_id' => $otherChampionship->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['lost_on']);
    }

    public function test_updating_an_unrelated_field_on_an_open_reign_does_not_trigger_the_check(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $championship = Championship::factory()->create();
        $openReign = TitleReign::factory()->create([
            'championship_id' => $championship->id,
            'won_on' => '2024-01-01',
            'lost_on' => null,
        ]);

        $response = $this->patchJson("/api/title-reigns/{$openReign->id}", [
            'win_type' => 'submission',
        ]);

        $response->assertStatus(200);
    }
}
