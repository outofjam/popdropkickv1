<?php

namespace App\Http\Resources;

// BEFORE: Manual array building
// AFTER: Uses formatChampionshipReference helper
class ChampionshipNestedResource extends BaseResource
{
    public function toArray($request): array
    {
        return array_merge(
            $this->formatChampionshipReference($this->resource),
            [
                'introduced_at' => $this->formatDate($this->resource->introduced_at),
                'current_champions' => $this->formatCurrentChampions($this->resource->currentTitleReign),
            ]
        );
    }
}
