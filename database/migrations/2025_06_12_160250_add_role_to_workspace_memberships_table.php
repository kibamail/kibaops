<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workspace_memberships', function (Blueprint $table) {
            $table->enum('role', ['developer', 'admin'])->default('developer')->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('workspace_memberships', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
