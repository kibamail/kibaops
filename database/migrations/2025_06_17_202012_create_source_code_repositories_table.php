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
        Schema::create('source_code_repositories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('source_code_connection_id')->constrained()->onDelete('cascade');
            $table->string('external_repository_id');
            $table->string('name');
            $table->string('owner_repo');
            $table->text('description')->nullable();
            $table->string('visibility');
            $table->string('default_branch')->default('main');
            $table->json('clone_urls');
            $table->string('web_url');
            $table->string('language')->nullable();
            $table->json('topics')->nullable();
            $table->boolean('archived')->default(false);
            $table->boolean('fork')->default(false);
            $table->json('repository_metadata')->nullable();
            $table->boolean('webhook_configured')->default(false);
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['source_code_connection_id']);
            $table->index(['external_repository_id', 'source_code_connection_id']);
            $table->index(['owner_repo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('source_code_repositories');
    }
};
