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
        Schema::create('workspace_memberships', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('email');
            $table->timestamps();

            $table->unique(['workspace_id', 'email']);
            $table->index(['user_id']);
            $table->index(['email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workspace_memberships');
    }
};
