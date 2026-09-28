<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Admin-facing audit trail: one row per successful CMS change. Rows are
     * kept forever; `user_name` is a snapshot so entries survive user deletion.
     */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name');
            $table->string('menu');
            $table->string('action');
            $table->string('subject')->nullable();
            // Text + 'array' cast rather than json: never queried into, and this
            // sidesteps Postgres/SQLite JSON differences.
            $table->text('changes')->nullable();
            $table->string('image_url', 2048)->nullable();
            $table->timestamps();

            $table->index('created_at');
            $table->index('menu');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
