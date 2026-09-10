<?php

namespace Tests\Feature;

use App\Models\ChangeRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChangeRequestApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_returns_reviewed_at_as_iso8601_timestamp(): void
    {
        $reviewer = User::factory()->create(['role' => 'admin']);
        $this->actingAs($reviewer, 'sanctum');

        $changeRequest = ChangeRequest::create([
            'user_id' => User::factory()->create()->id,
            'action' => 'create',
            'model_type' => 'wrestler',
            'data' => ['real_name' => 'Test Wrestler'],
            'status' => 'approved',
            'reviewer_id' => $reviewer->id,
            'reviewed_at' => '2024-05-01 12:30:00',
        ]);

        $response = $this->getJson("/api/change-requests/{$changeRequest->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'reviewed_at' => '2024-05-01T12:30:00+00:00',
            ]);
    }

    public function test_index_returns_pending_status_and_null_reviewed_at(): void
    {
        $reviewer = User::factory()->create(['role' => 'moderator']);
        $this->actingAs($reviewer, 'sanctum');

        ChangeRequest::create([
            'user_id' => User::factory()->create()->id,
            'action' => 'create',
            'model_type' => 'wrestler',
            'data' => ['real_name' => 'Pending Wrestler'],
            'status' => 'pending',
        ]);

        $response = $this->getJson('/api/change-requests');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'status' => 'pending',
                'reviewed_at' => null,
            ]);
    }

    public function test_index_and_show_include_timestamps(): void
    {
        $reviewer = User::factory()->create(['role' => 'admin']);
        $this->actingAs($reviewer, 'sanctum');

        $changeRequest = ChangeRequest::create([
            'user_id' => User::factory()->create()->id,
            'action' => 'create',
            'model_type' => 'wrestler',
            'data' => ['real_name' => 'Test Wrestler'],
            'status' => 'pending',
        ]);

        $this->getJson('/api/change-requests')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['*' => ['created_at', 'updated_at']]]);

        $this->getJson("/api/change-requests/{$changeRequest->id}")
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['created_at', 'updated_at']]);
    }
}
