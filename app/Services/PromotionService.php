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
            $with[] = 'wrestlers:id,name,slug'; // or include 'names' if needed
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
}
