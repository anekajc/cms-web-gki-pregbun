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
        // Singleton settings row for the Bajem Benowo page, following the
        // HomeSetting::current() pattern — a single row materialized on first
        // use, no seeder required.
        Schema::create('bajem_benowo_settings', function (Blueprint $table) {
            $table->id();
            $table->text('about_description')->nullable();
            $table->string('about_image_public_id')->nullable();
            $table->string('about_image_url')->nullable();
            $table->text('pelayanan_intro')->nullable();
            $table->text('address')->nullable();
            $table->string('maps_url')->nullable();
            $table->text('map_embed_url')->nullable();
            $table->string('location_image_public_id')->nullable();
            $table->string('location_image_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bajem_benowo_settings');
    }
};
