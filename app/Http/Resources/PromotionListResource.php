<?php

namespace App\Http\Resources;

// BEFORE: Manual timestamps
// AFTER: Uses formatTimestamps helper
class PromotionListResource extends BaseResource
{
    public function toArray($request): array
    {
        return array_merge(
            $this->formatBasicEntityData(),
            [
                'abbreviation' => $this->resource->abbreviation,
                'country' => $this->resource->country,
                'active_wrestlers_count' => $this->resource->active_wrestlers_count ?? 0,
                'active_championships' => ChampionshipNestedResource::collection($this->whenLoaded('activeChampionships')),
                'detail_url' => $this->detailUrl('promotions.show', $this->resource->slug ?? $this->resource->id),
            ],
            $this->formatTimestamps()
        );
    }
}
