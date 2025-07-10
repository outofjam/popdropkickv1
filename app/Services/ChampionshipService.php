<?php

namespace App\Services;

use App\Models\Championship;
use App\Models\Promotion;

class ChampionshipService
{
    /**
     * Create a new championship under the given promotion.
     */
    public function createChampionship(Promotion $promotion, array $data): Championship
    {
        return $promotion->championships()->create($data);
    }
}
