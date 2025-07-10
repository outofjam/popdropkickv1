<?php

// app/Http/Resources/PromotionResource.php

namespace App\Http\Resources;

// BEFORE: Manual promotion reference building
// AFTER: Uses formatPromotionReference helper
class PromotionNestedResource extends BaseResource
{
    public function toArray($request): array
    {
        return array_merge(
            $this->formatPromotionReference($this->resource),
            [
                'abbreviation' => $this->resource->abbreviation,
            ]
        );
    }
}
