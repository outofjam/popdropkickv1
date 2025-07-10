<?php

namespace Tests\Feature;

use App\Models\Promotion;
use App\Models\User;
use App\Models\Wrestler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateWrestlerPromotionsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Wrestler $wrestler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->wrestler = Wrestler::factory()->create();
        $this->actingAs($this->user, 'sanctum'); // Add the guard parameter
    }

    public function test_can_add_active_and_inactive_promotions_by_mixed_identifiers(): void
    {
        $inactive = Promotion::factory()->create(['slug' => 'roh']);
        $active = Promotion::factory()->create(['abbreviation' => 'WWE']);

        $payload = [
            'add_promotions' => [
                'inactive' => [$inactive->name],
                'active' => [$active->abbreviation],
            ],
        ];

        $response = $this->patchJson("/api/wrestlers/{$this->wrestler->slug}/promotions", $payload);

        $response->assertOk();
        $this->assertTrue($this->wrestler->fresh()->promotions->contains($inactive));
        $this->assertTrue($this->wrestler->fresh()->promotions->contains($active));
        $this->assertTrue($this->wrestler->fresh()->activePromotions->contains($active));
        $this->assertFalse($this->wrestler->fresh()->activePromotions->contains($inactive));
    }

    public function test_can_remove_promotions(): void
    {
        $promo1 = Promotion::factory()->create();
        $promo2 = Promotion::factory()->create(['slug' => 'impact']);
        $this->wrestler->promotions()->attach([$promo1->id, $promo2->id]);
        $this->wrestler->activePromotions()->attach($promo2);

        $payload = [
            'remove_promotions' => [$promo1->id, 'impact'],
        ];

        $response = $this->patchJson("/api/wrestlers/{$this->wrestler->slug}/promotions", $payload);
        $response->assertOk();

        $fresh = $this->wrestler->fresh();
        $this->assertFalse($fresh->promotions->contains($promo1));
        $this->assertFalse($fresh->promotions->contains($promo2));
        $this->assertFalse($fresh->activePromotions->contains($promo2));
    }

    public function test_can_deactivate_promotions(): void
    {
        $promo = Promotion::factory()->create(['abbreviation' => 'NXT']);
        $this->wrestler->promotions()->attach($promo);
        $this->wrestler->activePromotions()->attach($promo);

        $payload = [
            'deactivate_promotions' => ['NXT'],
        ];

        $response = $this->patchJson("/api/wrestlers/{$this->wrestler->slug}/promotions", $payload);
        $response->assertOk();

        $fresh = $this->wrestler->fresh();
        $this->assertTrue($fresh->promotions->contains($promo));
        $this->assertFalse($fresh->activePromotions->contains($promo));
    }

    public function test_can_add_and_remove_in_single_call(): void
    {
        $add = Promotion::factory()->create(['name' => 'MLW']);
        $remove = Promotion::factory()->create(['slug' => 'aew']);
        $this->wrestler->promotions()->attach($remove);
        $this->wrestler->activePromotions()->attach($remove);

        $payload = [
            'add_promotions' => ['inactive' => ['MLW']],
            'remove_promotions' => ['aew'],
        ];

        $response = $this->patchJson("/api/wrestlers/{$this->wrestler->slug}/promotions", $payload);
        $response->assertOk();

        $fresh = $this->wrestler->fresh();
        $this->assertTrue($fresh->promotions->contains($add));
        $this->assertFalse($fresh->promotions->contains($remove));
    }
}
