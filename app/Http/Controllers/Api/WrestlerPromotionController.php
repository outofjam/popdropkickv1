<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateWrestlerPromotionsRequest;
use App\Models\Wrestler;
use App\Services\WrestlerService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;

class WrestlerPromotionController extends Controller
{
    use ApiResponses;

    public function __construct(protected WrestlerService $service) {}

    /**
     * Update a wrestler’s promotion associations
     *
     * Modifies the wrestler’s promotion history and active affiliations using flexible identifiers (ID, slug, name, or abbreviation).
     */
    public function update(UpdateWrestlerPromotionsRequest $request, Wrestler $wrestler): JsonResponse
    {
        $data = $request->validated();

        $updatedWrestler = $this->service->updatePromotions($wrestler, $data);

        return $this->success($updatedWrestler, 'Wrestler promotions updated successfully.');
    }
}
