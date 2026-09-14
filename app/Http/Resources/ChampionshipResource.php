<?php

namespace App\Http\Resources;

class ChampionshipResource extends BaseResource
{
    public function toArray($request): array
    {
        $latestReign = $this->resource->titleReigns->sortByDesc('won_on')->first();

        $currentReign = ($latestReign && $latestReign->lost_on === null) ? $latestReign : null;
        $currentChampions = $this->formatCurrentChampions($currentReign);

        return array_merge(
            [
                'id' => $this->resource->id,
                'name' => $this->resource->name,
                'slug' => $this->resource->slug,
                'active' => (bool) $this->resource->active,
                'introduced_at' => $this->formatDate($this->resource->introduced_at),
                'status' => $currentChampions !== [] ? 'active' : 'vacant',
                'current_champions' => $currentChampions,

                // DRY: use the promotion helper
                'promotion' => $this->formatPromotionReference($this->resource->promotion),

                // DRY: use the reigns-for-championship helper (includes alias_name + wrestler ref)
                'title_reigns' => $this->formatTitleReignsForChampionship($this->resource->titleReigns),
            ],
            $this->formatTimestamps()
        );
    }
}
