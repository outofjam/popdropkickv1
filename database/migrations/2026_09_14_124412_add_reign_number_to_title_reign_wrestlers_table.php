<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('title_reign_wrestlers', function (Blueprint $table) {
            $table->unsignedInteger('reign_number')->nullable()->after('wrestler_name_id_at_win');

            // Marks the row TitleReign::syncPrimaryParticipant() owns (mirrored
            // from title_reigns.wrestler_id/wrestler_name_id_at_win), so that
            // hook can always find its own row instead of clobbering a
            // co-champion added directly via TitleReignService.
            $table->boolean('is_primary')->default(false)->after('reign_number');
        });

        // Every existing row is still a 1:1 mirror of its title_reign - it's
        // the primary participant, and its reign_number is simply the parent
        // reign's reign_number.
        DB::statement(<<<'SQL'
            UPDATE title_reign_wrestlers
            SET reign_number = (
                SELECT reign_number FROM title_reigns
                WHERE title_reigns.id = title_reign_wrestlers.title_reign_id
            ),
            is_primary = 1
        SQL);
    }

    public function down(): void
    {
        Schema::table('title_reign_wrestlers', function (Blueprint $table) {
            $table->dropColumn(['reign_number', 'is_primary']);
        });
    }
};
