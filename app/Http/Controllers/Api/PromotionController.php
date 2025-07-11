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

        $promotions = $this->service->getPaginatedWithCounts((int) $perPage);

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

        $promotion = $this->service->findByIdOrSlug($identifier, $includeInactive);

        if (! $promotion) {
            return $this->error('Promotion not found', 404);
        }

        $hasActive = $promotion->relationLoaded('activeWrestlers');
        $hasAll = $promotion->relationLoaded('wrestlers');

        $active = $hasActive ? $promotion->activeWrestlers : collect();
        $inactive = ($includeInactive && $hasAll)
            ? $promotion->wrestlers->reject(static fn ($wrestler) => $active->contains('id', $wrestler->getKey()))
            : collect();

        return $this->success(
            new PromotionResource($promotion),
            null,
            [
                'counts' => [
                    'active_wrestlers' => $active->count(),
                    'inactive_wrestlers' => $inactive->count(),
                ],
            ]
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
