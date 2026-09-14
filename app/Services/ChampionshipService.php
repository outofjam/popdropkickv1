<?php

namespace App\Services;

use App\Models\Championship;
use App\Models\Promotion;
use App\Models\TitleReign;
use App\Models\TitleReignWrestler;
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
     * Paginated list with each championship's promotion and current champions resolved.
     */
    public function getPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return Championship::with([
            'promotion:id,name,slug',
            'currentTitleReign.team:id,name',
            'currentTitleReign.titleReignWrestlers.wrestler:id,slug',
            'currentTitleReign.titleReignWrestlers.wrestler.primaryName:id,wrestler_id,name',
            'currentTitleReign.titleReignWrestlers.aliasAtWin:id,wrestler_id,name',
            // Fallbacks if older rows still have wrestler_id populated but no participant row
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
                    'team:id,name',
                    'titleReignWrestlers.wrestler:id,slug',
                    'titleReignWrestlers.wrestler.primaryName:id,wrestler_id,name',
                    'titleReignWrestlers.aliasAtWin:id,wrestler_id,name',
                    // Fallbacks if older rows still have wrestler_id populated but no participant row
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
        return [
            'wrestlers' => $this->reignParticipants($reign)->map(fn ($participant) => [
                'id' => $participant->wrestler->id,
                'slug' => $participant->wrestler->slug,
                'alias_name' => $participant->resolved_display_name,
            ])->values()->all(),
            'reign_length_in_days' => $reign->reign_length_in_days,
            'won_on' => $reign->won_on?->toDateString(),
            'lost_on' => $reign->lost_on?->toDateString(),
        ];
    }

    /**
     * Every participant row across $reigns, one per (wrestler, reign) -
     * i.e. a tag reign contributes one row per co-champion. Falls back to
     * the reign's legacy singular wrestler for any reign whose participant
     * rows haven't been synced/loaded.
     */
    private function reignParticipants(TitleReign $reign)
    {
        $participants = $reign->titleReignWrestlers->filter(fn ($p) => $p->wrestler !== null);

        if ($participants->isNotEmpty()) {
            return $participants;
        }

        return $reign->resolved_wrestler
            ? collect([new TitleReignWrestler([
                'wrestler_id' => $reign->resolved_wrestler->id,
            ])])->each(fn ($p) => $p->setRelation('wrestler', $reign->resolved_wrestler))
            : collect();
    }

    private function summarizeMostReigns($reigns): ?array
    {
        $byWrestler = $reigns
            ->flatMap(fn (TitleReign $reign) => $this->reignParticipants($reign))
            ->groupBy(fn (TitleReignWrestler $participant) => $participant->wrestler->id);

        if ($byWrestler->isEmpty()) {
            return null;
        }

        $topGroup = $byWrestler->sortByDesc(fn ($group) => $group->count())->first();
        $wrestler = $topGroup->first()->wrestler;

        return [
            'wrestler' => [
                'id' => $wrestler->id,
                'slug' => $wrestler->slug,
            ],
            'alias_name' => $topGroup->first()->resolved_display_name,
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
        $championship->active = ! $championship->active;
        $championship->save();

        return $championship->fresh();
    }
}
