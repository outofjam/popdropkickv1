<?php

namespace App\Http\Resources;

class WrestlerNestedResource extends BaseWrestlerResource
{
    public function toArray($request): array
    {
        return array_merge(
            parent::toArray($request),
            [
                'active_title_reigns' => $this->whenLoaded('activeTitleReigns', fn () => $this->formatTitleReigns($this->resource->activeTitleReigns)),
            ],
            $this->promotionsData(),
            [
                'detail_url' => $this->detailUrl('wrestlers.show', $this->resource->slug),
            ]
        );
    }
}
