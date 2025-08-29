<?php

namespace App\Services;

use App\Models\Championship;
use App\Models\TitleReign;
use App\Models\Wrestler;

class TitleReignService
{
    public function storeForWrestler(array $data, Wrestler $wrestler): TitleReign
    {

        // Provide a default reign_number to pass DB NOT NULL constraint
        $data['reign_number'] = 1;
        // Create reign via relationship, sets wrestler_id automatically
        $reign = $wrestler->titleReigns()->create($data);

        // Load Championship model to pass to renumberReigns
        $championship = $reign->championship()->firstOrFail();

        $this->renumberReigns($championship, $wrestler);

        return $reign;
    }

    public function updateReign(TitleReign $reign, array $data): void
    {
        $reign->update($data);

        $championship = $reign->championship()->firstOrFail();
        $wrestler = $reign->wrestler()->firstOrFail();

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
        $wrestler = $reign->wrestler()->firstOrFail();

        $reign->delete();

        $this->renumberReigns($championship, $wrestler);
    }
}
