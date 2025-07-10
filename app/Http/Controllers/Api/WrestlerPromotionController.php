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
     *
     * @group Wrestlers
     *
     * @authenticated
     *
     * @urlParam wrestler int required The ID of the wrestler. Example: 42
     *
     * @bodyParam add_promotions object Add promotions to the wrestler. Use `active` or `inactive` sub-arrays. Example: {"active": ["wwe", "njpw"], "inactive": ["roh"]}
     * @bodyParam add_promotions.active array List of promotion identifiers the wrestler is currently active in. Example: ["wwe", "aew"]
     * @bodyParam add_promotions.inactive array List of promotion identifiers to associate historically (not active). Example: ["ecw"]
     * @bodyParam remove_promotions array List of promotion identifiers to remove completely. Example: ["wcw", "tna"]
     * @bodyParam deactivate_promotions array List of currently active promotions to deactivate. Example: ["aew"]
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Wrestler promotions updated successfully.",
     *   "data": {
     *     "id": 42,
     *     "name": "AJ Styles",
     *     "slug": "aj-styles",
     *     "promotions": [
     *       {
     *         "id": 1,
     *         "name": "WWE",
     *         "active": true
     *       },
     *       {
     *         "id": 3,
     *         "name": "NJPW",
     *         "active": false
     *       }
     *     ]
     *   }
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "promotions": [
     *       "One or more promotion identifiers are invalid."
     *     ]
     *   }
     * }
     */
    public function update(UpdateWrestlerPromotionsRequest $request, Wrestler $wrestler): JsonResponse
    {
        $data = $request->validated();

        $updatedWrestler = $this->service->updatePromotions($wrestler, $data);

        return $this->success($updatedWrestler, 'Wrestler promotions updated successfully.');
    }
}
