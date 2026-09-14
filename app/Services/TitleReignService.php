<?php

// app/Services/TitleReignService.php

namespace App\Services;

use App\Models\Championship;
use App\Models\TitleReign;
use App\Models\TitleReignWrestler;
use App\Models\Wrestler;
use Illuminate\Support\Arr;

class TitleReignService
{
    public function storeForWrestler(array $data, Wrestler $wrestler): TitleReign
    {
        // Default alias to primary if omitted
        $data['wrestler_name_id_at_win'] ??= $wrestler->primaryName()->value('id');

        // Create through relation -> sets wrestler_id; syncPrimaryParticipant()
        // mirrors it into title_reign_wrestlers as the primary row.
        $reign = $wrestler->titleReigns()->create([
            'championship_id' => $data['championship_id'],
            'team_id' => $data['team_id'] ?? null,
            'won_on' => $data['won_on'],
            'won_at' => $data['won_at'] ?? null,
            'lost_on' => $data['lost_on'] ?? null,
            'lost_at' => $data['lost_at'] ?? null,
            'vacancy_reason' => $data['vacancy_reason'] ?? null,
            'win_type' => $data['win_type'] ?? null,
            'reign_number' => 1, // will be renumbered
            'wrestler_name_id_at_win' => $data['wrestler_name_id_at_win'],
        ]);

        $this->syncCoChampions($reign, $data['participants'] ?? []);
        $this->renumberAllParticipants($reign);

        return $reign->load($this->detailWith());
    }

    public function updateReign(TitleReign $reign, array $data): TitleReign
    {
        $reign->update(Arr::except($data, ['participants']));

        if (array_key_exists('participants', $data)) {
            $this->syncCoChampions($reign, $data['participants'] ?? [], replaceExisting: true);
        }

        $this->renumberAllParticipants($reign->refresh());

        return $reign->refresh()->load($this->detailWith());
    }

    public function deleteReign(TitleReign $reign): void
    {
        $championship = $reign->championship()->firstOrFail();
        $wrestlerIds = $reign->titleReignWrestlers()->pluck('wrestler_id')->all();

        $reign->delete(); // cascades title_reign_wrestlers

        foreach ($wrestlerIds as $wrestlerId) {
            $this->renumberReignsForWrestler($championship, $wrestlerId);
        }
    }

    /**
     * Championship + alias/wrestler fallback chain, plus the full
     * participant/team graph, used by the store/update responses.
     */
    private function detailWith(): array
    {
        return [
            'championship:id,name,slug',
            'team:id,name',
            'aliasAtWin:id,name,wrestler_id',
            'aliasAtWin.wrestler:id,slug',
            'wrestler:id,slug',
            'wrestler.primaryName:id,wrestler_id,name',
            'titleReignWrestlers.wrestler:id,slug',
            'titleReignWrestlers.wrestler.primaryName:id,wrestler_id,name',
            'titleReignWrestlers.aliasAtWin:id,name,wrestler_id',
        ];
    }

    /**
     * Add/update every co-champion (i.e. every participant other than the
     * reign's primary/route wrestler). With $replaceExisting, any existing
     * non-primary participant not present in $participants is removed -
     * used by updates, where an omitted co-champion means "no longer part
     * of this reign".
     */
    private function syncCoChampions(TitleReign $reign, array $participants, bool $replaceExisting = false): void
    {
        $keptIds = [];

        foreach ($participants as $participant) {
            if (($participant['wrestler_id'] ?? null) === $reign->wrestler_id) {
                continue; // already represented by the primary row
            }

            $row = $reign->titleReignWrestlers()->updateOrCreate(
                [
                    'title_reign_id' => $reign->id,
                    'wrestler_id' => $participant['wrestler_id'],
                    'is_primary' => false,
                ],
                [
                    'wrestler_name_id_at_win' => $participant['wrestler_name_id_at_win'] ?? null,
                ]
            );

            $keptIds[] = $row->id;
        }

        if ($replaceExisting) {
            $reign->titleReignWrestlers()
                ->where('is_primary', false)
                ->whereNotIn('id', $keptIds)
                ->delete();
        }
    }

    /**
     * Recompute reign_number for every wrestler participating in $reign
     * (primary and co-champions alike), across all of that wrestler's
     * reigns with this championship.
     */
    private function renumberAllParticipants(TitleReign $reign): void
    {
        $championship = $reign->championship()->firstOrFail();

        $wrestlerIds = $reign->titleReignWrestlers()->pluck('wrestler_id')->unique();

        foreach ($wrestlerIds as $wrestlerId) {
            $this->renumberReignsForWrestler($championship, $wrestlerId);
        }
    }

    /**
     * A given wrestler's reign_number for a championship is their own
     * position (by won_on) among every reign they've participated in for
     * that title - solo or as a co-champion. Mirrored onto title_reigns
     * .reign_number for whichever reigns they are the primary wrestler on,
     * to keep that column meaningful for existing consumers.
     */
    private function renumberReignsForWrestler(Championship $championship, string $wrestlerId): void
    {
        $participantRows = TitleReignWrestler::query()
            ->where('wrestler_id', $wrestlerId)
            ->whereHas('titleReign', fn ($q) => $q->where('championship_id', $championship->id))
            ->with('titleReign:id,won_on')
            ->get()
            ->sortBy(fn (TitleReignWrestler $row) => $row->titleReign->won_on)
            ->values();

        foreach ($participantRows as $index => $row) {
            $row->updateQuietly(['reign_number' => $index + 1]);

            if ($row->is_primary) {
                $row->titleReign->updateQuietly(['reign_number' => $index + 1]);
            }
        }
    }
}
