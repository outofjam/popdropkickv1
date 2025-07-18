<?php

namespace App\Services;

use App\Models\Promotion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PromotionService
{
    /**
     * Get paginated promotions with counts.
     */
    public function getPaginatedWithCounts(int $perPage = 15): LengthAwarePaginator
    {
        return Promotion::withCount(['wrestlers', 'activeWrestlers'])
            ->with('activeChampionships.currentTitleReign.wrestler.primaryName') // nested eager loading here
            ->paginate($perPage);
    }

    public function findByIdOrSlug(string $identifier, bool $includeInactive = false): ?Promotion
    {
        $with = [
            'activeWrestlers.names',
            'activeWrestlers.activeTitleReigns.championship',
            'activeChampionships.currentTitleReign.wrestler',
            'championships.currentTitleReign.wrestler',
        ];

        if ($includeInactive) {
            $with[] = 'wrestlers.names';  // eager load relation, not a column
        }

        return Promotion::with($with)
            ->where('id', $identifier)
            ->orWhere('slug', $identifier)
            ->first();
    }

    public function create(array $data): Promotion
    {
        $promotion = Promotion::create($data);

        // Eager load activeWrestlers to avoid null relations in resource
        $promotion->load('activeWrestlers');

        return $promotion;
    }

    public function getWrestlerCounts(Promotion $promotion): array
    {
        // Try to use loaded relationships first to avoid extra queries
        $active = $promotion->relationLoaded('activeWrestlers') ? $promotion->activeWrestlers : null;
        $all = $promotion->relationLoaded('wrestlers') ? $promotion->wrestlers : null;

        $activeCount = $active ? $active->count() : $promotion->activeWrestlers()->count();
        $totalCount = $all ? $all->count() : $promotion->wrestlers()->count();
        $inactiveCount = $totalCount - $activeCount;

        return [
            'active' => $activeCount,
            'inactive' => $inactiveCount,
        ];
    }

}
