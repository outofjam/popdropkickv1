<?php

namespace App\Http\Resources;

// BEFORE: Manual array merging and timestamp formatting
// AFTER: Uses formatTimestamps helper
class WrestlerListResource extends BaseWrestlerResource
{
    public function toArray($request): array
    {
        return array_merge(
            parent::toArray($request),
            $this->promotionsData(),
            [
                'debut_date' => $this->formatDate($this->resource->debut_date),
                //                'country' => $this->resource->country,
                'detail_url' => $this->detailUrl('wrestlers.show', $this->resource->slug),
            ],
            $this->formatTimestamps() // Using helper instead of manual formatting!
        );
    }
}
