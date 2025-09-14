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
            ->with([
                // For listing cards we usually show active champs + current champion
                'activeChampionships.currentTitleReign.aliasAtWin:id,wrestler_id,name',
                'activeChampionships.currentTitleReign.aliasAtWin.wrestler:id,slug',
                // Fallbacks if older rows still have wrestler_id populated
                'activeChampionships.currentTitleReign.wrestler:id,slug',
                'activeChampionships.currentTitleReign.wrestler.primaryName:id,wrestler_id,name',
            ])
            ->paginate($perPage);
    }

    /**
     * Show a single promotion (optionally include inactive wrestlers).
     */
    public function findByIdOrSlug(string $identifier, bool $includeInactive = false): ?Promotion
    {
        $with = [
            // Wrestlers currently active in the promotion
            'activeWrestlers.names',
            // Their active reigns (championship + alias-at-win + fallbacks)
            'activeWrestlers.activeTitleReigns.championship',
            'activeWrestlers.activeTitleReigns.aliasAtWin:id,wrestler_id,name',
            'activeWrestlers.activeTitleReigns.aliasAtWin.wrestler:id,slug',
            'activeWrestlers.activeTitleReigns.wrestler:id,slug',
            'activeWrestlers.activeTitleReigns.wrestler.primaryName:id,wrestler_id,name',

            // Championships in this promotion (current champ resolution)
            'activeChampionships.currentTitleReign.aliasAtWin:id,wrestler_id,name',
            'activeChampionships.currentTitleReign.aliasAtWin.wrestler:id,slug',
            'activeChampionships.currentTitleReign.wrestler:id,slug',
            'activeChampionships.currentTitleReign.wrestler.primaryName:id,wrestler_id,name',

            // If your resource shows inactive/retired belts too
            'championships.currentTitleReign.aliasAtWin:id,wrestler_id,name',
            'championships.currentTitleReign.aliasAtWin.wrestler:id,slug',
            'championships.currentTitleReign.wrestler:id,slug',
            'championships.currentTitleReign.wrestler.primaryName:id,wrestler_id,name',
        ];

        if ($includeInactive) {
            $with[] = 'wrestlers.names';
        }

        return Promotion::with($with)
            ->where(static fn ($q) => $q->where('id', $identifier)->orWhere('slug', $identifier))
            ->first();
    }

    public function create(array $data): Promotion
    {
        $promotion = Promotion::create($data);

        // Keep the index snappy after create
        $promotion->load('activeWrestlers');

        return $promotion;
    }

    public function getWrestlerCounts(Promotion $promotion): array
    {
        $active = $promotion->relationLoaded('activeWrestlers') ? $promotion->activeWrestlers : null;
        $all    = $promotion->relationLoaded('wrestlers')       ? $promotion->wrestlers       : null;

        $activeCount   = $active ? $active->count() : $promotion->activeWrestlers()->count();
        $totalCount    = $all    ? $all->count()    : $promotion->wrestlers()->count();
        $inactiveCount = $totalCount - $activeCount;

        return ['active' => $activeCount, 'inactive' => $inactiveCount];
    }
}
