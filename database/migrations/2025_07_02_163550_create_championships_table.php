<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('championships', static function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug')->unique();
            $table->foreignUuid('promotion_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('abbreviation')->nullable();
            $table->string('division')->nullable(); // e.g. "Heavyweight", "Women's", etc.
            $table->date('introduced_at')->nullable();
            $table->timestamps();
            $table->string('weight_class')->nullable();
            $table->boolean('active')->default(true);
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->unique(['promotion_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('championships');
    }
};
