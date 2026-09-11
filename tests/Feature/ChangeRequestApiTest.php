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

    public function test_index_returns_403_for_a_user_who_cannot_review(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user, 'sanctum');

        $this->getJson('/api/change-requests')->assertStatus(403);
    }

    public function test_show_returns_403_for_a_user_who_cannot_review(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user, 'sanctum');

        $changeRequest = ChangeRequest::create([
            'user_id' => User::factory()->create()->id,
            'action' => 'create',
            'model_type' => 'wrestler',
            'data' => ['real_name' => 'Test Wrestler'],
            'status' => 'pending',
        ]);

        $this->getJson("/api/change-requests/{$changeRequest->id}")->assertStatus(403);
    }

    public function test_approve_returns_403_for_a_user_who_cannot_review(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user, 'sanctum');

        $changeRequest = ChangeRequest::create([
            'user_id' => User::factory()->create()->id,
            'action' => 'create',
            'model_type' => 'wrestler',
            'data' => ['real_name' => 'Test Wrestler'],
            'status' => 'pending',
        ]);

        $this->postJson("/api/change-requests/{$changeRequest->id}/approve")->assertStatus(403);
    }

    public function test_reject_returns_403_for_a_user_who_cannot_review(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user, 'sanctum');

        $changeRequest = ChangeRequest::create([
            'user_id' => User::factory()->create()->id,
            'action' => 'create',
            'model_type' => 'wrestler',
            'data' => ['real_name' => 'Test Wrestler'],
            'status' => 'pending',
        ]);

        $this->postJson("/api/change-requests/{$changeRequest->id}/reject", ['comments' => 'No'])
            ->assertStatus(403);
    }

    public function test_bulk_approve_returns_403_for_a_user_who_cannot_review(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user, 'sanctum');

        $changeRequest = ChangeRequest::create([
            'user_id' => User::factory()->create()->id,
            'action' => 'create',
            'model_type' => 'wrestler',
            'data' => ['real_name' => 'Test Wrestler'],
            'status' => 'pending',
        ]);

        $this->postJson('/api/change-requests/bulk-approve', [
            'change_request_ids' => [$changeRequest->id],
        ])->assertStatus(403);
    }

    public function test_approve_endpoint_approves_pending_request_and_creates_the_wrestler(): void
    {
        $reviewer = User::factory()->create(['role' => 'admin']);
        $this->actingAs($reviewer, 'sanctum');

        $changeRequest = ChangeRequest::create([
            'user_id' => User::factory()->create()->id,
            'action' => 'create',
            'model_type' => 'wrestler',
            'data' => [
                'real_name' => 'Approved Wrestler',
                'aliases' => [['name' => 'Approved Wrestler', 'is_primary' => true]],
            ],
            'status' => 'pending',
        ]);

        $response = $this->postJson("/api/change-requests/{$changeRequest->id}/approve", [
            'comments' => 'Looks good',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.change_request.status', 'approved');

        $this->assertDatabaseHas('wrestlers', ['real_name' => 'Approved Wrestler']);
    }

    public function test_approve_endpoint_returns_422_when_change_request_already_reviewed(): void
    {
        $reviewer = User::factory()->create(['role' => 'admin']);
        $this->actingAs($reviewer, 'sanctum');

        $changeRequest = ChangeRequest::create([
            'user_id' => User::factory()->create()->id,
            'action' => 'create',
            'model_type' => 'wrestler',
            'data' => ['real_name' => 'Already Reviewed'],
            'status' => 'approved',
        ]);

        $this->postJson("/api/change-requests/{$changeRequest->id}/approve")
            ->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'Failed to approve change request: Change request has already been reviewed',
            ]);
    }

    public function test_reject_endpoint_requires_comments(): void
    {
        $reviewer = User::factory()->create(['role' => 'moderator']);
        $this->actingAs($reviewer, 'sanctum');

        $changeRequest = ChangeRequest::create([
            'user_id' => User::factory()->create()->id,
            'action' => 'create',
            'model_type' => 'wrestler',
            'data' => ['real_name' => 'Test Wrestler'],
            'status' => 'pending',
        ]);

        $this->postJson("/api/change-requests/{$changeRequest->id}/reject", [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('comments');
    }

    public function test_reject_endpoint_rejects_pending_request(): void
    {
        $reviewer = User::factory()->create(['role' => 'moderator']);
        $this->actingAs($reviewer, 'sanctum');

        $changeRequest = ChangeRequest::create([
            'user_id' => User::factory()->create()->id,
            'action' => 'create',
            'model_type' => 'wrestler',
            'data' => ['real_name' => 'Test Wrestler'],
            'status' => 'pending',
        ]);

        $this->postJson("/api/change-requests/{$changeRequest->id}/reject", ['comments' => 'Not good enough'])
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'rejected');

        $this->assertDatabaseMissing('wrestlers', ['real_name' => 'Test Wrestler']);
    }

    public function test_bulk_approve_endpoint_returns_approved_count_and_errors(): void
    {
        $reviewer = User::factory()->create(['role' => 'admin']);
        $this->actingAs($reviewer, 'sanctum');

        $submitter = User::factory()->create();

        $pending1 = ChangeRequest::create([
            'user_id' => $submitter->id,
            'action' => 'create',
            'model_type' => 'wrestler',
            'data' => ['real_name' => 'Bulk One', 'aliases' => [['name' => 'Bulk One', 'is_primary' => true]]],
            'status' => 'pending',
        ]);

        $pending2 = ChangeRequest::create([
            'user_id' => $submitter->id,
            'action' => 'create',
            'model_type' => 'wrestler',
            'data' => ['real_name' => 'Bulk Two', 'aliases' => [['name' => 'Bulk Two', 'is_primary' => true]]],
            'status' => 'pending',
        ]);

        $response = $this->postJson('/api/change-requests/bulk-approve', [
            'change_request_ids' => [$pending1->id, $pending2->id],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.approved_count', 2)
            ->assertJsonPath('data.errors', []);

        $this->assertSame('approved', $pending1->fresh()->status);
        $this->assertSame('approved', $pending2->fresh()->status);
    }
}
