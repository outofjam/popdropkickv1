<?php

// tests/Feature/WrestlerApiTest.php

namespace Tests\Feature;

use App\Models\Promotion;
use App\Models\Wrestler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WrestlerApiTest extends TestCase
{
    use RefreshDatabase;

    // deprecated
//    public function test_get_all_wrestlers_returns_expected_structure(): void
//    {
//        // Arrange: create promotion and wrestler with relationship
//        $promotion = Promotion::factory()->create();
//        $wrestler = Wrestler::factory()->create();
//        $wrestler->promotions()->attach($promotion);
//        $wrestler->activePromotions()->attach($promotion);
//
//        // Act
//        $response = $this->getJson('/api/wrestlers');
//
//        // Assert
//        $response->assertStatus(200)
//            ->assertJsonStructure([
//                'data' => [
//                    '*' => [ // array of wrestlers
//                        'id',
//                        'ring_name',
//                        'real_name',
//                        'debut_date',
//
//                        'active_promotions',
//                        'created_at',
//                        'updated_at',
//                    ],
//                ],
//                'meta' => ['status', 'timestamps'],
//            ]);
//    }

    // deprecated
//    public function test_pagination_meta_is_present_and_correct(): void
//    {
//        // Create enough wrestlers to trigger pagination (e.g., 30 with 15 per page)
//        Wrestler::factory()->count(30)->create();
//
//        $response = $this->getJson('/api/wrestlers');
//
//        $response->assertStatus(200);
//        $response->assertJsonStructure([
//            'meta' => [
//                'status',
//                'timestamps',
//                'page' => [
//                    'current_page',
//                    'last_page',
//                    'per_page',
//                    'total',
//                    'from',   // add these for completeness
//                    'to',
//                    'path',
//                ],
//                'links' => [
//                    'first',
//                    'last',
//                    'prev',
//                    'next',
//                ],
//            ],
//        ]);
//
//        $json = $response->json();
//        $this->assertEquals(1, $json['meta']['page']['current_page']);
//        $this->assertEquals(15, $json['meta']['page']['per_page']);
//        $this->assertTrue($json['meta']['page']['total'] >= 30);
//    }

    // deprecated
//    public function test_pagination_meta_is_present_and_correct_with_custom_per_page(): void
//    {
//        // Create enough wrestlers to trigger pagination (e.g., 30 with 15 per page)
//        Wrestler::factory()->count(30)->create();
//    }
    // deprecated
    public function test_empty_wrestlers_returns_empty_data_and_meta(): void
    {
        $response = $this->getJson('/api/wrestlers');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [],
                'meta' => [
                    'status' => 200,
                ],
            ]);
    }

    // deprecated
//    public function test_get_wrestler_detail_by_id_returns_expected_structure(): void
//    {
//        $promotion = Promotion::factory()->create();
//        $wrestler = Wrestler::factory()->create();
//        $wrestler->promotions()->attach($promotion);
//        $wrestler->activePromotions()->attach($promotion);
//    }

    public function test_get_wrestler_detail_by_slug_returns_expected_structure(): void
    {
        $promotion = Promotion::factory()->create();
        $wrestler = Wrestler::factory()->create([
            'slug' => 'kenny-omega',
        ]);
        $wrestler->promotions()->attach($promotion);
        $wrestler->activePromotions()->attach($promotion);

        $response = $this->getJson('/api/wrestlers/kenny-omega');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'ring_name',
                    'real_name',
                    'debut_date',

                    'promotions',
                    'active_promotions',

                ],
                'meta' => ['status', 'timestamps'],
            ]);
    }
}
