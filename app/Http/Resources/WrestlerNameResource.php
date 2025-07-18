<?php

namespace App\Http\Resources;

class WrestlerNameResource extends BaseResource
{
    public function toArray($request): array
    {
        return [
//            'id' => $this->resource->id,
            'name' => $this->resource->name,
            //            'started_at' => $this->formatDate($this->resource->started_at), // Using helper instead of direct call
            //            'ended_at' => $this->formatDate($this->resource->ended_at),     // Using helper instead of direct call
        ];
    }
}
