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

    public function updateChampionship(Championship $championship, array $data): Championship
    {
        $championship->update($data);
        return $championship->fresh();
    }

    public function toggleActiveStatus(Championship $championship): Championship
    {
        $championship->active = !$championship->active;
        $championship->save();
        return $championship->fresh();
    }
}
