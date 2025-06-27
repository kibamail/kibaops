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
        Schema::create('workspace_membership_projects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_membership_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('project_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['workspace_membership_id', 'project_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workspace_membership_projects');
    }
};
