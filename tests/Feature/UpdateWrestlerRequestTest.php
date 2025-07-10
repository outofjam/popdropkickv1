<?php

namespace Tests\Feature;

use App\Models\Promotion;
use App\Models\User;
use App\Models\Wrestler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateWrestlerRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_wrestler_partial_fields_and_aliases_and_promotions(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum'); // or just actingAs($user) if no guard

        $promotion1 = Promotion::factory()->create();
        $promotion2 = Promotion::factory()->create();

        // Create a wrestler with initial data
        $wrestler = Wrestler::factory()->create([
            'real_name' => 'Old Real Name',
            'debut_date' => '2020-01-01',
            'country' => 'USA',
        ]);
        $wrestler->names()->create([
            'name' => 'Old Primary Name',
            'is_primary' => true,
        ]);
        $wrestler->promotions()->attach($promotion1);

        // Prepare update payload (partial update)
        $payload = [
            'real_name' => 'New Real Name', // Update real_name
            'aliases' => [
                ['name' => 'New Primary Name', 'is_primary' => true],
                ['name' => 'Secondary Alias', 'is_primary' => false],
            ],
            'promotions' => [$promotion2->id], // Swap promotion
            // 'active_promotions' omitted: should remain unchanged
        ];

        $response = $this->putJson("/api/wrestlers/{$wrestler->slug}", $payload);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'real_name' => 'New Real Name',
        ]);

        // Reload wrestler fresh from DB
        $wrestler->refresh();

        $this->assertEquals('New Real Name', $wrestler->real_name);

        // Assert aliases updated
        $aliases = $wrestler->names()->get()->keyBy('name');
        $this->assertTrue($aliases->has('New Primary Name'));
        $this->assertTrue($aliases->has('Secondary Alias'));
        $this->assertEquals(2, $aliases->count());

        // Reload wrestler fresh from DB and reload promotions relation
        $wrestler->refresh()->load('promotions');

        \Log::info('Wrestler promotions after update:', $wrestler->promotions->pluck('id')->toArray());

        $this->assertTrue($wrestler->promotions->contains('id', $promotion2->id));
        $this->assertFalse($wrestler->promotions->contains('id', $promotion1->id));

    }

    // Additional tests could cover validation, no alias change, empty promotions, etc.
}
