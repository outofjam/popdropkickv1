<?php

namespace Tests\Feature;

use App\Models\ChangeRequest;
use App\Models\User;
use App\Models\Wrestler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ChangeRequestModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_target_model_returns_the_underlying_wrestler(): void
    {
        $wrestler = Wrestler::factory()->create();
        $submitter = User::factory()->create();

        $changeRequest = ChangeRequest::create([
            'user_id' => $submitter->id,
            'action' => 'update',
            'model_type' => 'wrestler',
            'model_id' => $wrestler->id,
            'data' => ['real_name' => 'Updated'],
            'status' => 'pending',
        ]);

        $target = $changeRequest->getTargetModel();

        $this->assertInstanceOf(Wrestler::class, $target);
        $this->assertSame($wrestler->id, $target->id);
    }

    public function test_get_target_model_returns_null_when_there_is_no_model_id(): void
    {
        $submitter = User::factory()->create();

        $changeRequest = ChangeRequest::create([
            'user_id' => $submitter->id,
            'action' => 'create',
            'model_type' => 'wrestler',
            'data' => ['real_name' => 'New'],
            'status' => 'pending',
        ]);

        $this->assertNull($changeRequest->getTargetModel());
    }

    public function test_get_target_model_returns_null_for_an_unrecognized_model_type(): void
    {
        $submitter = User::factory()->create();

        $changeRequest = ChangeRequest::create([
            'user_id' => $submitter->id,
            'action' => 'update',
            'model_type' => 'not_a_real_type',
            'model_id' => (string) Str::uuid(),
            'data' => [],
            'status' => 'pending',
        ]);

        $this->assertNull($changeRequest->getTargetModel());
    }

    public function test_can_be_reviewed_is_true_only_while_pending(): void
    {
        $submitter = User::factory()->create();

        $pending = ChangeRequest::create([
            'user_id' => $submitter->id,
            'action' => 'create',
            'model_type' => 'wrestler',
            'data' => ['real_name' => 'Pending'],
            'status' => 'pending',
        ]);

        $approved = ChangeRequest::create([
            'user_id' => $submitter->id,
            'action' => 'create',
            'model_type' => 'wrestler',
            'data' => ['real_name' => 'Approved'],
            'status' => 'approved',
        ]);

        $rejected = ChangeRequest::create([
            'user_id' => $submitter->id,
            'action' => 'create',
            'model_type' => 'wrestler',
            'data' => ['real_name' => 'Rejected'],
            'status' => 'rejected',
        ]);

        $this->assertTrue($pending->canBeReviewed());
        $this->assertFalse($approved->canBeReviewed());
        $this->assertFalse($rejected->canBeReviewed());
    }

    public function test_pending_and_approved_scopes_filter_by_status(): void
    {
        $submitter = User::factory()->create();

        ChangeRequest::create([
            'user_id' => $submitter->id,
            'action' => 'create',
            'model_type' => 'wrestler',
            'data' => ['real_name' => 'Pending One'],
            'status' => 'pending',
        ]);

        ChangeRequest::create([
            'user_id' => $submitter->id,
            'action' => 'create',
            'model_type' => 'wrestler',
            'data' => ['real_name' => 'Pending Two'],
            'status' => 'pending',
        ]);

        ChangeRequest::create([
            'user_id' => $submitter->id,
            'action' => 'create',
            'model_type' => 'wrestler',
            'data' => ['real_name' => 'Approved One'],
            'status' => 'approved',
        ]);

        ChangeRequest::create([
            'user_id' => $submitter->id,
            'action' => 'create',
            'model_type' => 'wrestler',
            'data' => ['real_name' => 'Rejected One'],
            'status' => 'rejected',
        ]);

        $this->assertSame(2, ChangeRequest::pending()->count());
        $this->assertSame(1, ChangeRequest::approved()->count());
    }
}
