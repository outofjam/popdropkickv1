<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wrestlers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug')->unique();
            $table->string('ring_name')->nullable();
            $table->string('real_name')->nullable();
            $table->date('debut_date')->nullable();
            $table->string('country')->nullable();
            $table->timestamps();
            $table->index('real_name');
            $table->index('country');
            $table->index('debut_date');
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wrestlers');
    }
};
