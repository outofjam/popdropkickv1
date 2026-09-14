<?php

namespace App\Http\Resources;

use App\Models\TitleReign;
use DateTimeInterface;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

abstract class BaseResource extends JsonResource
{
    protected function formatDate(?DateTimeInterface $date): ?string
    {
        return $date?->toDateString();
    }

    protected function formatTimestamp(?DateTimeInterface $timestamp): ?string
    {
        return $timestamp?->toIso8601String();
    }

    protected function detailUrl(string $routeName, $identifier): string
    {
        return route($routeName, $identifier);
    }

    // NEW: Generic entity reference formatter
    protected function formatEntityReference($entity, string $routeName): array
    {
        return [
            'id' => $entity->id,
            'name' => $entity->name,
            'slug' => $entity->slug,
            'detail_url' => $this->detailUrl($routeName, $entity->slug ?? $entity->id),
        ];
    }

    // NEW: Specific entity reference helpers
    protected function formatWrestlerReference($wrestler): array
    {
        return $this->formatEntityReference($wrestler, 'wrestlers.show');
    }

    protected function formatPromotionReference($promotion): array
    {
        return $this->formatEntityReference($promotion, 'promotions.show');
    }

    protected function formatChampionshipReference($championship): array
    {
        return array_merge(
            $this->formatEntityReference($championship, 'championships.show'),
            ['weight_class' => $championship->weight_class ?? null]
        );
    }

    // NEW: Collection filtering helper
    protected function filterInactiveItems(Collection $allItems, Collection $activeItems): Collection
    {
        return $allItems->reject(fn ($item) => $activeItems->contains('id', $item->id));
    }

    // NEW: Common metadata formatters
    protected function formatTimestamps(): array
    {
        return [
            'created_at' => $this->formatTimestamp($this->resource->created_at),
            'updated_at' => $this->formatTimestamp($this->resource->updated_at),
        ];
    }

    protected function formatBasicEntityData(): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'slug' => $this->resource->slug,
        ];
    }

    protected function formatTitleReigns($reigns)
    {
        return $reigns->map(function ($reign) {
            return [
                'championship_id' => $reign->championship->id,
                'championship_name' => $reign->championship->name,
                'won_on' => $this->formatDate($reign->won_on),
                'won_at' => $reign->won_at,
                'lost_on' => $this->formatDate($reign->lost_on),
                'lost_at' => $reign->lost_on !== null && $reign->lost_at === null ? 'vacated' : $reign->lost_at,
                'vacancy_reason' => $reign->vacancy_reason,
                'win_type' => $reign->win_type ?? null,
                'reign_length' => $reign->reign_length_in_days,
                'reign_length_human' => $reign->reign_length_human,

                'team' => $this->formatTeamReference($reign->team),
                'wrestlers' => $this->formatParticipants($reign),
            ];
        });
    }

    protected function formatTitleReignsForChampionship($reigns)
    {
        return $reigns->map(function ($reign) {
            return [
                'id' => $reign->id,
                'team' => $this->formatTeamReference($reign->team),
                'wrestlers' => $this->formatParticipants($reign),
                'reign_length' => $reign->reign_length_in_days,
                'reign_length_human' => $reign->reign_length_human,
                'won_on' => $this->formatDate($reign->won_on),
                'won_at' => $reign->won_at,
                'lost_on' => $this->formatDate($reign->lost_on),
                'lost_at' => $reign->lost_on !== null && $reign->lost_at === null ? 'vacated' : $reign->lost_at,
                'vacancy_reason' => $reign->vacancy_reason,
            ];
        });
    }

    protected function formatTeamReference($team): ?array
    {
        return $team ? ['id' => $team->id, 'name' => $team->name] : null;
    }

    /**
     * Every wrestler holding $reign, each with their own alias-at-win and
     * their own reign_number for this championship. Falls back to the
     * legacy singular wrestler_id/wrestler_name_id_at_win columns for any
     * reign whose participant rows haven't been synced/loaded.
     */
    protected function formatParticipants(TitleReign $reign): array
    {
        $participants = $reign->relationLoaded('titleReignWrestlers')
            ? $reign->titleReignWrestlers
            : $reign->titleReignWrestlers()->get();

        if ($participants->isEmpty()) {
            $wrestler = $reign->resolved_wrestler;

            if (! $wrestler) {
                return [];
            }

            return [[
                'wrestler' => $this->formatWrestlerReference($wrestler),
                'alias_name' => $reign->resolved_display_name_at_win,
                'reign_number' => $reign->reign_number,
            ]];
        }

        return $participants
            ->filter(fn ($participant) => $participant->wrestler !== null)
            ->map(fn ($participant) => [
                'wrestler' => $this->formatWrestlerReference($participant->wrestler),
                'alias_name' => $participant->resolved_display_name,
                'reign_number' => $participant->reign_number,
            ])
            ->values()
            ->all();
    }

    /**
     * The champions currently holding $reign (or [] if there is no open
     * reign / it truly has no resolvable champion), shaped for
     * "current_champions"-style API fields.
     */
    protected function formatCurrentChampions(?TitleReign $reign): array
    {
        if (! $reign) {
            return [];
        }

        return array_map(function (array $participant) use ($reign) {
            return array_merge($participant['wrestler'], [
                'alias_name' => $participant['alias_name'],
                'team' => $this->formatTeamReference($reign->team),
                'reign_start' => $this->formatDate($reign->won_on),
                'reign_number' => $participant['reign_number'],
                'reign_length' => $reign->reign_length_in_days,
                'reign_length_human' => $reign->reign_length_human,
            ]);
        }, $this->formatParticipants($reign));
    }
}
