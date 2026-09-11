<?php

namespace Tests\Feature;

use App\Models\ChangeRequest;
use App\Models\Promotion;
use App\Models\User;
use App\Models\Wrestler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WrestlerAutoApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_auto_approves_and_creates_wrestler_directly_for_a_moderator(): void
    {
        $moderator = User::factory()->create(['role' => 'moderator']);
        $this->actingAs($moderator, 'sanctum');

        $promotion = Promotion::factory()->create();

        $response = $this->postJson('/api/wrestlers', [
            'real_name' => 'Direct Create',
            'promotions' => [$promotion->id],
            'aliases' => [['name' => 'Direct Create', 'is_primary' => true]],
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('wrestlers', ['real_name' => 'Direct Create']);
        $this->assertSame(0, ChangeRequest::count());
    }

    public function test_store_submits_a_change_request_instead_of_creating_a_wrestler_for_a_regular_user(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user, 'sanctum');

        $promotion = Promotion::factory()->create();

        $response = $this->postJson('/api/wrestlers', [
            'real_name' => 'Needs Review',
            'promotions' => [$promotion->id],
            'aliases' => [['name' => 'Needs Review', 'is_primary' => true]],
        ]);

        $response->assertStatus(202)
            ->assertJsonStructure(['data' => ['change_request_id']]);

        $this->assertDatabaseMissing('wrestlers', ['real_name' => 'Needs Review']);

        $changeRequest = ChangeRequest::sole();
        $this->assertSame('pending', $changeRequest->status);
        $this->assertSame('create', $changeRequest->action);
        $this->assertSame('wrestler', $changeRequest->model_type);
        $this->assertSame($user->id, $changeRequest->user_id);
    }

    public function test_update_auto_approves_and_updates_the_wrestler_directly_for_a_trusted_user(): void
    {
        $trusted = User::factory()->create(['role' => 'trusted', 'reputation_score' => 150]);
        $this->actingAs($trusted, 'sanctum');

        $wrestler = Wrestler::factory()->create(['real_name' => 'Old Name']);

        $response = $this->putJson("/api/wrestlers/{$wrestler->slug}", ['real_name' => 'New Name']);

        $response->assertStatus(200);
        $this->assertSame('New Name', $wrestler->fresh()->real_name);
        $this->assertSame(0, ChangeRequest::count());
    }

    public function test_update_submits_a_change_request_instead_of_updating_the_wrestler_for_a_regular_user(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user, 'sanctum');

        $wrestler = Wrestler::factory()->create(['real_name' => 'Old Name']);

        $response = $this->putJson("/api/wrestlers/{$wrestler->slug}", ['real_name' => 'New Name']);

        $response->assertStatus(202)
            ->assertJsonStructure(['data' => ['change_request_id']]);

        $this->assertSame('Old Name', $wrestler->fresh()->real_name);

        $changeRequest = ChangeRequest::sole();
        $this->assertSame('pending', $changeRequest->status);
        $this->assertSame('update', $changeRequest->action);
        $this->assertSame($wrestler->id, $changeRequest->model_id);
    }
}
