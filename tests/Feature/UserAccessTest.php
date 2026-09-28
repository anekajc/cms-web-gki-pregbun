<?php

use App\Models\Event;
use App\Models\Pelayanan;
use App\Models\User;
use App\Models\UserPermission;
use App\Models\Warta;

function userWithAccess(string ...$keys): User
{
    $user = User::factory()->create(['role' => 'user']);

    foreach ($keys as $key) {
        $user->permissions()->create(['permission' => $key]);
    }

    return $user;
}

function makePelayanan(string $slug): Pelayanan
{
    return Pelayanan::create(['slug' => $slug, 'title' => ucfirst($slug), 'subtitle' => '', 'description' => '', 'order' => 1]);
}

test('admins can open every page', function () {
    $this->actingAs(User::factory()->admin()->create());

    foreach (['/dashboard', '/tentangkami', '/kebaktian', '/event', '/pelayanan', '/bajem-benowo', '/komisi', '/pembangunan', '/persembahan', '/master/pelayanan', '/user'] as $url) {
        $this->get($url)->assertOk();
    }
});

test('a user with no access is sent to the no-access page', function () {
    $this->actingAs(userWithAccess());

    $this->get('/dashboard')->assertRedirect(route('no-access'));
    $this->get('/no-access')->assertOk();
});

test('a user without dashboard access lands on their first permitted page', function () {
    $this->actingAs(userWithAccess('pembangunan.dana', 'komisi'));

    // Komisi comes before Pembangunan in sidebar order.
    $this->get('/dashboard')->assertRedirect(route('komisi'));
    $this->get('/event')->assertRedirect(route('komisi'));
});

test('a section key opens the page but not its sibling sections', function () {
    $this->actingAs(userWithAccess('dashboard.video'));

    $this->get('/dashboard')->assertOk();

    $this->post('/warta', ['service_date' => '2026-10-04', 'source_url' => 'https://drive.google.com/x'])
        ->assertSessionHasErrors('access');
    expect(Warta::count())->toBe(0);
});

test('pelayanan edits are limited to the granted ministries', function () {
    $allowed = makePelayanan('poliklinik');
    $denied = makePelayanan('beasiswa');
    $this->actingAs(userWithAccess("pelayanan.{$allowed->id}"));

    $this->put("/pelayanan/{$allowed->id}", ['subtitle' => 'Boleh'])->assertSessionDoesntHaveErrors();
    $this->put("/pelayanan/{$denied->id}", ['subtitle' => 'Tidak'])->assertSessionHasErrors('access');

    expect($allowed->refresh()->subtitle)->toBe('Boleh');
    expect($denied->refresh()->subtitle)->toBe('');
});

test('event mutations are limited by event type', function () {
    $this->actingAs(userWithAccess('event.spesial'));

    $this->post('/event', [
        'title' => 'Doa Pagi',
        'location' => 'Gedung Gereja',
        'description' => 'Deskripsi.',
        'category' => 'ibadah',
        'type' => 'mingguan',
        'day' => 'Senin',
        'start_time' => '08:00',
    ])->assertSessionHasErrors('access');

    expect(Event::count())->toBe(0);
});

test('bajem settings fields are limited to their tab', function () {
    $this->actingAs(userWithAccess('bajem.tentang'));

    $this->put('/bajem-benowo/settings', ['about_description' => 'Tentang kami'])->assertSessionDoesntHaveErrors();
    $this->put('/bajem-benowo/settings', ['address' => 'Jl. Benowo'])->assertSessionHasErrors('access');
});

test('admins can set a user\'s access and unknown keys are rejected', function () {
    $pelayanan = makePelayanan('poliklinik');
    $target = userWithAccess('komisi');
    $this->actingAs(User::factory()->admin()->create());

    $this->get("/user/{$target->id}/akses")->assertOk();

    $this->put("/user/{$target->id}/akses", ['permissions' => ['dashboard.warta', "pelayanan.{$pelayanan->id}"]])
        ->assertRedirect(route('user'));
    expect($target->permissions()->pluck('permission')->sort()->values()->all())
        ->toBe(['dashboard.warta', "pelayanan.{$pelayanan->id}"]);

    $this->put("/user/{$target->id}/akses", ['permissions' => ['dashboard']])->assertSessionHasErrors('permissions.0');
});

test('non-admins cannot manage access', function () {
    $target = userWithAccess();
    $this->actingAs(userWithAccess('dashboard.warta'));

    $this->get("/user/{$target->id}/akses")->assertRedirect(route('dashboard'));
    $this->put("/user/{$target->id}/akses", ['permissions' => ['komisi']]);

    expect($target->permissions()->count())->toBe(0);
});

test('deleting a pelayanan in master removes its grants', function () {
    $pelayanan = makePelayanan('beasiswa');
    $holder = userWithAccess("pelayanan.{$pelayanan->id}");
    $this->actingAs(User::factory()->admin()->create());

    $this->delete("/master/pelayanan/{$pelayanan->id}")->assertSessionDoesntHaveErrors();

    expect(UserPermission::where('user_id', $holder->id)->count())->toBe(0);
});
