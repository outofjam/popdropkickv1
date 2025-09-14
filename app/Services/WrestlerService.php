<?php

namespace App\Services;

use App\Models\Promotion;
use App\Models\Wrestler;
use App\Models\WrestlerName;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class WrestlerService
{
    /**
     * Get paginated wrestlers with relations.
     */
    public function getPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return Wrestler::with([
            'names:id,wrestler_id,name,is_primary',
            'activePromotions:id,name,abbreviation',
            'activeTitleReigns.championship:id,name,slug',
            'activeTitleReigns.aliasAtWin:id,wrestler_id,name',
        ])->paginate($perPage);
    }

    /**
     * Find wrestler by UUID or slug.
     */
    /**
     * @deprecated Use route model binding instead via `Wrestler $wrestler`
     */
    public function findByIdOrSlug(string $identifier): ?Wrestler
    {
        $wrestler = Wrestler::find($identifier)
            ?? Wrestler::where('slug', $identifier)->first();

        return $wrestler?->load([
            'names:id,wrestler_id,name,is_primary',
            'promotions',
            'activePromotions',
            'activeTitleReigns.championship',
            'titleReigns.championship',
        ]);
    }

    /**
     * Create a new wrestler with related aliases and promotions.
     */
    public function create(array $data): Wrestler
    {
        $titleReigns = data_get($data, 'title_reigns', []);
        $aliases = data_get($data, 'aliases', []);
        $promotionIds = data_get($data, 'promotions', []);
        $activePromotionIds = data_get($data, 'active_promotions', []);

        $wrestlerData = $this->extractWrestlerData($data);

        $wrestler = Wrestler::create($wrestlerData);

        $this->createAliases($wrestler, $aliases);
        $this->syncPromotionRelations($wrestler, 'promotions', $promotionIds);
        $this->syncPromotionRelations($wrestler, 'activePromotions', $activePromotionIds);

        $this->syncTitleReigns($wrestler, $titleReigns);

        $this->generateSlugFor($wrestler);

        return $this->loadRelations($wrestler);
    }

    /**
     * Update an existing wrestler with partial data.
     */
    public function update(Wrestler $wrestler, array $data): Wrestler
    {
        $titleReigns = data_get($data, 'title_reigns');
        $aliases = data_get($data, 'aliases');
        $promotionIds = data_get($data, 'promotions');
        $activePromotionIds = data_get($data, 'active_promotions');

        $wrestlerData = $this->extractWrestlerData($data);

        if (! empty($wrestlerData)) {
            $wrestler->update($wrestlerData);
        }

        if (is_array($aliases)) {
            $wrestler->names()->delete();
            $this->createAliases($wrestler, $aliases);
            $this->generateSlugFor($wrestler);
        }

        Log::info('Promotions received for sync:', ['promotions' => $promotionIds]);

        $this->syncPromotionRelations($wrestler, 'promotions', $promotionIds);
        $this->syncPromotionRelations($wrestler, 'activePromotions', $activePromotionIds);

        if (is_array($titleReigns)) {
            $this->syncTitleReigns($wrestler, $titleReigns, true);
        }

        return $this->loadRelations($wrestler);
    }

    /**
     * Sync promotions or active promotions on wrestler.
     * Accepts an array of IDs, names, slugs, or abbreviations.
     */
    private function syncPromotionRelations(Wrestler $wrestler, string $relationName, ?array $identifiers): void
    {
        if (! is_array($identifiers)) {
            return;
        }

        $uuidPattern = '/^[0-9a-fA-F\-]{36}$/'; // simple UUID regex

        $ids = [];
        $strings = [];

        foreach ($identifiers as $idOrString) {
            $idOrString = (string) $idOrString;
            if (preg_match($uuidPattern, $idOrString)) {
                // It's a UUID string, treat as ID
                $ids[] = $idOrString;
            } elseif (ctype_digit($idOrString)) {
                // Numeric string, convert to int
                $ids[] = (int) $idOrString;
            } else {
                // Otherwise treat as string to match name/slug/abbr
                $strings[] = $idOrString;
            }
        }

        // Find IDs matching the string identifiers by name, slug, or abbreviation
        if (! empty($strings)) {
            $matchedIds = Promotion::where(static function ($query) use ($strings) {
                $query->whereIn('name', $strings)
                    ->orWhereIn('slug', $strings)
                    ->orWhereIn('abbreviation', $strings);
            })->pluck('id')->toArray();

            $ids = array_merge($ids, $matchedIds);
        }

        // Remove duplicates and validate existing IDs (including UUID strings)
        $validIds = Promotion::whereIn('id', array_unique($ids))->pluck('id')->toArray();

        $wrestler->$relationName()->sync($validIds);
    }

    /**
     * Load common relations on wrestler.
     */
    private function loadRelations(Wrestler $wrestler): Wrestler
    {
        return $wrestler->load('names', 'promotions', 'activePromotions');
    }

    /**
     * Create alias records for wrestler.
     */
    private function createAliases(Wrestler $wrestler, array $aliases): void
    {
        if (empty($aliases)) {
            return;
        }

        $aliases = array_map(static fn ($alias) => array_merge(['is_primary' => false], $alias), $aliases);

        if (! collect($aliases)->contains('is_primary', true)) {
            $aliases[0]['is_primary'] = true;
        }

        $wrestler->names()->createMany($aliases);
    }

    /**
     * Generate a unique slug based on primary alias name.
     */
    private function generateSlugFor(Wrestler $wrestler): void
    {
        $primaryName = $wrestler->primaryName()->first();

        if ($primaryName) {
            $slugBase = Str::slug($primaryName->name);
            $slug = Wrestler::generateUniqueSlug($slugBase);
            $wrestler->slug = $slug;
            $wrestler->saveQuietly();
        }
    }

    /**
     * Extract only direct Wrestler attributes from input.
     */
    private function extractWrestlerData(array $data): array
    {
        return collect($data)
            ->except(['aliases', 'promotions', 'active_promotions', 'title_reigns'])
            ->toArray();
    }

    /**
     * Sync title reigns for the wrestler.
     */
    private function syncTitleReigns(Wrestler $wrestler, array $titleReignsData, bool $isUpdate = false): void
    {
        if ($isUpdate) {
            $wrestler->titleReigns()->delete();
        }

        foreach ($titleReignsData as $reignData) {
            $wrestler->titleReigns()->create($reignData);
        }
    }

    public function addAliases(Wrestler $wrestler, array $aliases): array
{
    return collect($aliases)->map(function ($data) use ($wrestler) {
        return $this->addAlias($wrestler, $data);
    })->all();
}


    public function addAlias(Wrestler $wrestler, array $data): WrestlerName
    {
        $data['is_primary'] = (bool) ($data['is_primary'] ?? false);

        // If making it primary, unmark others
        if ($data['is_primary']) {
            $wrestler->names()->update(['is_primary' => false]);
        }

        $alias = $wrestler->names()->create($data);

        // Optionally update slug if it's the new primary
        if ($data['is_primary']) {
            $this->generateSlugFor($wrestler->fresh());
        }

        return $alias;
    }

    public function removeAlias(Wrestler $wrestler, string $aliasId): bool
    {
        $alias = $wrestler->names()->where('id', $aliasId)->first();

        if (! $alias) {
            return false;
        }

        $deleted = $alias->delete();

        // If it was primary, assign another as primary + update slug
        if ($alias->is_primary) {
            $newPrimary = $wrestler->names()->first();
            if ($newPrimary) {
                $newPrimary->update(['is_primary' => true]);
                $this->generateSlugFor($wrestler->fresh());
            }
        }

        return $deleted;
    }

    public function updatePromotions(Wrestler $wrestler, array $data): Wrestler
    {
        $remove = $this->resolvePromotionIds(data_get($data, 'remove_promotions', []));
        $deactivate = $this->resolvePromotionIds(data_get($data, 'deactivate_promotions', []));
        $addInactive = $this->resolvePromotionIds(data_get($data, 'add_promotions.inactive', []));
        $addActive = $this->resolvePromotionIds(data_get($data, 'add_promotions.active', []));

        $currentPromotions = $wrestler->promotions()->pluck('promotions.id')->toArray();

        $this->detachPromotions($wrestler, $remove);
        $this->deactivatePromotions($wrestler, $deactivate);
        $this->attachInactivePromotions($wrestler, $addInactive, $remove);
        $this->attachActivePromotions($wrestler, $addActive, $remove, $currentPromotions);

        return $wrestler->load('promotions', 'activePromotions');
    }

    private function detachPromotions(Wrestler $wrestler, array $promotionIds): void
    {
        if (empty($promotionIds)) {
            return;
        }

        $wrestler->promotions()->detach($promotionIds);
        $wrestler->activePromotions()->detach($promotionIds);
    }

    private function deactivatePromotions(Wrestler $wrestler, array $promotionIds): void
    {
        if (empty($promotionIds)) {
            return;
        }

        $wrestler->activePromotions()->detach($promotionIds);
    }

    private function attachInactivePromotions(Wrestler $wrestler, array $promotionIds, array $removeIds): void
    {
        $toAttach = array_diff($promotionIds, $removeIds);

        if (! empty($toAttach)) {
            $wrestler->promotions()->syncWithoutDetaching($toAttach);
        }
    }

    private function attachActivePromotions(
        Wrestler $wrestler,
        array $promotionIds,
        array $removeIds,
        array $currentPromotionIds
    ): void {
        $toAttach = array_diff($promotionIds, $removeIds);

        if (empty($toAttach)) {
            return;
        }

        // Make sure they're in `promotions` before activating
        $missingInInactive = array_diff($toAttach, $currentPromotionIds);
        if (! empty($missingInInactive)) {
            $wrestler->promotions()->syncWithoutDetaching($missingInInactive);
        }

        $wrestler->activePromotions()->syncWithoutDetaching($toAttach);
    }

    /**
     * Resolve flexible identifiers (id, slug, name, abbreviation) to promotion IDs.
     */
    private function resolvePromotionIds(array $identifiers): array
    {
        if (empty($identifiers)) {
            return [];
        }

        $identifiers = array_map('strval', $identifiers);

        return Promotion::whereIn('id', $identifiers)
            ->orWhereIn('slug', $identifiers)
            ->orWhereIn('name', $identifiers)
            ->orWhereIn('abbreviation', $identifiers)
            ->pluck('id')
            ->toArray();
    }
}
