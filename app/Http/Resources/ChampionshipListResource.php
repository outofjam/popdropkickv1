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
                'current_champions' => $this->formatCurrentChampions($this->resource->currentTitleReign),
            ],
            $this->formatTimestamps()
        );
    }
}
