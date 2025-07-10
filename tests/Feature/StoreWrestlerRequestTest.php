<?php

namespace Tests\Feature;

use App\Models\Promotion;
use App\Models\User;
use App\Models\Wrestler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreWrestlerRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_fails_if_no_primary_alias(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum'); // or just actingAs($user) if no guard

        // Arrange: create a promotion for valid promotion_ids
        $promotion = Promotion::factory()->create();

        $payload = [
            'real_name' => 'John Doe',
            'promotions' => [$promotion->id],
            'aliases' => [
                ['name' => 'Johnny', 'is_primary' => false],
                ['name' => 'J-Dog', 'is_primary' => false],
            ],
        ];

        // Act: post request to your store route (adjust URI as needed)
        $response = $this->postJson('/api/wrestlers', $payload);

        // Assert: validation fails with expected message
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['aliases'])
            ->assertJsonFragment([
                'At least one alias must be marked as primary.',
            ]);
    }

    public function test_passes_if_primary_alias_exists(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum'); // or just actingAs($user) if no guard

        // Arrange: create a promotion for valid promotion_ids
        $promotion = Promotion::factory()->create();

        $payload = [
            'real_name' => 'John Doe',
            'ring_name' => 'John Doe',
            'promotions' => [$promotion->id],
            'aliases' => [
                ['name' => 'Johnny', 'is_primary' => false],
                ['name' => 'J-Dog', 'is_primary' => true],
            ],
        ];

        // Act: post request
        $response = $this->postJson('/api/wrestlers', $payload);

        // Assert: no validation error on aliases
        $response->assertStatus(201);
        $response->assertJsonMissingValidationErrors('aliases');
    }

    public function test_created_by_and_updated_by_are_set_when_authenticated(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum'); // or just actingAs($user) if no guard

        $promotion = Promotion::factory()->create();

        $payload = [
            'real_name' => 'Jane Doe',
            'promotions' => [$promotion->id],
            'aliases' => [
                ['name' => 'Janie', 'is_primary' => true],
            ],
        ];

        $response = $this->postJson('/api/wrestlers', $payload);

        $response->assertStatus(201);

        $wrestlerId = $response->json('data.id');
        $wrestler   = Wrestler::with('names')->findOrFail($wrestlerId);

        $this->assertEquals($user->id, $wrestler->created_by);
        $this->assertEquals($user->id, $wrestler->updated_by);

        $primaryAlias = $wrestler->names->firstWhere('is_primary', true);
        $this->assertNotNull($primaryAlias);
        $this->assertEquals($user->id, $primaryAlias->created_by);
        $this->assertEquals($user->id, $primaryAlias->updated_by);
    }
}
