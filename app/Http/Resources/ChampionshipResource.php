<?php

namespace App\Http\Resources;

class ChampionshipResource extends BaseResource
{
    public function toArray($request): array
    {
        $latestReign = $this->resource->titleReigns->sortByDesc('won_on')->first();

        $currentChampion = ($latestReign && $latestReign->lost_on === null)
            ? new CurrentChampionResource($latestReign)
            : null;

        return [
            'id'   => $this->resource->id,
            'name' => $this->resource->name,
            'slug' => $this->resource->slug,
            'active' => (bool) $this->resource->active,
            'status'           => $currentChampion ? 'active' : 'vacant',
            'current_champion' => $currentChampion?->toArray($request),

            // DRY: use the promotion helper
            'promotion' => $this->formatPromotionReference($this->resource->promotion),

            // DRY: use the reigns-for-championship helper (includes alias_name + wrestler ref)
            'title_reigns' => $this->formatTitleReignsForChampionship($this->resource->titleReigns),
        ];
    }
}
