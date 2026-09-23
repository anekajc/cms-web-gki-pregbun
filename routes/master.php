<?php

use App\Http\Controllers\MasterPelayananController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('master')->name('master.')->group(function () {
    // Master data: the source list a feature page's tabs/options are built
    // from. Pelayanan is the first — add/rename/delete/reorder the fixed set
    // of ministries here; the Pelayanan page only edits each one's content.
    Route::get('pelayanan', [MasterPelayananController::class, 'index'])->name('pelayanan');
    Route::post('pelayanan', [MasterPelayananController::class, 'store'])->name('pelayanan.store');
    Route::put('pelayanan/reorder', [MasterPelayananController::class, 'reorder'])->name('pelayanan.reorder');
    Route::put('pelayanan/{pelayanan}', [MasterPelayananController::class, 'update'])->name('pelayanan.update');
    Route::delete('pelayanan/{pelayanan}', [MasterPelayananController::class, 'destroy'])->name('pelayanan.destroy');
});
