<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('source_code_webhook_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('source_code_connection_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('source_code_repository_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('external_event_id')->nullable();
            $table->string('event_type');
            $table->string('event_action')->nullable();
            $table->string('branch_name')->nullable();
            $table->string('commit_sha')->nullable();
            $table->json('payload');
            $table->json('normalized_payload')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->string('processing_status')->default('pending');
            $table->integer('processing_attempts')->default(0);
            $table->timestamps();

            $table->index(['source_code_connection_id', 'processing_status']);
            $table->index(['source_code_repository_id', 'event_type']);
            $table->index(['processing_status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('source_code_webhook_events');
    }
};
