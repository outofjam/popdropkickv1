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
        // Resolving id/slug -> row is a single indexed lookup, so it's cheap
        // enough to run uncached on every call (including 404s). Only the
        // heavy relationship graph loaded below is worth caching.
        $promotion = $this->resolvePromotion($identifier);

        if (! $promotion) {
            return null;
        }

        $cacheKey = "promotion:{$promotion->id}:inclInactive:".(int) $includeInactive;

        return Cache::remember($cacheKey, 300, function () use ($promotion, $includeInactive) {
            return $this->loadPromotionGraph($promotion, $includeInactive);
        });
    }

    /**
     * Show a single promotion (optionally include inactive wrestlers).
     */
    public function findByIdOrSlug(string $identifier, bool $includeInactive = false): ?Promotion
    {
        $promotion = $this->resolvePromotion($identifier);

        if (! $promotion) {
            return null;
        }

        return $this->loadPromotionGraph($promotion, $includeInactive);
    }

    /**
     * Fast point lookup by PK *or* slug (no OR).
     */
    private function resolvePromotion(string $identifier): ?Promotion
    {
        if (Str::isUuid($identifier)) {
            $promotion = Promotion::find($identifier);

            if ($promotion) {
                return $promotion;
            }
        }

        return Promotion::where('slug', $identifier)->first();
    }

    /**
     * Load the heavy relationship graph only once we have the single row.
     */
    private function loadPromotionGraph(Promotion $promotion, bool $includeInactive): Promotion
    {
        $with = [
            // For a promotion's page we show its active roster plus each
            // active wrestler's active reigns (and how to resolve/display them).
            'activeWrestlers.names',
            'activeWrestlers.activeTitleReigns.championship',
            'activeWrestlers.activeTitleReigns.aliasAtWin:id,wrestler_id,name',
            'activeWrestlers.activeTitleReigns.aliasAtWin.wrestler:id,slug',
            'activeWrestlers.activeTitleReigns.wrestler:id,slug',
            'activeWrestlers.activeTitleReigns.wrestler.primaryName:id,wrestler_id,name',

            // Active championships need their current champion resolved.
            'activeChampionships.currentTitleReign.aliasAtWin:id,wrestler_id,name',
            'activeChampionships.currentTitleReign.aliasAtWin.wrestler:id,slug',
            'activeChampionships.currentTitleReign.wrestler:id,slug',
            'activeChampionships.currentTitleReign.wrestler.primaryName:id,wrestler_id,name',

            // All championships (including inactive/retired belts) so their
            // last-held champion can still be shown.
            'championships.currentTitleReign.aliasAtWin:id,wrestler_id,name',
            'championships.currentTitleReign.aliasAtWin.wrestler:id,slug',
            'championships.currentTitleReign.wrestler:id,slug',
            'championships.currentTitleReign.wrestler.primaryName:id,wrestler_id,name',
        ];

        if ($includeInactive) {
            $with[] = 'wrestlers.names';
        }

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
