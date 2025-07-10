<?php

namespace App\Http\Resources;

use DateTimeInterface;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

abstract class BaseResource extends JsonResource
{
    protected function formatDate(?DateTimeInterface $date): ?string
    {
        return $date?->toDateString();
    }

    protected function formatTimestamp(DateTimeInterface $timestamp): string
    {
        return $timestamp->toIso8601String();
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

    // ENHANCED: Title reign formatter (keeping your original logic)
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
                'reign_number' => $reign->reign_number,
                'win_type' => $reign->win_type ?? null,
                'reign_length' => $reign->reign_length_in_days,
                'reign_length_human' => $reign->reign_length_human,
            ];
        });
    }

    // NEW: Simplified title reign formatter for championship lists
    protected function formatTitleReignsForChampionship($reigns)
    {
        return $reigns->map(function ($reign) {
            return [
                'id' => $reign->id,
                'wrestler' => $this->formatWrestlerReference($reign->wrestler),
                'reign_length' => $reign->reign_length_in_days,
                'reign_length_human' => $reign->reign_length_human,
                'won_on' => $this->formatDate($reign->won_on),
                'won_at' => $reign->won_at,
                'lost_on' => $this->formatDate($reign->lost_on),
                'lost_at' => $reign->lost_on !== null && $reign->lost_at === null ? 'vacated' : $reign->lost_at,
            ];
        });
    }
}
