<?php

// database/migrations/xxxx_xx_xx_xxxxxx_add_wrestler_name_fk_to_title_reigns_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('title_reigns', function (Blueprint $table) {
            // singles-only: FK to the alias record used at win-time
            $table->uuid('wrestler_name_id_at_win')->nullable()->after('championship_id');

            $table->foreign('wrestler_name_id_at_win')
                ->references('id')->on('wrestler_names')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('title_reigns', function (Blueprint $table) {
            $table->dropForeign(['wrestler_name_id_at_win']);
            $table->dropColumn('wrestler_name_id_at_win');
        });
    }
};
