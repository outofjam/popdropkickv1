<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\WrestlerAliasRequest;
use App\Models\Wrestler;
use App\Services\WrestlerService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;

class WrestlerAliasController extends Controller
{
    use ApiResponses;

    public function __construct(protected WrestlerService $service) {}

    /**
     * Add an alias to a wrestler
     *
     * Creates a new ring name or alias for the specified wrestler. Optionally marks it as their primary identity.
     *
     * @group Wrestlers
     *
     * @authenticated
     *
     * @urlParam wrestler int required The ID of the wrestler. Example: 42
     *
     * @bodyParam name string required The alias or ring name. Example: "The Phenomenal One"
     * @bodyParam is_primary boolean Whether this alias should become the wrestler’s primary name. Example: true
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Alias added",
     *   "data": {
     *     "id": 12,
     *     "wrestler_id": 42,
     *     "name": "The Phenomenal One",
     *     "is_primary": true
     *   }
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "name": [
     *       "The name field is required."
     *     ]
     *   }
     * }
     */
    public function store(WrestlerAliasRequest $request, Wrestler $wrestler): JsonResponse
    {
        $aliases = $this->service->addAliases($wrestler, $request->validated()['aliases']);
        
        return $this->ok($alias->toArray(), 'Alias added');
    }

    /**
     * Delete a wrestler alias
     *
     * Deletes a specific alias by name or ID. Returns a 404 if the alias is not found or cannot be removed.
     *
     * @group Wrestlers
     *
     * @authenticated
     *
     * @urlParam wrestler int required The ID of the wrestler. Example: 42
     * @urlParam alias string required The ID or name of the alias to delete. Example: "the-phenomenal-one"
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Alias deleted",
     *   "data": null
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Alias not found or could not be deleted"
     * }
     */
    public function destroy(Wrestler $wrestler, string $alias): JsonResponse
    {
        $deleted = $this->service->removeAlias($wrestler, $alias);

        if (! $deleted) {
            return $this->error('Alias not found or could not be deleted', 404);
        }

        return $this->ok(null, 'Alias deleted');
    }
}
