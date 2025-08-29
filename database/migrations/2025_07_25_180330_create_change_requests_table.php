<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('change_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('action'); // 'create', 'update', 'delete'
            $table->string('model_type'); // 'wrestler', 'championship', etc.
            $table->uuid('model_id')->nullable(); // null for creates
            $table->json('data'); // proposed changes
            $table->json('original_data')->nullable(); // for updates/deletes
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignUuid('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reviewer_comments')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['status', 'created_at']);
            $table->index(['model_type', 'model_id']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('change_requests');
    }
};
