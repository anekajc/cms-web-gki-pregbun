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
        // Freely add/edit/delete/reorder items for the Bajem Benowo page's two
        // lists: Jadwal Ibadah and Pelayanan, distinguished by `section`.
        Schema::create('bajem_benowo_items', function (Blueprint $table) {
            $table->id();
            $table->string('section')->index(); // 'ibadah' | 'pelayanan'
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('schedules')->nullable();
            $table->string('location')->nullable();
            $table->string('audience')->nullable();
            $table->string('cadence')->nullable(); // pelayanan only
            $table->string('image_public_id')->nullable();
            $table->string('image_url')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bajem_benowo_items');
    }
};
