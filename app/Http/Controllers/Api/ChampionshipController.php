<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChampionshipRequest;
use App\Http\Requests\UpdateChampionshipRequest;
use App\Http\Resources\ChampionshipListResource;
use App\Http\Resources\ChampionshipResource;
use App\Models\Championship;
use App\Models\Promotion;
use App\Services\ChampionshipService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ChampionshipController extends Controller
{
    use ApiResponses;

    protected ChampionshipService $service;

    public function __construct(ChampionshipService $service)
    {
        $this->service = $service;
    }

    /**
     * List all championships
     *
     * Returns a paginated list of championships across all promotions.
     */
    public function index(): JsonResponse
    {
        $perPage = request()->query('per_page', 15);

        $championships = $this->service->getPaginated((int) $perPage);

        return $this->ok(ChampionshipListResource::collection($championships));
    }

    /**
     * Create a new championship for a promotion
     *
     * Creates a new championship under the given promotion.
     */
    public function store(StoreChampionshipRequest $request, Promotion $promotion): JsonResponse
    {
        $data = $request->validated();

        $championship = $this->service->createChampionship($promotion, $data);
        Log::info('Updated championship:', $championship->toArray());

        return $this->success($championship, 'Championship Created', null, 201);
    }

    // app/Http/Controllers/Api/ChampionshipController.php

    /**
     * Get a championship by ID or slug
     *
     * Returns details about a specific championship, including its promotion and title reign history.
     */
    public function show(string $identifier): JsonResponse
    {
        $championship = $this->service->findByIdOrSlugWithDetails($identifier);

        if (! $championship) {
            return $this->error('Championship not found', 404);
        }

        return $this->success(
            new ChampionshipResource($championship),
            null,
            [
                'counts' => $this->service->getCounts($championship),
                'reign_stats' => $this->service->getReignStatistics($championship),
            ]
        );
    }

    /**
     * Update a championship by ID or slug.
     */
    public function update(UpdateChampionshipRequest $request, string $identifier): JsonResponse
    {
        $championship = $this->service->findByIdOrSlug($identifier);

        if (! $championship) {
            return $this->error('Championship not found', 404);
        }

        $updated = $this->service->loadDetail(
            $this->service->updateChampionship($championship, $request->validated())
        );

        return $this->success(new ChampionshipResource($updated), 'Championship updated successfully');
    }

    /**
     * Toggle Championship  Active
     *
     * Toggles a championship between active and inactive
     */
    public function toggleActive(Championship $championship): JsonResponse
    {
        $updatedChampionship = $this->service->toggleActiveStatus($championship);

        return $this->success($updatedChampionship, 'Championship active status toggled');
    }
}
