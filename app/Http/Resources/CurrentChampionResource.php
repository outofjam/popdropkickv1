<?php

namespace App\Http\Resources;

// BEFORE: Manual wrestler reference building
// AFTER: Uses formatWrestlerReference helper
class CurrentChampionResource extends BaseResource
{
    public function toArray($request): array
    {
        $reign = $this->resource;

        if (! $reign || ! $reign->wrestler) {
            return ['status' => 'vacant'];
        }

        return array_merge(
            $this->formatWrestlerReference($reign->wrestler),
            [
                'reign_start' => $this->formatDate($reign->won_on),
                'reign_number' => $reign->reign_number,
                'reign_length' => $reign->reign_length_in_days,
                'reign_length_human' => $reign->reign_length_human,
            ]
        );
    }
}
