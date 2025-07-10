<?php

namespace App\Http\Resources;

class InactiveWrestlerResource extends BaseResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'slug' => $this->resource->slug,
            'primary_name' => stripslashes($this->resource->primaryName?->name),
            'detail_url' => $this->detailUrl('wrestlers.show', $this->resource->slug), // Now using helper!
        ];
    }
}
