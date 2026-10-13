<?php

use App\Http\Controllers\BidangPelayananController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'access:bidangpelayanan'])->group(function () {
    Route::get('bidang-pelayanan', [BidangPelayananController::class, 'index'])->name('bidang-pelayanan');
    Route::post('bidang-pelayanan', [BidangPelayananController::class, 'store'])->name('bidang-pelayanan.store');
    Route::put('bidang-pelayanan/reorder', [BidangPelayananController::class, 'reorder'])->name('bidang-pelayanan.reorder');
    Route::put('bidang-pelayanan/{bidangPelayanan}', [BidangPelayananController::class, 'update'])->name('bidang-pelayanan.update');
    Route::delete('bidang-pelayanan/{bidangPelayanan}', [BidangPelayananController::class, 'destroy'])->name('bidang-pelayanan.destroy');
});
