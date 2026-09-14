<?php

namespace App\Rules;

use App\Models\TitleReign;
use Illuminate\Contracts\Validation\ImplicitRule;

/**
 * Fails when $value (a lost_on date) is null - i.e. the reign being
 * validated would be open - and the given championship already has a
 * different open reign (lost_on IS NULL). A championship can only have
 * one open reign at a time; closed/historical reigns never conflict.
 *
 * Implements the (legacy but still supported) ImplicitRule so it still runs
 * when lost_on is null/absent - the modern ValidationRule contract is
 * skipped by the "nullable" rule before it ever runs.
 */
class NoOpenReignForChampionship implements ImplicitRule
{
    private const MESSAGE = 'This championship already has an open title reign. Close it (set lost_on) before creating or reopening another.';

    public function __construct(
        private readonly ?string $championshipId,
        private readonly ?string $excludeReignId = null,
    ) {}

    public function passes($attribute, $value): bool
    {
        if ($value !== null || ! $this->championshipId) {
            return true;
        }

        $query = TitleReign::query()
            ->where('championship_id', $this->championshipId)
            ->whereNull('lost_on');

        if ($this->excludeReignId) {
            $query->whereKeyNot($this->excludeReignId);
        }

        return ! $query->exists();
    }

    public function message(): string
    {
        return self::MESSAGE;
    }
}
