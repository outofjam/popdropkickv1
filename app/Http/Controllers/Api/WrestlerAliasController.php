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
     */
    public function store(WrestlerAliasRequest $request, Wrestler $wrestler): JsonResponse
    {
        $aliases = $this->service->addAliases($wrestler, $request->validated()['aliases']);

        return $this->ok($aliases, 'Aliases added');
    }

    /**
     * Delete a wrestler alias
     *
     * Deletes a specific alias by name or ID. Returns a 404 if the alias is not found or cannot be removed.
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
