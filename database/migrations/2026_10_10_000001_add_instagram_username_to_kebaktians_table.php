<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kebaktians', function (Blueprint $table) {
            // Stored without the leading "@"; the public site builds the profile link.
            $table->string('instagram_username', 30)->nullable()->after('youtube_url');
        });
    }

    public function down(): void
    {
        Schema::table('kebaktians', function (Blueprint $table) {
            $table->dropColumn('instagram_username');
        });
    }
};
