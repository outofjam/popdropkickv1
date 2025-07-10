<?php

namespace App\Http\Resources;

// BEFORE: Manual timestamp formatting
// AFTER: Uses formatTimestamps helper
class WrestlerResource extends BaseWrestlerResource
{
    public function toArray($request): array
    {
        return array_merge(
            parent::toArray($request),
            $this->promotionsData(),
            [
                'debut_date' => $this->formatDate($this->resource->debut_date),
                //                'country' => $this->resource->country,
                'title_reigns' => $this->whenLoaded('titleReigns', fn () => $this->formatTitleReigns($this->resource->titleReigns)),
            ],
            $this->formatTimestamps() // Using helper instead of manual formatting!
        );
    }
}
