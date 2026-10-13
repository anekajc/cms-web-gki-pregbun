<?php

use App\Models\BidangPelayanan;
use App\Models\User;

function makeBidang(string $name, int $order = 1): BidangPelayanan
{
    return BidangPelayanan::create(['name' => $name, 'pic_name' => 'Budi', 'pic_phone' => '6281234567890', 'order' => $order]);
}

test('storing a bidang pelayanan appends it to the end of the list', function () {
    $this->actingAs(User::factory()->admin()->create());
    makeBidang('Musik', 1);

    $this->post('/bidang-pelayanan', ['name' => 'Multimedia', 'pic_name' => 'Sari', 'pic_phone' => '6281311112222'])
        ->assertSessionDoesntHaveErrors();

    $bidang = BidangPelayanan::where('name', 'Multimedia')->sole();
    expect($bidang->pic_name)->toBe('Sari');
    expect($bidang->pic_phone)->toBe('6281311112222');
    expect($bidang->order)->toBe(2);
});

test('the PIC phone must start with 628 and contain only digits', function (string $phone) {
    $this->actingAs(User::factory()->admin()->create());

    $this->post('/bidang-pelayanan', ['name' => 'Musik', 'pic_name' => 'Budi', 'pic_phone' => $phone])
        ->assertSessionHasErrors('pic_phone');

    expect(BidangPelayanan::count())->toBe(0);
})->with(['081234567890', '62abc', '62081234567890', '+6281234567890', '62812-3456-7890', '628123']);

test('all fields are required', function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->post('/bidang-pelayanan', [])->assertSessionHasErrors(['name', 'pic_name', 'pic_phone']);
});

test('updating a bidang pelayanan changes its fields', function () {
    $this->actingAs(User::factory()->admin()->create());
    $bidang = makeBidang('Musik');

    $this->put("/bidang-pelayanan/{$bidang->id}", ['name' => 'Musik Gereja', 'pic_name' => 'Andi', 'pic_phone' => '6285700001111'])
        ->assertSessionDoesntHaveErrors();

    $bidang->refresh();
    expect($bidang->name)->toBe('Musik Gereja');
    expect($bidang->pic_name)->toBe('Andi');
    expect($bidang->pic_phone)->toBe('6285700001111');
});

test('reordering assigns order from the given id sequence', function () {
    $this->actingAs(User::factory()->admin()->create());
    $a = makeBidang('A', 1);
    $b = makeBidang('B', 2);

    $this->put('/bidang-pelayanan/reorder', ['ids' => [$b->id, $a->id]])->assertSessionDoesntHaveErrors();

    expect($b->refresh()->order)->toBe(1);
    expect($a->refresh()->order)->toBe(2);
});

test('deleting a bidang pelayanan removes it', function () {
    $this->actingAs(User::factory()->admin()->create());
    $bidang = makeBidang('Musik');

    $this->delete("/bidang-pelayanan/{$bidang->id}")->assertSessionDoesntHaveErrors();

    expect(BidangPelayanan::find($bidang->id))->toBeNull();
});

test('only users granted bidangpelayanan can open or change the page', function () {
    $bidang = makeBidang('Musik');

    $outsider = User::factory()->create(['role' => 'user']);
    $outsider->permissions()->create(['permission' => 'komisi']);
    $this->actingAs($outsider);

    $this->get('/bidang-pelayanan')->assertRedirect(route('komisi'));
    $this->delete("/bidang-pelayanan/{$bidang->id}")->assertSessionHasErrors('access');
    expect(BidangPelayanan::find($bidang->id))->not->toBeNull();

    $member = User::factory()->create(['role' => 'user']);
    $member->permissions()->create(['permission' => 'bidangpelayanan']);
    $this->actingAs($member);

    $this->get('/bidang-pelayanan')->assertOk();
});
