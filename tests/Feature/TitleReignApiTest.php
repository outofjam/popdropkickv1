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
