<?php

use App\Models\Pelayanan;
use App\Models\PelayananDetail;
use App\Models\User;

test('storing a pelayanan generates a slug from the name', function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->post('/master/pelayanan', ['name' => 'Pelayanan Lansia'])->assertSessionDoesntHaveErrors();

    $pelayanan = Pelayanan::sole();
    expect($pelayanan->title)->toBe('Pelayanan Lansia');
    expect($pelayanan->slug)->toBe('pelayanan-lansia');
});

test('storing a pelayanan with a name that slugs the same makes the slug unique', function () {
    $this->actingAs(User::factory()->admin()->create());
    Pelayanan::create(['slug' => 'lansia', 'title' => 'Lansia', 'subtitle' => '', 'description' => '', 'order' => 1]);

    $this->post('/master/pelayanan', ['name' => 'Lansia'])->assertSessionDoesntHaveErrors();

    expect(Pelayanan::where('title', 'Lansia')->pluck('slug')->all())->toBe(['lansia', 'lansia-2']);
});

test('renaming a pelayanan updates the title but never the slug', function () {
    $this->actingAs(User::factory()->admin()->create());
    $pelayanan = Pelayanan::create(['slug' => 'poliklinik', 'title' => 'Poliklinik', 'subtitle' => '', 'description' => '', 'order' => 1]);

    $this->put("/master/pelayanan/{$pelayanan->id}", ['name' => 'Poliklinik Sehat'])->assertSessionDoesntHaveErrors();

    $pelayanan->refresh();
    expect($pelayanan->title)->toBe('Poliklinik Sehat');
    expect($pelayanan->slug)->toBe('poliklinik');
});

test('reordering assigns order from the given id sequence', function () {
    $this->actingAs(User::factory()->admin()->create());
    $a = Pelayanan::create(['slug' => 'a', 'title' => 'A', 'subtitle' => '', 'description' => '', 'order' => 1]);
    $b = Pelayanan::create(['slug' => 'b', 'title' => 'B', 'subtitle' => '', 'description' => '', 'order' => 2]);

    $this->put('/master/pelayanan/reorder', ['ids' => [$b->id, $a->id]])->assertSessionDoesntHaveErrors();

    expect($b->refresh()->order)->toBe(1);
    expect($a->refresh()->order)->toBe(2);
});

test('deleting a pelayanan cascades its detail cards', function () {
    $this->actingAs(User::factory()->admin()->create());
    $pelayanan = Pelayanan::create(['slug' => 'beasiswa', 'title' => 'Beasiswa', 'subtitle' => '', 'description' => '', 'order' => 1]);
    PelayananDetail::create(['pelayanan_id' => $pelayanan->id, 'label' => 'Syarat', 'value' => 'Aktif jemaat', 'order' => 1]);

    $this->delete("/master/pelayanan/{$pelayanan->id}")->assertSessionDoesntHaveErrors();

    expect(Pelayanan::find($pelayanan->id))->toBeNull();
    expect(PelayananDetail::count())->toBe(0);
});
