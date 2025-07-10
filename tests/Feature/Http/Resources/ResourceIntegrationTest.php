<?php

namespace Tests\Feature\Http\Resources;

use App\Http\Resources\ChampionshipResource;
use App\Http\Resources\PromotionResource;
use App\Http\Resources\WrestlerResource;
use App\Models\Championship;
use App\Models\Promotion;
use App\Models\TitleReign;
use App\Models\Wrestler;
use App\Models\WrestlerName;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ResourceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function championship_resource_uses_dry_helpers_correctly(): void
    {
        // Arrange
        $promotion = Promotion::factory()->create([
            'name' => 'WWE',
            'slug' => 'wwe',
        ]);

        $wrestler = Wrestler::factory()->create([
            'slug' => 'john-cena',
        ]);

        WrestlerName::factory()->create([
            'wrestler_id' => $wrestler->id,
            'name' => 'John Cena',
            'is_primary' => true,
        ]);

        $championship = Championship::factory()->create([
            'name' => 'WWE Championship',
            'slug' => 'wwe-championship',
            'weight_class' => 'Heavyweight',
            'promotion_id' => $promotion->id,
        ]);

        $titleReign = TitleReign::factory()->create([
            'championship_id' => $championship->id,
            'wrestler_id' => $wrestler->id,
            'won_on' => Carbon::parse('2023-04-02'),
            'won_at' => 'WrestleMania 39',
            'lost_on' => null, // Current champion
            'lost_at' => null,
            'reign_number' => 16,
        ]);

        // Load the primary name relationship
        $wrestler->load('primaryName');

        $championship->load(['promotion', 'titleReigns.wrestler.primaryName']);

        // Act
        $resource = new ChampionshipResource($championship);
        $result   = $resource->toArray(new Request);

        // Assert - Check that DRY helpers are working
        $this->assertEquals($championship->id, $result['id']);
        $this->assertEquals($championship->name, $result['name']);
        $this->assertEquals($championship->slug, $result['slug']);
        $this->assertEquals('active', $result['status']);

        // Check promotion reference (should use formatPromotionReference helper)
        $this->assertArrayHasKey('promotion', $result);
        $this->assertEquals($promotion->id, $result['promotion']['id']);
        $this->assertEquals($promotion->name, $result['promotion']['name']);
        $this->assertEquals($promotion->slug, $result['promotion']['slug']);
        $this->assertStringContainsString('promotions', $result['promotion']['detail_url']);

        // Check current champion (should use CurrentChampionResource)
        $this->assertArrayHasKey('current_champion', $result);
        $this->assertNotNull($result['current_champion'], 'Current champion should not be null');
        $this->assertEquals($wrestler->id, $result['current_champion']['id']);
        $this->assertEquals($wrestler->name, $result['current_champion']['name']); // Uses name attribute

        // Check title reigns (should use formatTitleReignsForChampionship helper)
        $this->assertArrayHasKey('title_reigns', $result);
        $this->assertCount(1, $result['title_reigns']);

        $reignData = $result['title_reigns'][0];
        $this->assertEquals($titleReign->id, $reignData['id']);
        $this->assertArrayHasKey('wrestler', $reignData);
        $this->assertEquals($wrestler->id, $reignData['wrestler']['id']);
        $this->assertEquals($wrestler->name, $reignData['wrestler']['name']); // Uses name attribute
        $this->assertStringContainsString('wrestlers', $reignData['wrestler']['detail_url']);
    }

    #[Test]
    public function promotion_resource_uses_filter_inactive_items_helper(): void
    {
        // Arrange
        $promotion = Promotion::factory()->create([
            'name' => 'AEW',
            'slug' => 'aew',
        ]);

        // Create wrestlers - some active, some inactive
        $activeWrestler1     = Wrestler::factory()->create();
        $activeWrestler1Name = WrestlerName::factory()->create([
            'wrestler_id' => $activeWrestler1->id,
            'name' => 'Jon Moxley',
            'is_primary' => true,
        ]);

        $activeWrestler2     = Wrestler::factory()->create();
        $activeWrestler2Name = WrestlerName::factory()->create([
            'wrestler_id' => $activeWrestler2->id,
            'name' => 'CM Punk',
            'is_primary' => true,
        ]);

        $inactiveWrestler     = Wrestler::factory()->create();
        $inactiveWrestlerName = WrestlerName::factory()->create([
            'wrestler_id' => $inactiveWrestler->id,
            'name' => 'Former Wrestler',
            'is_primary' => true,
        ]);

        // Create championships - some active, some inactive
        $activeChampionship = Championship::factory()->create([
            'name' => 'AEW World Championship',
            'promotion_id' => $promotion->id,
            'active' => true,
        ]);
        $inactiveChampionship = Championship::factory()->create([
            'name' => 'Retired Championship',
            'promotion_id' => $promotion->id,
            'active' => false,
        ]);

        // Load the primary names for the wrestlers so the name attribute works
        $activeWrestler1->load('primaryName');
        $activeWrestler2->load('primaryName');
        $inactiveWrestler->load('primaryName');

        // Attach wrestlers using the BelongsToMany relationships
        $promotion->wrestlers()->attach([$activeWrestler1->id, $activeWrestler2->id, $inactiveWrestler->id]);
        $promotion->activeWrestlers()->attach([$activeWrestler1->id, $activeWrestler2->id]);

        // Load relationships
        $promotion->load([
            'wrestlers.primaryName',
            'activeWrestlers.primaryName',
            'championships',
            'activeChampionships',
        ]);

        // Act
        $resource = new PromotionResource($promotion);
        $result   = $resource->toArray(new Request);

        // Assert - Check that filterInactiveItems helper worked correctly
        $this->assertCount(2, $result['active_wrestlers']);
        $this->assertCount(1, $result['inactive_wrestlers']);
        $this->assertCount(1, $result['active_championships']);
        $this->assertCount(1, $result['inactive_championships']);

        // DEBUG: Let's see what the actual JSON output looks like
        $activeWrestlersArray   = $result['active_wrestlers']->toArray(new Request);
        $inactiveWrestlersArray = $result['inactive_wrestlers']->toArray(new Request);

        // Verify the correct wrestlers are in each list
        $activeWrestlerNames = collect($activeWrestlersArray)->pluck('ring_name')->toArray();
        $this->assertContains('Jon Moxley', $activeWrestlerNames);
        $this->assertContains('CM Punk', $activeWrestlerNames);
        $this->assertNotContains('Former Wrestler', $activeWrestlerNames);

        $inactiveWrestlerNames = collect($inactiveWrestlersArray)->pluck('primary_name')->toArray();
        $this->assertContains('Former Wrestler', $inactiveWrestlerNames);
    }

    #[Test]
    public function wrestler_resource_uses_format_timestamps_helper(): void
    {
        // Arrange
        $wrestler = Wrestler::factory()->create([
            'ring_name' => 'Daniel Bryan',
            'slug' => 'daniel-bryan',
            'real_name' => 'Bryan Danielson',
            'debut_date' => Carbon::parse('2000-02-04'),
            'country' => 'United States',
            'created_at' => Carbon::parse('2023-01-01 10:00:00'),
            'updated_at' => Carbon::parse('2023-06-15 14:30:00'),
        ]);

        // Act
        $resource = new WrestlerResource($wrestler);
        $result   = $resource->toArray(new Request);

        // Assert - Check that formatTimestamps helper worked
        $this->assertArrayHasKey('created_at', $result);
        $this->assertArrayHasKey('updated_at', $result);

        // Should be ISO8601 format (from formatTimestamp helper)
        $this->assertStringContainsString('2023-01-01T10:00:00', $result['created_at']);
        $this->assertStringContainsString('2023-06-15T14:30:00', $result['updated_at']);

        // Check other DRY helper usage
        $this->assertEquals('2000-02-04', $result['debut_date']); // formatDate helper
        //        $this->assertStringContainsString('wrestlers', $result['detail_url'] ?? ''); // detailUrl helper
    }

    #[Test]
    public function resources_handle_edge_cases_consistently(): void
    {
        // Test null handling across different resources
        $wrestler = Wrestler::factory()->create([
            'debut_date' => null, // Test null date
            'real_name' => null,
        ]);

        $wrestlerResource = new WrestlerResource($wrestler);
        $wrestlerResult   = $wrestlerResource->toArray(new Request);

        // Assert consistent null handling
        $this->assertNull($wrestlerResult['debut_date']); // formatDate should handle null
        $this->assertNull($wrestlerResult['real_name']);

    }

    #[Test]
    public function nested_wrestler_resources_include_detail_url(): void
    {
        $championship = Championship::factory()->create();
        $wrestler     = Wrestler::factory()->create(['slug' => 'edge']);

        WrestlerName::factory()->create([
            'wrestler_id' => $wrestler->id,
            'name' => 'Edge',
            'is_primary' => true,
        ]);

        TitleReign::factory()->create([
            'championship_id' => $championship->id,
            'wrestler_id' => $wrestler->id,
            'won_on' => now(),
            'reign_number' => 1,
        ]);

        $championship->load(['titleReigns.wrestler.primaryName']);
        $resource = new ChampionshipResource($championship);
        $data     = $resource->toArray(new Request);

        $nestedWrestler = $data['title_reigns'][0]['wrestler'];
        $this->assertArrayHasKey('detail_url', $nestedWrestler);
        $this->assertStringContainsString('wrestlers', $nestedWrestler['detail_url']);
    }
}
