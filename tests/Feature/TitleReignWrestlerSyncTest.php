<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\TitleReign;
use App\Models\Wrestler;
use App\Models\WrestlerName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TitleReignWrestlerSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_title_reign_syncs_a_participant_row(): void
    {
        $wrestler = Wrestler::factory()->create();
        $alias = WrestlerName::factory()->create(['wrestler_id' => $wrestler->id]);

        $reign = TitleReign::factory()->create([
            'wrestler_id' => $wrestler->id,
            'wrestler_name_id_at_win' => $alias->id,
        ]);

        $this->assertCount(1, $reign->titleReignWrestlers);
        $participant = $reign->titleReignWrestlers->first();
        $this->assertEquals($wrestler->id, $participant->wrestler_id);
        $this->assertEquals($alias->id, $participant->wrestler_name_id_at_win);
    }

    public function test_updating_the_wrestler_on_a_reign_updates_the_participant_row_in_place(): void
    {
        $originalWrestler = Wrestler::factory()->create();
        $newWrestler = Wrestler::factory()->create();

        $reign = TitleReign::factory()->create(['wrestler_id' => $originalWrestler->id]);

        $reign->update(['wrestler_id' => $newWrestler->id]);

        $this->assertCount(1, $reign->titleReignWrestlers()->get());
        $this->assertEquals($newWrestler->id, $reign->titleReignWrestlers()->first()->wrestler_id);
    }

    public function test_updating_an_unrelated_field_does_not_duplicate_the_participant_row(): void
    {
        $reign = TitleReign::factory()->create();

        $reign->update(['win_type' => 'submission']);
        $reign->update(['won_at' => 'Some Other Event']);

        $this->assertCount(1, $reign->titleReignWrestlers()->get());
    }

    public function test_deleting_a_title_reign_cascades_to_its_participant_row(): void
    {
        $reign = TitleReign::factory()->create();
        $participantId = $reign->titleReignWrestlers()->first()->id;

        $reign->delete();

        $this->assertDatabaseMissing('title_reign_wrestlers', ['id' => $participantId]);
    }

    public function test_title_reign_can_be_assigned_an_optional_team(): void
    {
        $team = Team::factory()->create(['name' => 'The New Day']);

        $reign = TitleReign::factory()->create(['team_id' => $team->id]);

        $this->assertEquals('The New Day', $reign->fresh()->team->name);
    }

    public function test_title_reign_team_is_optional(): void
    {
        $reign = TitleReign::factory()->create();

        $this->assertNull($reign->team);
    }
}
