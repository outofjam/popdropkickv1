<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePromotionRequest;
use App\Http\Resources\PromotionListResource;
use App\Http\Resources\PromotionResource;
use App\Services\PromotionService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;

class PromotionController extends Controller
{
    use ApiResponses;

    protected PromotionService $service;

    public function __construct(PromotionService $service)
    {
        $this->service = $service;
    }

    /**
     * List all promotions
     *
     * Returns a paginated list of promotions with associated counts.
     *
     * @group Promotions
     *
     * @authenticated
     *
     * @queryParam per_page int Number of results per page. Defaults to 15. Example: 20
     *
     * @response PromotionListResource // 1 - PHPDoc
     */
    public function index(): JsonResponse
    {
        // You can get perPage from request query, fallback to 15
        $perPage = request()->query('per_page', 5);

        $promotions = $this->service->getPaginatedWithCounts((int)$perPage);

        return $this->ok(PromotionListResource::collection($promotions));
    }

    /**
     * Get a single promotion by ID or slug
     *
     * Returns detailed info about a promotion including counts of active and inactive wrestlers.
     *
     * @group Promotions
     *
     * @authenticated
     *
     * @urlParam identifier string required The promotion ID or slug. Example: world-wrestling-alliance
     *
     * @queryParam include_inactive boolean Whether to include inactive wrestlers in the count. Example: true
     *
     * @response PromotionResource // 1 - PHPDoc
     * @response 404 {
     *   "success": false,
     *   "message": "Promotion not found"
     * }
     */
    public function show(string $identifier): JsonResponse
    {
        $includeInactive = request()->boolean('include_inactive');

        // Fetch promotion with eager loading depending on includeInactive
        $promotion = $this->service->findByIdOrSlug($identifier, $includeInactive);

        if (!$promotion) {
            return $this->error('Promotion not found', 404);
        }

        // Get active wrestlers collection (should be eager loaded)
        $active = $promotion->relationLoaded('activeWrestlers') ? $promotion->activeWrestlers : collect();

        // Get all wrestlers collection if loaded
        $all = $promotion->relationLoaded('wrestlers') ? $promotion->wrestlers : null;


        // Get active count consistently
        $activeCount = $promotion->relationLoaded('activeWrestlers')
            ? $promotion->activeWrestlers->count()
            : $promotion->activeWrestlers()->count();

        // Detect if inactive wrestlers exist using counts
        if ($all) {
            $inactiveCount = $all->count() - $activeCount;
        } else {
            // Use relationship count methods to avoid loading full collection
            $inactiveCount = $promotion->wrestlers()->count() - $promotion->activeWrestlers()->count();
        }

        $inactiveExist = $inactiveCount > 0;

        $meta = [
            'counts' => [
                'active_wrestlers' => $active->count(),
                'inactive_wrestlers' => $inactiveCount,
            ],
            'inactive_wrestlers_included' => $includeInactive,
            'inactive_wrestlers_exist' => $inactiveExist,
        ];

        // Add hint only if inactive wrestlers exist but are not included
        if ($inactiveExist && !$includeInactive) {
            $meta['inactive_wrestlers_hint'] = 'Add ?include_inactive=true to see inactive wrestlers';
        }

        return $this->success(
            new PromotionResource($promotion),
            null,
            $meta
        );
    }


    /**
     * Create a new promotion
     *
     * Stores a new wrestling promotion.
     *
     * @group Promotions
     *
     * @authenticated
     *
     * @bodyParam name string required The name of the promotion. Example: World Wrestling Alliance
     * @bodyParam founded date required The date the promotion was founded. Example: 1999-04-01
     * @bodyParam retired date The date the promotion was retired (if applicable). Example: 2009-06-30
     *
     * @response 201 {
     *   "success": true,
     *   "message": null,
     *   "data": {
     *     "id": 1,
     *     "name": "World Wrestling Alliance",
     *     "slug": "world-wrestling-alliance",
     *     "founded": "1999-04-01",
     *     "retired": null
     *   }
     * }
     */
    public function store(StorePromotionRequest $request): JsonResponse
    {
        $promotion = $this->service->create($request->validated());

        return $this->success(new PromotionResource($promotion), null, null, 201);
    }
}
