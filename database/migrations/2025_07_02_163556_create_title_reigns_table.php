<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Only needed if you use database enum types (optional)

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('title_reigns', static function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('championship_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('wrestler_id')->constrained()->cascadeOnDelete();

            $table->unsignedInteger('reign_number'); // e.g. 1st, 2nd reign
            $table->date('won_on');
            $table->string('win_type')->nullable(); // enum in PHP layer
            $table->text('win_details')->nullable(); // e.g. "Defeated so-and-so at WrestleMania"
            $table->string('won_at')->nullable();
            $table->string('lost_at')->nullable();
            $table->date('lost_on')->nullable();
            $table->string('lost_type')->nullable();
            $table->text('lost_details')->nullable();
            $table->boolean('vacated')->default(false);
            $table->string('vacancy_reason')->nullable();

            $table->timestamps();

            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('title_reigns');
    }
};
