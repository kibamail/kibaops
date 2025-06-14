<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cloud_providers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 32);
            $table->enum('type', ['aws', 'hetzner', 'leaseweb', 'google_cloud', 'digital_ocean', 'linode', 'vultr', 'ovh']);
            $table->foreignUuid('workspace_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->index(['workspace_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cloud_providers');
    }
};
