<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wrestler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WrestlerAliasTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_add_alias_to_wrestler(): void
    {
        $this->actingAs(User::factory()->create());

        $wrestler = Wrestler::factory()->create();

        $response = $this->postJson("/api/wrestlers/{$wrestler->slug}/aliases", [
            'name' => 'El Generico',
            'is_primary' => true,
        ]);

        $response->assertOk()->assertJsonFragment(['message' => 'Alias added']);
        $this->assertEquals('El Generico', $wrestler->fresh()->primaryName?->name);
    }

    public function test_can_remove_alias_from_wrestler(): void
    {
        $this->actingAs(User::factory()->create());

        $wrestler = Wrestler::factory()->create();
        $alias = $wrestler->names()->create(['name' => 'Prince Puma', 'is_primary' => false]);

        $response = $this->deleteJson("/api/wrestlers/{$wrestler->slug}/aliases/{$alias->id}");

        $response->assertOk()->assertJsonFragment(['message' => 'Alias deleted']);
        $this->assertDatabaseMissing('wrestler_names', ['id' => $alias->id]);
    }
}
