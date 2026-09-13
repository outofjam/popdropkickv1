<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('title_reign_wrestlers', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('title_reign_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('wrestler_id')->constrained()->cascadeOnDelete();

            $table->uuid('wrestler_name_id_at_win')->nullable();
            $table->foreign('wrestler_name_id_at_win')
                ->references('id')->on('wrestler_names')
                ->nullOnDelete();

            $table->timestamps();
        });

        // Backfill: one participant row per existing title reign, mirroring
        // its current (single) wrestler_id / wrestler_name_id_at_win. Kept
        // in sync going forward by TitleReign's saved hook.
        DB::table('title_reigns')->orderBy('id')->chunkById(200, function ($reigns) {
            $now = now();

            $rows = $reigns
                ->filter(fn ($reign) => $reign->wrestler_id !== null)
                ->map(fn ($reign) => [
                    'id' => (string) Str::uuid(),
                    'title_reign_id' => $reign->id,
                    'wrestler_id' => $reign->wrestler_id,
                    'wrestler_name_id_at_win' => $reign->wrestler_name_id_at_win,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
                ->values()
                ->all();

            if ($rows !== []) {
                DB::table('title_reign_wrestlers')->insert($rows);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('title_reign_wrestlers');
    }
};
