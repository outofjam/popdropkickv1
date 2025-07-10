<?php

// app/Http/Resources/WrestlerNameResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WrestlerNameNestedResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'name' => $this->resource->name,
        ];
    }
}
