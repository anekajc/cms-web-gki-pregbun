<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-user CMS access: one row per granted leaf key (e.g. "dashboard.warta",
     * "pelayanan.3"). Admins bypass this table entirely.
     */
    public function up(): void
    {
        Schema::create('user_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('permission');
            $table->timestamps();

            $table->unique(['user_id', 'permission']);
        });

        // Before this table existed every user could reach every page, so grant
        // existing non-admins everything to keep their access unchanged. Keys are
        // listed literally so later edits to App\Support\Access don't alter what
        // this migration did.
        $keys = [
            'dashboard.warta', 'dashboard.video',
            'tentangkami',
            'ibadah.home',
            'event.mingguan', 'event.spesial',
            'bajem.tentang', 'bajem.ibadah', 'bajem.pelayanan', 'bajem.lokasi',
            'komisi',
            'pembangunan.update', 'pembangunan.dana',
            'persembahan.hero', 'persembahan.item', 'persembahan.pembangunan',
            'master.pelayanan',
        ];

        foreach (DB::table('kebaktians')->pluck('id') as $id) {
            $keys[] = "ibadah.kebaktian.{$id}";
        }

        foreach (DB::table('pelayanan')->pluck('id') as $id) {
            $keys[] = "pelayanan.{$id}";
        }

        $now = now();

        foreach (DB::table('users')->where('role', 'user')->pluck('id') as $userId) {
            DB::table('user_permissions')->insert(array_map(fn ($key) => [
                'user_id' => $userId,
                'permission' => $key,
                'created_at' => $now,
                'updated_at' => $now,
            ], $keys));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_permissions');
    }
};
