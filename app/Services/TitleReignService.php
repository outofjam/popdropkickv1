<?php

// app/Services/TitleReignService.php

namespace App\Services;

use App\Models\Championship;
use App\Models\TitleReign;
use App\Models\Wrestler;

class TitleReignService
{
    public function storeForWrestler(array $data, Wrestler $wrestler): TitleReign
    {
        // Default alias to primary if omitted
        $data['wrestler_name_id_at_win'] ??= $wrestler->primaryName()->value('id');

        // Create through relation -> sets wrestler_id
        $reign = $wrestler->titleReigns()->create([
            'championship_id'          => $data['championship_id'],
            'won_on'                   => $data['won_on'],
            'won_at'                   => $data['won_at']  ?? null,
            'lost_on'                  => $data['lost_on'] ?? null,
            'lost_at'                  => $data['lost_at'] ?? null,
            'win_type'                 => $data['win_type'] ?? null,
            'reign_number'             => 1, // will be renumbered
            'wrestler_name_id_at_win'  => $data['wrestler_name_id_at_win'],
        ]);

        $this->renumberReigns($reign->championship, $wrestler);

        return $reign->load([
            'championship:id,name,slug',
            'aliasAtWin.wrestler:id,slug',
            'wrestler:id,slug',
            'wrestler.primaryName:id,wrestler_id,name',
        ]);
    }

    public function updateReign(TitleReign $reign, array $data): void
    {
        $reign->update($data);

        $championship = $reign->championship()->firstOrFail();
        $wrestler     = $reign->wrestler; // use relation property (already a model)

        $this->renumberReigns($championship, $wrestler);
    }

    private function renumberReigns(Championship $championship, Wrestler $wrestler): void
    {
        $reigns = TitleReign::query()
            ->where('championship_id', $championship->id)
            ->where('wrestler_id', $wrestler->id)
            ->orderBy('won_on')
            ->get();

        foreach ($reigns as $index => $reign) {
            $reign->updateQuietly(['reign_number' => $index + 1]);
        }
    }

    public function deleteReign(TitleReign $reign): void
    {
        $championship = $reign->championship()->firstOrFail();
        $wrestler     = $reign->wrestler; // use relation property

        $reign->delete();

        $this->renumberReigns($championship, $wrestler);
    }
}
