<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Users can log in with a username as well as their email. It starts null
     * — EnsurePasswordChanged holds anyone without one on the password page
     * until they pick it. Stored lowercase, so the plain unique index is
     * effectively case-insensitive.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 30)->nullable()->unique()->after('name');
        });

        // Snapshot alongside user_name, so log entries keep it after renames/deletes.
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->string('user_username', 30)->nullable()->after('user_name');
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropColumn('user_username');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn('username');
        });
    }
};
