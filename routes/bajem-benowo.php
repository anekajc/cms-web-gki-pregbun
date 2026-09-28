<?php

use App\Http\Controllers\BajemBenowoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    // Tabs map to bajem.tentang / .ibadah / .pelayanan / .lokasi; mutations are
    // checked in the controller since settings and items span several tabs.
    Route::get('bajem-benowo', [BajemBenowoController::class, 'index'])->middleware('access:bajem')->name('bajem-benowo');

    // Singleton settings: Tentang description/image, Pelayanan intro, Lokasi.
    Route::put('bajem-benowo/settings', [BajemBenowoController::class, 'updateSettings'])->name('bajem-benowo.settings.update');
    Route::post('bajem-benowo/settings/image/{slot}', [BajemBenowoController::class, 'updateSettingImage'])
        ->whereIn('slot', ['about', 'location'])->name('bajem-benowo.settings.image.update');
    Route::delete('bajem-benowo/settings/image/{slot}', [BajemBenowoController::class, 'destroySettingImage'])
        ->whereIn('slot', ['about', 'location'])->name('bajem-benowo.settings.image.destroy');

    // Jadwal Ibadah / Pelayanan items — freely add/edit/delete/reorder, split by `section`.
    Route::post('bajem-benowo/items', [BajemBenowoController::class, 'storeItem'])->name('bajem-benowo.items.store');
    Route::put('bajem-benowo/items/reorder', [BajemBenowoController::class, 'reorderItems'])->name('bajem-benowo.items.reorder');
    Route::put('bajem-benowo/items/{item}', [BajemBenowoController::class, 'updateItem'])->name('bajem-benowo.items.update');
    Route::post('bajem-benowo/items/{item}/image', [BajemBenowoController::class, 'updateItemImage'])->name('bajem-benowo.items.image.update');
    Route::delete('bajem-benowo/items/{item}/image', [BajemBenowoController::class, 'destroyItemImage'])->name('bajem-benowo.items.image.destroy');
    Route::delete('bajem-benowo/items/{item}', [BajemBenowoController::class, 'destroyItem'])->name('bajem-benowo.items.destroy');
});
