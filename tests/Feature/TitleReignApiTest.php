<?php

namespace Tests\Feature;

use App\Models\Championship;
use App\Models\TitleReign;
use App\Models\User;
use App\Models\Wrestler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TitleReignApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_title_reign(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum'); // or just actingAs($user) if no guard

        $wrestler     = Wrestler::factory()->create();
        $championship = Championship::factory()->create();

        $payload = [
            'championship_id' => $championship->id,
            'won_on' => '2021-01-01',
            'won_at' => 'Event Name',
            'lost_on' => '2021-06-01',
            'lost_at' => 'Event Name',
            'win_type' => 'pinfall',
            'reign_number' => 1,
        ];

        $response = $this->postJson("/api/wrestlers/{$wrestler->slug}/title-reigns", $payload);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'wrestler_id' => $wrestler->id,
            'championship_id' => $championship->id,
        ]);

    }

    public function test_store_title_reign_with_vacancy_reason(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $wrestler     = Wrestler::factory()->create();
        $championship = Championship::factory()->create();

        $payload = [
            'championship_id' => $championship->id,
            'won_on' => '2021-01-01',
            'won_at' => 'Event Name',
            'lost_on' => '2021-06-01',
            'vacancy_reason' => 'Stripped for failing a drug test',
            'win_type' => 'vacated',
        ];

        $response = $this->postJson("/api/wrestlers/{$wrestler->slug}/title-reigns", $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('title_reigns', [
            'wrestler_id' => $wrestler->id,
            'championship_id' => $championship->id,
            'vacancy_reason' => 'Stripped for failing a drug test',
        ]);
    }

    public function test_update_title_reign_sets_vacancy_reason(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $titleReign = TitleReign::factory()->create([
            'lost_on' => '2021-06-01',
            'lost_at' => null,
        ]);

        $payload = ['vacancy_reason' => 'Relinquished due to injury'];

        $response = $this->patchJson("/api/title-reigns/{$titleReign->id}", $payload);

        $response->assertStatus(200);

        $this->assertDatabaseHas('title_reigns', [
            'id' => $titleReign->id,
            'vacancy_reason' => 'Relinquished due to injury',
        ]);
    }

    public function test_update_title_reign(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum'); // or just actingAs($user) if no guard
        $titleReign = TitleReign::factory()->create([
            'won_on' => '2021-01-01',
            'won_at' => 'Event Name',
            'lost_on' => '2021-06-01',
            'lost_at' => 'Event Name',
            'win_type' => 'pinfall',
            'reign_number' => 1,

        ]);

        $payload = ['win_type' => 'submission'];

        $response = $this->patchJson("/api/title-reigns/{$titleReign->id}", $payload);

        $response->assertJson([
            'message' => 'Title Reign Updated',
            'meta' => [
                'status' => 200,
                // optionally check timestamps or skip them
            ],
            // 'data' can be empty array or whatever you expect
        ]);

        $this->assertDatabaseHas('title_reigns', [
            'id' => $titleReign->id,
            'win_type' => 'submission', // <-- updated value
        ]);

    }

    public function test_delete_title_reign(): void
    {

        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum'); // or just actingAs($user) if no guard

        $titleReign = TitleReign::factory()->create();

        $response = $this->deleteJson("/api/title-reigns/{$titleReign->id}");

        $data = $response->json();
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Title Reign Deleted',
            ]);

        $this->assertDatabaseMissing('title_reigns', ['id' => $titleReign->id]);
    }

    public function test_deleting_a_reign_renumbers_the_remaining_reigns(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $wrestler = Wrestler::factory()->create();
        $championship = Championship::factory()->create();

        $reign1 = TitleReign::factory()->create([
            'wrestler_id' => $wrestler->id,
            'championship_id' => $championship->id,
            'won_on' => '2020-01-01',
            'lost_on' => '2020-02-01',
            'reign_number' => 1,
        ]);
        $reign2 = TitleReign::factory()->create([
            'wrestler_id' => $wrestler->id,
            'championship_id' => $championship->id,
            'won_on' => '2020-03-01',
            'lost_on' => '2020-04-01',
            'reign_number' => 2,
        ]);
        $reign3 = TitleReign::factory()->create([
            'wrestler_id' => $wrestler->id,
            'championship_id' => $championship->id,
            'won_on' => '2020-05-01',
            'lost_on' => '2020-06-01',
            'reign_number' => 3,
        ]);

        $response = $this->deleteJson("/api/title-reigns/{$reign2->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('title_reigns', ['id' => $reign2->id]);
        $this->assertEquals(1, $reign1->fresh()->reign_number);
        $this->assertEquals(2, $reign3->fresh()->reign_number);
    }

    public function test_wrestler_can_hold_multiple_active_title_reigns_simultaneously(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $wrestler = Wrestler::factory()->create();
        $championshipA = Championship::factory()->create(['name' => 'Championship A']);
        $championshipB = Championship::factory()->create(['name' => 'Championship B']);

        $this->postJson("/api/wrestlers/{$wrestler->slug}/title-reigns", [
            'championship_id' => $championshipA->id,
            'won_on' => '2021-01-01',
            'win_type' => 'pinfall',
        ])->assertStatus(201);

        $this->postJson("/api/wrestlers/{$wrestler->slug}/title-reigns", [
            'championship_id' => $championshipB->id,
            'won_on' => '2021-02-01',
            'win_type' => 'pinfall',
        ])->assertStatus(201);

        $response = $this->getJson("/api/wrestlers/{$wrestler->slug}");

        $response->assertStatus(200);

        $activeTitleReigns = $response->json('data.active_title_reigns');

        $this->assertCount(2, $activeTitleReigns);
        $this->assertEqualsCanonicalizing(
            [$championshipA->id, $championshipB->id],
            array_column($activeTitleReigns, 'championship_id')
        );
    }

    public function test_reign_numbers_are_renumbered_on_out_of_order_creation(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $wrestler     = Wrestler::factory()->create();
        $championship = Championship::factory()->create();

        // Create reign with won_on 2021-06-01 first
        $this->postJson("/api/wrestlers/{$wrestler->slug}/title-reigns", [
            'championship_id' => $championship->id,
            'won_on' => '2021-06-01',
            'won_at' => 'Event 1',
            'win_type' => 'pinfall',  // <-- add the required win_type here
        ]);

        // Then create reign with earlier won_on 2021-01-01
        $response = $this->postJson("/api/wrestlers/{$wrestler->slug}/title-reigns", [
            'championship_id' => $championship->id,
            'won_on' => '2021-01-01',
            'won_at' => 'Event 2',
            'win_type' => 'pinfall',  // <-- add the required win_type here
        ]);

        $response->assertStatus(201);

        $reigns = TitleReign::where('wrestler_id', $wrestler->id)
            ->where('championship_id', $championship->id)
            ->orderBy('won_on')
            ->get();

        $this->assertEquals(1, $reigns[0]->reign_number); // won_on = 2021-01-01
        $this->assertEquals(2, $reigns[1]->reign_number); // won_on = 2021-06-01
    }
}
