<?php

// app/Http/Controllers/Api/WrestlerController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWrestlerRequest;
use App\Http\Requests\UpdateWrestlerRequest;
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
     * @scramblephp
     *
     * @responseType \Illuminate\Http\Resources\Json\AnonymousResourceCollection<WrestlerListResource>
     */
    // removed from endpoints. this one doesn't make sense
    //    public function index(): JsonResponse
    //    {
    //        $wrestlers = $this->service->getPaginated();
    //
    //        return $this->ok(WrestlerListResource::collection($wrestlers));
    //    }

    /**
     * Get details for a single wrestler
     */
    public function show(Wrestler $wrestler): JsonResponse
    {
        $wrestler = $this->service->loadDetail($wrestler);

        return $this->success(
            new WrestlerResource($wrestler),
            null,
            ['counts' => $this->service->getDetailStats($wrestler)]
        );
    }

    /**
     * Create a new wrestler
     *
     * Stores a new wrestler record.
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
            'status' => 'pending',
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
            'status' => 'pending',
        ]);

        return $this->success(
            ['change_request_id' => $changeRequest->id],
            'Wrestler update request submitted for review',
            null,
            202
        );
    }
}
