<?php

use App\Models\ActivityLog;
use App\Models\Kebaktian;
use App\Models\Pelayanan;
use App\Models\User;
use App\Models\Warta;

function makeKebaktian(array $overrides = []): Kebaktian
{
    return Kebaktian::create([
        'slug' => 'umum',
        'title' => 'Kebaktian Umum',
        'location' => 'Gedung Utama',
        'schedules' => ['07:00', '09:00'],
        'order' => 1,
        ...$overrides,
    ]);
}

function editor(string ...$keys): User
{
    $user = User::factory()->create(['role' => 'user', 'name' => 'Budi']);

    foreach ($keys as $key) {
        $user->permissions()->create(['permission' => $key]);
    }

    return $user;
}

test('an update is logged with the user, menu, subject and old → new values', function () {
    $kebaktian = makeKebaktian();
    $budi = editor("ibadah.kebaktian.{$kebaktian->id}");
    $this->actingAs($budi);

    $this->put("/kebaktian/{$kebaktian->id}", [
        'location' => 'Gedung Utma',
        'schedules' => ['07:00'],
    ])->assertSessionDoesntHaveErrors();

    $log = ActivityLog::sole();
    expect($log->user_id)->toBe($budi->id);
    expect($log->user_name)->toBe('Budi');
    expect($log->menu)->toBe('ibadah');
    expect($log->action)->toBe('Mengubah informasi kebaktian');
    expect($log->subject)->toBe('Kebaktian Umum');
    expect($log->changes)->toContain(['label' => 'Lokasi', 'old' => 'Gedung Utama', 'new' => 'Gedung Utma']);
    expect($log->changes)->toContain(['label' => 'Jadwal', 'old' => ['07:00', '09:00'], 'new' => ['07:00']]);
});

test('failed validation and denied access are not logged', function () {
    $kebaktian = makeKebaktian();
    $other = makeKebaktian(['slug' => 'pemuda', 'title' => 'Kebaktian Pemuda']);
    $this->actingAs(editor("ibadah.kebaktian.{$kebaktian->id}"));

    $this->put("/kebaktian/{$kebaktian->id}", ['youtube_url' => 'bukan-url'])->assertSessionHasErrors('youtube_url');
    $this->put("/kebaktian/{$other->id}", ['location' => 'X'])->assertSessionHasErrors('access');

    expect(ActivityLog::count())->toBe(0);
});

test('a reorder is logged without a field diff', function () {
    $a = Pelayanan::create(['slug' => 'a', 'title' => 'A', 'subtitle' => '', 'description' => '', 'order' => 1]);
    $b = Pelayanan::create(['slug' => 'b', 'title' => 'B', 'subtitle' => '', 'description' => '', 'order' => 2]);
    $this->actingAs(User::factory()->admin()->create());

    $this->put('/master/pelayanan/reorder', ['ids' => [$b->id, $a->id]])->assertSessionDoesntHaveErrors();

    $log = ActivityLog::sole();
    expect($log->action)->toBe('Mengubah urutan pelayanan (master)');
    expect($log->changes)->toBeNull();
});

test('a delete keeps a snapshot of the old values', function () {
    $warta = Warta::create(['service_date' => '2026-10-04', 'title' => 'Warta Minggu', 'source_url' => 'https://drive.google.com/x', 'url' => 'https://x']);
    $this->actingAs(User::factory()->admin()->create());

    $this->delete("/warta/{$warta->id}")->assertSessionDoesntHaveErrors();

    $log = ActivityLog::sole();
    expect($log->action)->toBe('Menghapus warta');
    expect($log->subject)->toBe('Warta Minggu');
    expect($log->changes)->toContain(['label' => 'Judul', 'old' => 'Warta Minggu', 'new' => null]);
    expect($log->changes)->toContain(['label' => 'Tanggal Ibadah', 'old' => '2026-10-04', 'new' => null]);
    // The derived embed link is not shown.
    expect(collect($log->changes)->pluck('label'))->not->toContain('Gambar');
});

test('password regeneration never records the password', function () {
    $target = User::factory()->create(['name' => 'Sari']);
    $this->actingAs(User::factory()->admin()->create());

    $this->post("/user/{$target->id}/regenerate-password")->assertSessionDoesntHaveErrors();

    $log = ActivityLog::sole();
    expect($log->action)->toBe('Membuat password baru');
    expect($log->subject)->toBe('Sari');
    expect($log->changes)->toBeNull();
    expect(json_encode($log->getAttributes()))->not->toContain($target->refresh()->generated_password);
});

test('set pemakai logs granted and revoked sections by name', function () {
    $target = editor('komisi');
    $this->actingAs(User::factory()->admin()->create());

    $this->put("/user/{$target->id}/akses", ['permissions' => ['dashboard.warta']])->assertRedirect(route('user'));

    $log = ActivityLog::sole();
    expect($log->menu)->toBe('user');
    expect($log->subject)->toBe('Budi');
    expect($log->changes)->toBe([
        ['label' => 'Akses dicabut', 'old' => ['Komisi'], 'new' => null],
        ['label' => 'Akses diberikan', 'old' => null, 'new' => ['Dashboard › Warta Jemaat']],
    ]);
});

test('the log page is admin-only', function () {
    $this->actingAs(editor('komisi'));
    $this->get('/log-aktivitas')->assertRedirect(route('dashboard'));

    $this->actingAs(User::factory()->admin()->create());
    $this->get('/log-aktivitas')->assertOk();
});

test('filters narrow the log', function () {
    $budi = User::factory()->create(['name' => 'Budi']);
    $sari = User::factory()->create(['name' => 'Sari']);
    ActivityLog::create(['user_id' => $budi->id, 'user_name' => 'Budi', 'menu' => 'ibadah', 'action' => 'Mengubah informasi kebaktian', 'subject' => 'Kebaktian Umum']);
    ActivityLog::create(['user_id' => $sari->id, 'user_name' => 'Sari', 'menu' => 'event', 'action' => 'Menambah kegiatan', 'subject' => 'Retreat Pemuda']);
    $old = ActivityLog::create(['user_id' => $sari->id, 'user_name' => 'Sari', 'menu' => 'event', 'action' => 'Menghapus kegiatan', 'subject' => 'Lama']);
    $old->forceFill(['created_at' => now()->subMonth()])->save();

    $this->actingAs(User::factory()->admin()->create());

    $count = fn (array $query) => count($this->get(route('activity-log', $query))->viewData('page')['props']['logs']['data']);

    expect($count([]))->toBe(3);
    expect($count(['user' => $budi->id]))->toBe(1);
    expect($count(['menu' => 'event']))->toBe(2);
    expect($count(['q' => 'retreat']))->toBe(1);
    expect($count(['from' => now('Asia/Jakarta')->subDay()->format('Y-m-d')]))->toBe(2);
    expect($count(['to' => now('Asia/Jakarta')->subDays(7)->format('Y-m-d')]))->toBe(1);
});
