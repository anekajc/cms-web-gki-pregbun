<?php

use App\Http\Controllers\PembangunanController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('pembangunan', [PembangunanController::class, 'index'])->middleware('access:pembangunan')->name('pembangunan');

    // Dana Pembangunan tab: financial snapshots
    Route::middleware('access:pembangunan.dana')->group(function () {
        Route::post('pembangunan', [PembangunanController::class, 'store'])->name('pembangunan.store');
        Route::delete('pembangunan/{pembangunanUpdate}', [PembangunanController::class, 'destroy'])->name('pembangunan.destroy');
    });

    // Update tab: YouTube video history + image gallery
    Route::middleware('access:pembangunan.update')->group(function () {
        Route::post('pembangunan/video', [PembangunanController::class, 'storeVideo'])->name('pembangunan.video.store');
        Route::delete('pembangunan/video/{video}', [PembangunanController::class, 'destroyVideo'])->name('pembangunan.video.destroy');
        Route::post('pembangunan/image', [PembangunanController::class, 'storeImage'])->name('pembangunan.image.store');
        Route::put('pembangunan/image/reorder', [PembangunanController::class, 'reorderImages'])->name('pembangunan.images.reorder');
        Route::delete('pembangunan/image/{image}', [PembangunanController::class, 'destroyImage'])->name('pembangunan.image.destroy');
    });
});
