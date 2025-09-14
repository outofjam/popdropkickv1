<?php

namespace App\Http\Resources;

class CurrentChampionResource extends BaseResource
{
    public function toArray($request): array
    {
        $reign = $this->resource;

        // If there's no open reign or no wrestler can be resolved, treat as vacant
        $wrestler = $reign?->resolved_wrestler;
        if (! $reign || ! $wrestler) {
            return ['status' => 'vacant'];
        }

        return array_merge(
        // Standardized wrestler reference (id, name, slug, detail_url)
            $this->formatWrestlerReference($wrestler),
            [
                // Alias text captured (or primary if backfilling)
                'alias_name'          => $reign->resolved_display_name_at_win,

                // Reign metadata
                'reign_start'         => $this->formatDate($reign->won_on),
                'reign_number'        => $reign->reign_number,
                'reign_length'        => $reign->reign_length_in_days,
                'reign_length_human'  => $reign->reign_length_human,
            ]
        );
    }
}
