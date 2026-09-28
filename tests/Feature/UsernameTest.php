<?php

use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Warta;

test('users can log in with their username, case-insensitively', function () {
    User::factory()->create(['username' => 'budi.santoso']);

    $this->post('/login', ['login' => 'Budi.Santoso', 'password' => 'password']);

    $this->assertAuthenticated();
});

test('a wrong username does not log in', function () {
    User::factory()->create(['username' => 'budi']);

    $this->post('/login', ['login' => 'sari', 'password' => 'password'])->assertSessionHasErrors('login');

    $this->assertGuest();
});

test('a new user must set a username and a new password on the same page', function () {
    $user = User::factory()->create(['username' => null, 'must_change_password' => true]);
    $this->actingAs($user);

    $this->get('/dashboard')->assertRedirect(route('password.edit'));

    $this->put('/settings/password', [
        'current_password' => 'password',
        'password' => 'rahasia-baru-123',
        'password_confirmation' => 'rahasia-baru-123',
    ])->assertSessionHasErrors('username');

    $this->put('/settings/password', [
        'username' => '  Budi_01 ',
        'current_password' => 'password',
        'password' => 'rahasia-baru-123',
        'password_confirmation' => 'rahasia-baru-123',
    ])->assertSessionHasNoErrors()->assertRedirect(route('dashboard'));

    $user->refresh();
    expect($user->username)->toBe('budi_01');
    expect($user->must_change_password)->toBeFalse();
});

test('an existing account without a username only has to pick one', function () {
    $user = User::factory()->admin()->create(['username' => null]);
    $this->actingAs($user);

    $this->get('/dashboard')->assertRedirect(route('password.edit'));

    $this->put('/settings/password', ['username' => 'admin.gereja'])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard'));

    expect($user->refresh()->username)->toBe('admin.gereja');
    $this->get('/dashboard')->assertOk();
});

test('invalid and taken usernames are rejected', function (string $username) {
    User::factory()->create(['username' => 'budi']);
    $this->actingAs(User::factory()->admin()->create(['username' => null]));

    $this->put('/settings/password', ['username' => $username])->assertSessionHasErrors('username');
})->with([
    'spaces' => 'budi santoso',
    'at sign' => 'budi@gereja',
    'too short' => 'bs',
    'taken' => 'budi',
    'taken, other case' => 'BUDI',
]);

test('users can change their username in profile settings', function () {
    $user = User::factory()->create(['username' => 'lama']);
    $this->actingAs($user);

    $this->patch('/settings/profile', ['name' => $user->name, 'username' => 'Baru', 'email' => $user->email])
        ->assertSessionHasNoErrors();

    expect($user->refresh()->username)->toBe('baru');
});

test('log entries keep the username of whoever made the change', function () {
    $this->actingAs(User::factory()->admin()->create(['username' => 'admin1']));

    $this->post('/warta', ['service_date' => '2026-10-04', 'source_url' => 'https://drive.google.com/file/d/abc/view'])
        ->assertSessionHasNoErrors();

    expect(Warta::count())->toBe(1);
    expect(ActivityLog::sole()->user_username)->toBe('admin1');
});
