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
        Schema::create('source_code_connections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained()->onDelete('cascade');
            $table->string('provider_type');
            $table->string('connection_name');
            $table->string('external_account_id');
            $table->string('external_account_name');
            $table->string('external_account_type');
            $table->string('avatar_url')->nullable();
            $table->json('permissions_scope')->nullable();
            $table->string('vault_credentials_path')->nullable();
            $table->string('connection_status')->default('active');
            $table->timestamp('last_sync_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['workspace_id', 'provider_type']);
            $table->index(['external_account_id', 'provider_type']);
            $table->unique(['workspace_id', 'external_account_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('source_code_connections');
    }
};
