<?php

namespace App\Http\Resources;

// BEFORE: Complex collection filtering with duplicated logic
// AFTER: Uses filterInactiveItems helper
class PromotionResource extends BaseResource
{
    public function toArray($request): array
    {
        $allWrestlers = $this->resource->relationLoaded('wrestlers') ? collect($this->resource->wrestlers) : collect();
        $activeWrestlers = $this->resource->relationLoaded('activeWrestlers') ? collect($this->resource->activeWrestlers) : collect();
        $inactiveWrestlers = $this->filterInactiveItems($allWrestlers, $activeWrestlers); // Using helper!

        $activeChamps = $this->resource->relationLoaded('activeChampionships') ? collect($this->resource->activeChampionships) : collect();
        $allChamps = $this->resource->relationLoaded('championships') ? collect($this->resource->championships) : collect();
        $inactiveChamps = $this->filterInactiveItems($allChamps, $activeChamps); // Using helper!

        return array_merge(
            $this->formatBasicEntityData(),
            [
                'abbreviation' => $this->resource->abbreviation,
                'country' => $this->resource->country,
                'active_wrestlers' => WrestlerNestedResource::collection($activeWrestlers),
                'inactive_wrestlers' => InactiveWrestlerResource::collection($inactiveWrestlers),
                'active_championships' => ChampionshipNestedResource::collection($activeChamps),
                'inactive_championships' => ChampionshipNestedResource::collection($inactiveChamps),
                'detail_url' => $this->detailUrl('promotions.show', $this->resource->slug ?? $this->resource->id),
            ]
        );
    }
}
