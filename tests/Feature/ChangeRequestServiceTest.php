<?php

namespace Tests\Feature;

use App\Models\ChangeRequest;
use App\Models\User;
use App\Models\Wrestler;
use App\Services\ChangeRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class ChangeRequestServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_approve_create_change_request_creates_wrestler_and_sets_reviewer_and_reputation(): void
    {
        $reviewer = User::factory()->create(['role' => 'admin']);
        $this->actingAs($reviewer, 'sanctum');

        $submitter = User::factory()->create(['reputation_score' => 10]);

        $changeRequest = ChangeRequest::create([
            'user_id' => $submitter->id,
            'action' => 'create',
            'model_type' => 'wrestler',
            'data' => [
                'real_name' => 'New Wrestler',
                'aliases' => [['name' => 'New Wrestler', 'is_primary' => true]],
            ],
            'status' => 'pending',
        ]);

        $result = app(ChangeRequestService::class)->approve($changeRequest);

        $this->assertInstanceOf(Wrestler::class, $result);
        $this->assertDatabaseHas('wrestlers', ['real_name' => 'New Wrestler']);

        $changeRequest->refresh();
        $this->assertSame('approved', $changeRequest->status);
        $this->assertSame($reviewer->id, $changeRequest->reviewer_id);
        $this->assertNotNull($changeRequest->reviewed_at);

        $this->assertSame(15, $submitter->fresh()->reputation_score);
    }

    public function test_approve_update_change_request_updates_the_wrestler(): void
    {
        $reviewer = User::factory()->create(['role' => 'admin']);
        $this->actingAs($reviewer, 'sanctum');

        $wrestler = Wrestler::factory()->create(['real_name' => 'Old Name']);
        $submitter = User::factory()->create();

        $changeRequest = ChangeRequest::create([
            'user_id' => $submitter->id,
            'action' => 'update',
            'model_type' => 'wrestler',
            'model_id' => $wrestler->id,
            'data' => ['real_name' => 'Updated Name'],
            'original_data' => $wrestler->toArray(),
            'status' => 'pending',
        ]);

        $result = app(ChangeRequestService::class)->approve($changeRequest);

        $this->assertInstanceOf(Wrestler::class, $result);
        $this->assertSame('Updated Name', $wrestler->fresh()->real_name);
        $this->assertSame('approved', $changeRequest->fresh()->status);
    }

    public function test_approve_delete_change_request_deletes_the_wrestler(): void
    {
        $reviewer = User::factory()->create(['role' => 'admin']);
        $this->actingAs($reviewer, 'sanctum');

        $wrestler = Wrestler::factory()->create();
        $submitter = User::factory()->create();

        $changeRequest = ChangeRequest::create([
            'user_id' => $submitter->id,
            'action' => 'delete',
            'model_type' => 'wrestler',
            'model_id' => $wrestler->id,
            'data' => [],
            'status' => 'pending',
        ]);

        app(ChangeRequestService::class)->approve($changeRequest);

        $this->assertDatabaseMissing('wrestlers', ['id' => $wrestler->id]);
        $this->assertSame('approved', $changeRequest->fresh()->status);
    }

    public function test_approve_throws_when_change_request_already_reviewed(): void
    {
        $reviewer = User::factory()->create(['role' => 'admin']);
        $this->actingAs($reviewer, 'sanctum');

        $submitter = User::factory()->create();

        $changeRequest = ChangeRequest::create([
            'user_id' => $submitter->id,
            'action' => 'create',
            'model_type' => 'wrestler',
            'data' => ['real_name' => 'Already Reviewed'],
            'status' => 'approved',
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Change request has already been reviewed');

        app(ChangeRequestService::class)->approve($changeRequest);
    }

    public function test_reject_sets_status_and_decreases_reputation(): void
    {
        $reviewer = User::factory()->create(['role' => 'moderator']);
        $this->actingAs($reviewer, 'sanctum');

        $submitter = User::factory()->create(['reputation_score' => 10]);

        $changeRequest = ChangeRequest::create([
            'user_id' => $submitter->id,
            'action' => 'create',
            'model_type' => 'wrestler',
            'data' => ['real_name' => 'Rejected Wrestler'],
            'status' => 'pending',
        ]);

        app(ChangeRequestService::class)->reject($changeRequest, ['comments' => 'Not enough info']);

        $changeRequest->refresh();
        $this->assertSame('rejected', $changeRequest->status);
        $this->assertSame($reviewer->id, $changeRequest->reviewer_id);
        $this->assertSame('Not enough info', $changeRequest->reviewer_comments);
        $this->assertNotNull($changeRequest->reviewed_at);

        $this->assertSame(8, $submitter->fresh()->reputation_score);
        $this->assertDatabaseMissing('wrestlers', ['real_name' => 'Rejected Wrestler']);
    }

    public function test_bulk_approve_approves_pending_requests_skips_already_reviewed_and_reports_errors_for_invalid_ids(): void
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

        $alreadyApproved = ChangeRequest::create([
            'user_id' => $submitter->id,
            'action' => 'create',
            'model_type' => 'wrestler',
            'data' => ['real_name' => 'Already Approved'],
            'status' => 'approved',
            'reviewer_id' => $reviewer->id,
            'reviewed_at' => now(),
        ]);

        $invalidId = 999999;

        $result = app(ChangeRequestService::class)->bulkApprove([
            $pending1->id,
            $pending2->id,
            $alreadyApproved->id,
            $invalidId,
        ]);

        // Only the two truly pending requests are approved.
        $this->assertCount(2, $result['results']);

        // The already-approved request is silently skipped (no error, not
        // re-counted); only the nonexistent id surfaces as an error.
        $this->assertCount(1, $result['errors']);
        $this->assertStringContainsString("ID {$invalidId}", $result['errors'][0]);

        $this->assertSame('approved', $pending1->fresh()->status);
        $this->assertSame('approved', $pending2->fresh()->status);
        $this->assertSame($reviewer->id, $pending1->fresh()->reviewer_id);
    }
}
