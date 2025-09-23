<?php

namespace App\Services;

use App\Models\Promotion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

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


    public function findByIdOrSlugCached(string $identifier, bool $includeInactive = false): ?Promotion
    {
        $cacheKey = "promotion:{$identifier}:inclInactive:".(int)$includeInactive;

        return Cache::remember($cacheKey, 300, function () use ($identifier, $includeInactive) {
            return $this->findByIdOrSlug($identifier, $includeInactive);
        });
    }

    /**
     * Show a single promotion (optionally include inactive wrestlers).
     */

    public function findByIdOrSlug(string $identifier, bool $includeInactive = false): ?Promotion
    {
        $with = [
            'activeWrestlers.names',
            'activeWrestlers.activeTitleReigns.championship',
            'activeWrestlers.activeTitleReigns.aliasAtWin:id,wrestler_id,name',
            'activeWrestlers.activeTitleReigns.aliasAtWin.wrestler:id,slug',
            'activeWrestlers.activeTitleReigns.wrestler:id,slug',
            'activeWrestlers.activeTitleReigns.wrestler.primaryName:id,wrestler_id,name',

            'activeChampionships.currentTitleReign.aliasAtWin:id,wrestler_id,name',
            'activeChampionships.currentTitleReign.aliasAtWin.wrestler:id,slug',
            'activeChampionships.currentTitleReign.wrestler:id,slug',
            'activeChampionships.currentTitleReign.wrestler.primaryName:id,wrestler_id,name',

            'championships.currentTitleReign.aliasAtWin:id,wrestler_id,name',
            'championships.currentTitleReign.aliasAtWin.wrestler:id,slug',
            'championships.currentTitleReign.wrestler:id,slug',
            'championships.currentTitleReign.wrestler.primaryName:id,wrestler_id,name',
        ];

        if ($includeInactive) {
            $with[] = 'wrestlers.names';
        }

        // 1) Fast point lookup by PK *or* slug (no OR).
        $promotion = null;

        if (Str::isUuid($identifier)) {
            $promotion = Promotion::find($identifier);
        }

        if (! $promotion) {
            $promotion = Promotion::where('slug', $identifier)->first();
        }

        if (! $promotion) {
            return null;
        }

        // 2) Load the heavy graph only once we have the single row.
        $promotion->load($with);

        return $promotion;
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
