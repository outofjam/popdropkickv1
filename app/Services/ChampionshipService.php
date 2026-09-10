<?php

namespace App\Services;

use App\Models\Championship;
use App\Models\Promotion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ChampionshipService
{
    /**
     * Create a new championship under the given promotion.
     */
    public function createChampionship(Promotion $promotion, array $data): Championship
    {
        return $promotion->championships()->create($data);
    }

    /**
     * Paginated list with each championship's promotion and current champion resolved.
     */
    public function getPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return Championship::with([
            'promotion:id,name,slug',
            'currentTitleReign.aliasAtWin:id,wrestler_id,name',
            'currentTitleReign.aliasAtWin.wrestler:id,slug',
            'currentTitleReign.wrestler:id,slug',
            'currentTitleReign.wrestler.primaryName:id,wrestler_id,name',
        ])->paginate($perPage);
    }

    /**
     * Fast point lookup by PK *or* slug (no OR), without the detail graph.
     */
    public function findByIdOrSlug(string $identifier): ?Championship
    {
        return Championship::query()
            ->where(fn ($q) => $q->whereKey($identifier)->orWhere('slug', $identifier))
            ->first();
    }

    /**
     * Lookup plus the full relationship graph used by the show/update responses.
     */
    public function findByIdOrSlugWithDetails(string $identifier): ?Championship
    {
        $championship = $this->findByIdOrSlug($identifier);

        return $championship ? $this->loadDetail($championship) : null;
    }

    /**
     * Promotion plus ordered title reigns with their alias/wrestler fallback chain.
     */
    public function loadDetail(Championship $championship): Championship
    {
        return $championship->load([
            'promotion:id,name,slug',
            'titleReigns' => fn ($q) => $q
                ->orderBy('won_on')
                ->with([
                    'championship:id,name,slug', // required by formatTitleReigns
                    'aliasAtWin:id,wrestler_id,name',
                    'aliasAtWin.wrestler:id,slug',
                    'wrestler:id,slug',
                    'wrestler.primaryName:id,wrestler_id,name',
                ]),
        ]);
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
