<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChampionshipRequest;
use App\Http\Requests\UpdateChampionshipRequest;
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
     * Create a new championship for a promotion
     *
     * Creates a new championship under the given promotion.
     *
     * @group Championships
     *
     * @authenticated
     *
     * @urlParam promotion int required The ID of the promotion. Example: 3
     *
     * @bodyParam name string required The name of the championship. Example: Intercontinental Championship
     * @bodyParam introduced_on date required The date the championship was introduced. Example: 1990-07-01
     * @bodyParam retired_on date The date the championship was retired (if applicable). Example: 2001-03-15
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Championship Created",
     *   "data": {
     *     "id": 1,
     *     "name": "Intercontinental Championship",
     *     "slug": "intercontinental-championship",
     *     "introduced_on": "1990-07-01",
     *     "retired_on": null,
     *     "promotion_id": 3
     *   }
     * }
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
     *
     * @group Championships
     *
     * @urlParam identifier string required The ID or slug of the championship. Example: intercontinental-championship
     *
     * @response 200 {
     *   "success": true,
     *   "message": null,
     *   "data": {
     *     "id": 1,
     *     "name": "Intercontinental Championship",
     *     "slug": "intercontinental-championship",
     *     "introduced_on": "1990-07-01",
     *     "retired_on": null,
     *     "promotion": {
     *       "id": 3,
     *       "name": "WWE",
     *       "slug": "wwe"
     *     },
     *     "title_reigns": [
     *       {
     *         "id": 1,
     *         "wrestler": {
     *           "id": 5,
     *           "slug": "bret-hart"
     *         },
     *         "won_on": "1991-08-26",
     *         "lost_on": "1992-04-05"
     *       },
     *       ...
     *     ]
     *   },
     *   "meta": {
     *     "counts": {
     *       "title_reigns": 15
     *     }
     *   }
     * }
     * @response 404 {
     *   "message": "Championship not found"
     * }
     */
    public function show(string $identifier): JsonResponse
    {
        $championship = Championship::query()
            ->where('id', $identifier)
            ->orWhere('slug', $identifier)
            ->with([
                'promotion:id,name,slug',
                'titleReigns.wrestler:id,slug',
                'titleReigns' => static function ($query) {
                    $query->orderBy('won_on');
                },
            ])
            ->first();

        if (!$championship) {
            return $this->error('Championship not found', 404);
        }

        $titleReignsCount = $championship->titleReigns->count();

        return $this->success(
            new ChampionshipResource($championship),
            null,
            ['counts' => ['title_reigns' => $titleReignsCount]]
        );
    }

    /**
     * Update a championship by ID or slug.
     *
     * @group Championships
     *
     * @urlParam identifier string required The ID or slug of the championship. Example: intercontinental-championship
     *
     * @response 200 {
     *   "success": true,
     *   "message": null,
     *   "data": {
     *     "id": 1,
     *     "name": "Intercontinental Championship",
     *     "slug": "intercontinental-championship",
     *     "introduced_on": "1990-07-01",
     *     "retired_on": null,
     *     "promotion": {
     *       "id": 3,
     *       "name": "WWE",
     *       "slug": "wwe"
     *     },
     *     "title_reigns": [
     *       {
     *         "id": 1,
     *         "wrestler": {
     *           "id": 5,
     *           "slug": "bret-hart"
     *         },
     *         "won_on": "1991-08-26",
     *         "lost_on": "1992-04-05"
     *       },
     *       ...
     *     ]
     *   },
     *   "meta": {
     *     "counts": {
     *       "title_reigns": 15
     *     }
     *   }
     * }
     * @response 404 {
     *   "message": "Championship not found"
     * }
     */
    public function update(UpdateChampionshipRequest $request, string $identifier): JsonResponse
    {
        $championship = Championship::query()
            ->where('id', $identifier)
            ->orWhere('slug', $identifier)
            ->with([
                'promotion:id,name,slug',
                'titleReigns.wrestler:id,slug',
                'titleReigns' => static function ($query) {
                    $query->orderBy('won_on');
                },
            ])
            ->first();

        if (!$championship) {
            return $this->error('Championship not found', 404);
        }

        $updatedChampionship = $this->service->updateChampionship($championship, $request->validated());

        return $this->success($updatedChampionship, 'Championship updated successfully');
    }

    /**
     * Toggle Championship  Active
     *
     * Toggles a championship between active and inactive
     *
     * @group Championships
     *
     * @urlParam identifier string required The ID or slug of the championship. Example: intercontinental-championship
     *
     * @response 404 {
     *   "message": "Championship not found"
     * }
     */
    public function toggleActive(Championship $championship): JsonResponse
    {
        $updatedChampionship = $this->service->toggleActiveStatus($championship);

        return $this->success($updatedChampionship, 'Championship active status toggled');
    }

}
