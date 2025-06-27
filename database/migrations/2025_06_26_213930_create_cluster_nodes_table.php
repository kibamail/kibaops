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
        Schema::create('cluster_nodes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cluster_id')->constrained()->onDelete('cascade');
            $table->string('node_id', 128);
            $table->enum('type', ['worker', 'storage']);
            $table->enum('status', ['Healthy', 'Unhealthy', 'Pending'])->default('Pending');
            $table->string('public_ip', 45)->nullable();
            $table->string('private_ip', 45)->nullable();
            $table->string('public_ipv6', 45)->nullable();
            $table->string('private_ipv6', 45)->nullable();
            $table->string('server_type', 64)->nullable();
            $table->unsignedInteger('cpu_cores');
            $table->unsignedInteger('ram_gb');
            $table->unsignedInteger('disk_gb');
            $table->string('os', 64)->default('ubuntu-24.04');
            $table->timestamps();

            $table->unique(['cluster_id', 'node_id']);
            $table->index(['cluster_id', 'type']);
            $table->index(['cluster_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cluster_nodes');
    }
};
