<?php

namespace App\Http\Resources;

class ChampionshipListResource extends BaseResource
{
    public function toArray($request): array
    {
        return array_merge(
            $this->formatChampionshipReference($this->resource),
            [
                'active' => (bool) $this->resource->active,
                'introduced_at' => $this->formatDate($this->resource->introduced_at),
                'promotion' => $this->formatPromotionReference($this->resource->promotion),
                'current_champion' => $this->when($this->resource->currentTitleReign, function () {
                    return new CurrentChampionResource($this->resource->currentTitleReign);
                }, ['status' => 'vacant']),
            ]
        );
    }
}
