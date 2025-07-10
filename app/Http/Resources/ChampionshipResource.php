<?php

namespace App\Http\Resources;

// app/Http/Resources/ChampionshipResource.php

class ChampionshipResource extends BaseResource
{
    public function toArray($request): array
    {
        $latestReign = $this->resource->titleReigns->sortByDesc('won_on')->first();

        $currentChampion = $latestReign && $latestReign->lost_on === null
            ? new CurrentChampionResource($latestReign)
            : null;

        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'slug' => $this->resource->slug,
            'status' => $currentChampion ? 'active' : 'vacant',
            'current_champion' => $currentChampion?->toArray($request),
            'promotion' => [
                'id' => $this->resource->promotion->id,
                'name' => $this->resource->promotion->name,
                'slug' => $this->resource->promotion->slug,
                'detail_url' => $this->detailUrl('promotions.show', $this->resource->promotion->slug),
            ],
            'title_reigns' => $this->resource->titleReigns->map(function ($reign) {
                return [
                    'id' => $reign->id,
                    'wrestler' => [
                        'id' => $reign->wrestler->id,
                        'name' => $reign->wrestler->name,
                        'slug' => $reign->wrestler->slug,
                        'detail_url' => $this->detailUrl('wrestlers.show', $reign->wrestler->slug),
                    ],
                    'reign_length' => $reign->reign_length_in_days,
                    'reign_length_human' => $reign->reign_length_human,
                    'won_on' => $this->formatDate($reign->won_on),
                    'won_at' => $reign->won_at,
                    'lost_on' => $this->formatDate($reign->lost_on),
                    'lost_at' => $reign->lost_on !== null && $reign->lost_at === null ? 'vacated' : $reign->lost_at,
                ];
            }),
        ];
    }
}
