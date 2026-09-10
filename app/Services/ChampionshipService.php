<?php

namespace App\Services;

use App\Models\Championship;
use App\Models\Promotion;
use App\Models\TitleReign;
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

    /**
     * Longest/shortest reign and the wrestler with the most reigns, over
     * $championship->titleReigns (must already be loaded).
     */
    public function getReignStatistics(Championship $championship): array
    {
        $reigns = $championship->titleReigns;

        if ($reigns->isEmpty()) {
            return [
                'longest_reign' => null,
                'shortest_reign' => null,
                'most_reigns' => null,
            ];
        }

        return [
            'longest_reign' => $this->summarizeReign($reigns->sortByDesc('reign_length_in_days')->first()),
            'shortest_reign' => $this->summarizeReign($reigns->sortBy('reign_length_in_days')->first()),
            'most_reigns' => $this->summarizeMostReigns($reigns),
        ];
    }

    private function summarizeReign(TitleReign $reign): array
    {
        $wrestler = $reign->resolved_wrestler;

        return [
            'wrestler' => $wrestler ? [
                'id' => $wrestler->id,
                'slug' => $wrestler->slug,
            ] : null,
            'alias_name' => $reign->resolved_display_name_at_win,
            'reign_length_in_days' => $reign->reign_length_in_days,
            'won_on' => $reign->won_on?->toDateString(),
            'lost_on' => $reign->lost_on?->toDateString(),
        ];
    }

    private function summarizeMostReigns($reigns): ?array
    {
        $byWrestler = $reigns
            ->groupBy(fn (TitleReign $reign) => $reign->resolved_wrestler?->id)
            ->filter(fn ($group, $wrestlerId) => $wrestlerId !== null);

        if ($byWrestler->isEmpty()) {
            return null;
        }

        $topGroup = $byWrestler->sortByDesc(fn ($group) => $group->count())->first();
        $wrestler = $topGroup->first()->resolved_wrestler;

        return [
            'wrestler' => [
                'id' => $wrestler->id,
                'slug' => $wrestler->slug,
            ],
            'alias_name' => $topGroup->first()->resolved_display_name_at_win,
            'reign_count' => $topGroup->count(),
        ];
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
