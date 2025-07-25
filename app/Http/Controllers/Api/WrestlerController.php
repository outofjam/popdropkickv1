<?php

// app/Http/Controllers/Api/WrestlerController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWrestlerRequest;
use App\Http\Requests\UpdateWrestlerRequest;
use App\Http\Resources\WrestlerListResource;
use App\Http\Resources\WrestlerResource;
use App\Models\Wrestler;
use App\Services\ChangeRequestService;
use App\Services\WrestlerService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;

class WrestlerController extends Controller
{
    use ApiResponses;

    protected WrestlerService $service;
    protected ChangeRequestService $changeRequestService;

    public function __construct(WrestlerService $service, ChangeRequestService $changeRequestService)
    {
        $this->service = $service;
        $this->changeRequestService = $changeRequestService;
    }

    /**
     * Get a list of all wrestlers in the database
     *
     * Returns a paginated list of wrestlers.
     *
     * @authenticated
     *
     * @queryParam page int The page number. Example: 1
     * @queryParam per_page int Number of results per page. Defaults to 15. Example: 20
     * @queryParam sort string Sort by field. Example: name
     * @queryParam order string Sort order. Example: asc
     *
     * @scramblephp
     *
     * @group Wrestlers
     *
     * @responseField id string The UUID of the wrestler.
     * @responseField slug string The URL-friendly identifier.
     * @responseField ring_name string|null The primary ring name of the wrestler.
     * @responseField real_name string|null The wrestler’s real/legal name.
     * @responseField also_known_as WrestlerNameResource[] List of alias ring names (non-primary).
     * @responseField also_known_as[].id string The ID of the alias.
     * @responseField also_known_as[].name string The alternate ring name.
     * @responseField active_title_reigns array[] List of currently held titles.
     * @responseField active_title_reigns[].championship_id string The ID of the championship.
     * @responseField active_title_reigns[].championship_name string The name of the championship.
     * @responseField active_title_reigns[].won_on string Date the title was won (YYYY-MM-DD).
     * @responseField active_title_reigns[].won_at string Event or context of title win.
     * @responseField active_title_reigns[].lost_on string|null Date lost (or null if active).
     * @responseField active_title_reigns[].lost_at string|null|"vacated" Loss method or "vacated".
     * @responseField active_title_reigns[].reign_number int Reign number for this wrestler.
     * @responseField active_title_reigns[].win_type string|null Method of victory (e.g., pinfall).
     * @responseField active_title_reigns[].reign_length int Duration of reign in days.
     * @responseField active_title_reigns[].reign_length_human string Human-readable duration.
     * @responseField promotions PromotionNestedResource[] All promotions the wrestler is/was in.
     * @responseField promotions[].id string The ID of the promotion.
     * @responseField promotions[].name string The name of the promotion.
     * @responseField promotions[].slug string The slug of the promotion.
     * @responseField promotions[].detail_url string API URL for promotion details.
     * @responseField promotions[].abbreviation string|null Shortform name.
     * @responseField active_promotions PromotionNestedResource[] Currently active promotions.
     * @responseField debut_date string|null Wrestler's debut date (YYYY-MM-DD).
     * @responseField detail_url string Route to detailed wrestler view.
     * @responseField created_at string ISO 8601 timestamp when created.
     * @responseField updated_at string ISO 8601 timestamp when last updated.
     *
     * @responseType \Illuminate\Http\Resources\Json\AnonymousResourceCollection<WrestlerListResource>
     */
    // removed from endpoints. this one doesn't make sense
    public function index(): JsonResponse
    {
        $wrestlers = $this->service->getPaginated();

        return $this->ok(WrestlerListResource::collection($wrestlers));
    }

    /**
     * Get details for a single wrestler
     *
     *
     * @group Wrestlers
     *
     * @authenticated
     *
     * @queryParam page int The page number. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": null,
     *   "data": {
     *     "current_page": 1,
     *     "data": [
     *       {
     *         "id": "1d9e7f96-9876-44b9-9932-a394e4e0d5aa",
     *         "slug": "john-slammer",
     *         "ring_name": "John Slammer",
     *         "real_name": "Johnny Kowalski",
     *         "also_known_as": [
     *           { "id": "n1", "name": "The Crusher" }
     *         ],
     *         "active_title_reigns": [
     *           {
     *             "championship_id": "abc123",
     *             "championship_name": "World Heavyweight Championship",
     *             "won_on": "2024-06-15",
     *             "won_at": "WrestleFest XII",
     *             "lost_on": null,
     *             "lost_at": null,
     *             "reign_number": 2,
     *             "win_type": "pinfall",
     *             "reign_length": 23,
     *             "reign_length_human": "23 days"
     *           }
     *         ],
     *         "promotions": [
     *           {
     *             "id": "p1",
     *             "name": "NXT",
     *             "slug": "nxt",
     *             "abbreviation": "NXT",
     *             "detail_url": "https://api.popdropkick.test/promotions/nxt"
     *           }
     *         ],
     *         "active_promotions": [
     *           {
     *             "id": "p1",
     *             "name": "NXT",
     *             "slug": "nxt",
     *             "abbreviation": "NXT",
     *             "detail_url": "https://api.popdropkick.test/promotions/nxt"
     *           }
     *         ],
     *         "debut_date": "2010-04-22",
     *         "detail_url": "https://api.popdropkick.test/wrestlers/john-slammer",
     *         "created_at": "2024-05-01T13:45:00Z",
     *         "updated_at": "2024-07-01T10:12:00Z"
     *       }
     *     ],
     *     "first_page_url": "...",
     *     "last_page": 10,
     *     ...
     *   }
     * }
     */
    public function show(Wrestler $wrestler): JsonResponse
    {
        return $this->success(
            new WrestlerResource($wrestler),
            null,
            [
                'counts' => [
                    'title_reigns' => $wrestler->titleReigns->count(),
                    'active_title_reigns' => $wrestler->activeTitleReigns->count(),
                    'promotions' => $wrestler->promotions->count(),
                    'active_promotions' => $wrestler->activePromotions->count(),
                ],
            ]
        );
    }

    /**
     * Create a new wrestler
     *
     * Stores a new wrestler record.
     *
     * @group Wrestlers
     *
     * @authenticated
     *
     * @bodyParam name string required The name of the wrestler. Example: John Slammer
     * @bodyParam birthdate date required The wrestler's birthdate. Example: 1985-02-10
     * @bodyParam debuted_on date required Date of professional debut. Example: 2010-06-01
     * @bodyParam retired_on date The date the wrestler retired, if applicable. Example: 2023-01-01
     * @bodyParam promotion_id int The ID of the current promotion. Example: 3
     *
     * @response 201 {
     *   "success": true,
     *   "message": null,
     *   "data": {
     *     "id": 1,
     *     "name": "John Slammer",
     *     "slug": "john-slammer",
     *     "birthdate": "1985-02-10",
     *     "debuted_on": "2010-06-01",
     *     "retired_on": null,
     *     ...
     *   }
     * }
     */
    public function store(StoreWrestlerRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Check if user has auto-approval privileges
        if (auth()->user()->canAutoApprove('wrestler_create')) {
            $wrestler = $this->service->create($data);
            return $this->success(
                new WrestlerResource($wrestler),
                'Wrestler created successfully',
                null,
                201
            );
        }

        // Create change request for approval
        $changeRequest = $this->changeRequestService->create([
            'user_id' => auth()->id(),
            'action' => 'create',
            'model_type' => 'wrestler',
            'data' => $data,
            'status' => 'pending'
        ]);

        return $this->success(
            ['change_request_id' => $changeRequest->id],
            'Wrestler creation request submitted for review',
            null,
            202 // Accepted but not processed
        );
    }

    /**
     * Update a wrestler by ID or slug
     *
     * Updates an existing wrestler's information.
     *
     * @group Wrestlers
     *
     * @authenticated
     *
     * @urlParam identifier string required The ID or slug of the wrestler. Example: john-slammer
     *
     * @bodyParam name string The name of the wrestler. Example: John Slammer
     * @bodyParam birthdate date The wrestler's birthdate. Example: 1985-02-10
     * @bodyParam debuted_on date Date of professional debut. Example: 2010-06-01
     * @bodyParam retired_on date The date the wrestler retired, if applicable. Example: 2023-01-01
     * @bodyParam promotion_id int The ID of the current promotion. Example: 3
     *
     * @response 200 {
     *   "success": true,
     *   "message": null,
     *   "data": {
     *     "id": 1,
     *     "name": "John Slammer",
     *     "slug": "john-slammer",
     *     ...
     *   }
     * }
     * @response 404 {
     *   "message": "Wrestler not found"
     * }
     */
    public function update(UpdateWrestlerRequest $request, Wrestler $wrestler): JsonResponse
    {
        $data = $request->validated();

        // Check for auto-approval
        if (auth()->user()->canAutoApprove('wrestler_update')) {
            $updatedWrestler = $this->service->update($wrestler, $data);
            return $this->success(
                new WrestlerResource($updatedWrestler),
                'Wrestler updated successfully'
            );
        }

        // Create change request
        $changeRequest = $this->changeRequestService->create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model_type' => 'wrestler',
            'model_id' => $wrestler->id,
            'data' => $data,
            'original_data' => $wrestler->toArray(),
            'status' => 'pending'
        ]);

        return $this->success(
            ['change_request_id' => $changeRequest->id],
            'Wrestler update request submitted for review',
            null,
            202
        );
    }
}
