<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('active_promotion_wrestler', static function (Blueprint $table) {
            $table->uuid('promotion_id');
            $table->uuid('wrestler_id');
            $table->timestamps();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->primary(['promotion_id', 'wrestler_id']);
            $table->foreign('promotion_id')->references('id')->on('promotions')->cascadeOnDelete();
            $table->foreign('wrestler_id')->references('id')->on('wrestlers')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('active_promotion_wrestler');
    }
};
