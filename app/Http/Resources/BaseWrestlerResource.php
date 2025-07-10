<?php

namespace App\Http\Resources;

abstract class BaseWrestlerResource extends BaseResource
{
    public function toArray($request): array
    {
        $primaryName = $this->resource->names->firstWhere('is_primary', true);
        $aliases = $this->resource->names->where('is_primary', false);

        return [
            'id' => $this->resource->id,
            'slug' => $this->resource->slug,
            'ring_name' => $primaryName?->name,
            'real_name' => $this->resource->real_name,
            'also_known_as' => WrestlerNameResource::collection(
                $this->whenLoaded('names', static fn () => $aliases)
            ),
            'active_title_reigns' => $this->whenLoaded(
                'activeTitleReigns',
                fn () => $this->formatTitleReigns($this->resource->activeTitleReigns)
            ),
        ];
    }

    protected function promotionsData(): array
    {
        return [
            'promotions' => PromotionNestedResource::collection($this->whenLoaded('promotions')),
            'active_promotions' => PromotionNestedResource::collection($this->whenLoaded('activePromotions')),
        ];
    }
}
