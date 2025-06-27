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
        Schema::create('clusters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 64);
            $table->string('slug', 128);
            $table->enum('status', ['Healthy', 'Unhealthy', 'Pending'])->default('Pending');
            $table->foreignUuid('workspace_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('cloud_provider_id')->constrained()->onDelete('cascade');
            $table->string('region', 64);
            $table->boolean('shared_storage_worker_nodes')->default(false);
            $table->string('vault_ssh_key_path')->nullable();
            $table->timestamps();

            $table->unique(['workspace_id', 'slug']);
            $table->index(['workspace_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clusters');
    }
};
