<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePromotionRequest;
use App\Http\Resources\ChampionshipListResource;
use App\Http\Resources\PromotionListResource;
use App\Http\Resources\PromotionResource;
use App\Http\Resources\WrestlerListResource;
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
     */
    public function show(string $identifier): JsonResponse
    {
        $includeInactive = request()->boolean('include_inactive');

        $promotion = $this->service->findByIdOrSlugCached($identifier, $includeInactive);

        if (! $promotion) {
            return $this->error('Promotion not found', 404);
        }

        $counts = $this->service->getWrestlerCounts($promotion);

        $meta = [
            'counts' => [
                'active_wrestlers' => $counts['active'],
                'inactive_wrestlers' => $counts['inactive'],
            ],
            'inactive_wrestlers_included' => $includeInactive,
        ];

        if ($counts['inactive'] > 0 && ! $includeInactive) {
            $meta['inactive_wrestlers_exist'] = true;
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
     */
    public function store(StorePromotionRequest $request): JsonResponse
    {
        $promotion = $this->service->create($request->validated());

        return $this->success(new PromotionResource($promotion), null, null, 201);
    }

    /**
     * List a promotion's championships (active and inactive).
     */
    public function championships(string $identifier): JsonResponse
    {
        $promotion = $this->service->resolvePromotion($identifier);

        if (! $promotion) {
            return $this->error('Promotion not found', 404);
        }

        $perPage = request()->query('per_page', 15);

        $championships = $this->service->getPaginatedChampionships($promotion, (int) $perPage);

        return $this->ok(ChampionshipListResource::collection($championships));
    }

    /**
     * List a promotion's wrestlers.
     *
     * Returns active wrestlers only unless include_inactive=true is passed.
     */
    public function wrestlers(string $identifier): JsonResponse
    {
        $promotion = $this->service->resolvePromotion($identifier);

        if (! $promotion) {
            return $this->error('Promotion not found', 404);
        }

        $includeInactive = request()->boolean('include_inactive');
        $perPage = request()->query('per_page', 15);

        $wrestlers = $this->service->getPaginatedWrestlers($promotion, $includeInactive, (int) $perPage);

        return $this->ok(WrestlerListResource::collection($wrestlers));
    }
}
