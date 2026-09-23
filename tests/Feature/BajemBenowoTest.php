<?php

use App\Models\BajemBenowoItem;
use App\Models\BajemBenowoSetting;
use App\Models\User;

test('updating settings persists text fields on the singleton row', function () {
    $this->actingAs(User::factory()->create());

    $this->put('/bajem-benowo/settings', [
        'about_description' => 'Sejarah Bajem Benowo.',
        'address' => "Jl. Contoh\nSurabaya",
    ])->assertSessionDoesntHaveErrors();

    $settings = BajemBenowoSetting::current();
    expect($settings->about_description)->toBe('Sejarah Bajem Benowo.');
    expect($settings->address)->toBe("Jl. Contoh\nSurabaya");
    expect(BajemBenowoSetting::count())->toBe(1);
});

test('storing an item requires a valid section', function () {
    $this->actingAs(User::factory()->create());

    $this->post('/bajem-benowo/items', ['section' => 'bukan-section', 'title' => 'Contoh'])
        ->assertSessionHasErrors('section');

    expect(BajemBenowoItem::count())->toBe(0);
});

test('storing an ibadah item persists schedules as an array', function () {
    $this->actingAs(User::factory()->create());

    $this->post('/bajem-benowo/items', [
        'section' => 'ibadah',
        'title' => 'Ibadah Umum',
        'schedules' => ['Minggu · 09.00 WIB'],
    ])->assertSessionDoesntHaveErrors();

    $item = BajemBenowoItem::sole();
    expect($item->section)->toBe('ibadah');
    expect($item->schedules)->toBe(['Minggu · 09.00 WIB']);
    expect($item->order)->toBe(1);
});

test('items in different sections order independently', function () {
    $this->actingAs(User::factory()->create());

    $this->post('/bajem-benowo/items', ['section' => 'ibadah', 'title' => 'Ibadah A'])->assertSessionDoesntHaveErrors();
    $this->post('/bajem-benowo/items', ['section' => 'pelayanan', 'title' => 'Pelayanan A'])->assertSessionDoesntHaveErrors();

    expect(BajemBenowoItem::where('section', 'ibadah')->sole()->order)->toBe(1);
    expect(BajemBenowoItem::where('section', 'pelayanan')->sole()->order)->toBe(1);
});

test('reordering only touches items within the given section', function () {
    $this->actingAs(User::factory()->create());
    $ibadahA = BajemBenowoItem::create(['section' => 'ibadah', 'title' => 'A', 'order' => 1]);
    $ibadahB = BajemBenowoItem::create(['section' => 'ibadah', 'title' => 'B', 'order' => 2]);
    $pelayananA = BajemBenowoItem::create(['section' => 'pelayanan', 'title' => 'P', 'order' => 1]);

    $this->put('/bajem-benowo/items/reorder', [
        'section' => 'ibadah',
        'ids' => [$ibadahB->id, $ibadahA->id],
    ])->assertSessionDoesntHaveErrors();

    expect($ibadahB->refresh()->order)->toBe(1);
    expect($ibadahA->refresh()->order)->toBe(2);
    expect($pelayananA->refresh()->order)->toBe(1);
});

test('deleting an item removes it', function () {
    $this->actingAs(User::factory()->create());
    $item = BajemBenowoItem::create(['section' => 'pelayanan', 'title' => 'Konseling', 'order' => 1]);

    $this->delete("/bajem-benowo/items/{$item->id}")->assertSessionDoesntHaveErrors();

    expect(BajemBenowoItem::find($item->id))->toBeNull();
});
