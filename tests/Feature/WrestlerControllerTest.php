<?php

namespace Tests\Feature;

use App\Models\Promotion;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WrestlerControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_auto_approved_user_creates_wrestler_directly(): void
    {
        $user = User::factory()->create([
            'role' => 'admin', // assuming admin can auto approve
        ]);
        $this->actingAs($user);

        $payload = [
            'real_name' => 'John Smith',
            'debut_date' => '1990-01-01',
            'ring_name' => 'Test Wrestler',
            'country' => 'USA',
            'promotions' => [
                'wwe',         // example promotion slug/string
                'nxt',
            ],
            'aliases' => [
                [
                    'name' => 'The Crusher',
                    'is_primary' => true,
                    'started_at' => '2010-01-01',
                    'ended_at' => null,
                ],
                [
                    'name' => 'Crusher X',
                    'is_primary' => false,
                    'started_at' => '2015-01-01',
                    'ended_at' => null,
                ],
            ],
            // Optionally add title reigns if needed
        ];


        $response = $this->postJson('/api/wrestlers', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.ring_name', 'The Crusher'); // Confirm API response shows primary alias

        $this->assertDatabaseHas('wrestler_names', [
            'wrestler_id' => $response->json('data.id'),
            'name' => 'The Crusher',
            'is_primary' => true,
        ]);

        // Optionally confirm no change requests were created
        $this->assertDatabaseMissing('change_requests', [
            'user_id' => $user->id,
        ]);

    }

    public function test_regular_user_creates_wrestler_change_request(): void
    {
        $user = User::factory()->create([
            'role' => 'user', // or role without auto-approve
        ]);
        $this->actingAs($user);

        $payload = [
            'real_name' => 'John Smith',
            'debut_date' => '1990-01-01',
            'country' => 'USA',
            'promotions' => [
                'wwe',         // example promotion slug/string
                'nxt',
            ],
            'aliases' => [
                [
                    'name' => 'The Crusher',
                    'is_primary' => true,
                    'started_at' => '2010-01-01',
                    'ended_at' => null,
                ],
                [
                    'name' => 'Crusher X',
                    'is_primary' => false,
                    'started_at' => '2015-01-01',
                    'ended_at' => null,
                ],
            ],
            // Optionally add title reigns if needed
        ];

        $response = $this->postJson('/api/wrestlers', $payload);

        $response->assertStatus(202)
            ->assertJsonStructure(['data' => ['change_request_id']])
            ->assertJsonFragment(['message' => 'Wrestler creation request submitted for review']);

        $this->assertDatabaseHas('change_requests', [
            'model_type' => 'wrestler',
            'action' => 'create',
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseCount('wrestlers', 0); // No wrestler created yet
    }
}
