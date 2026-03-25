<?php

use Illuminate\Support\Facades\Route;
use Modules\PayHereModule\Http\Controllers\PayHereController;

Route::middleware(['web', 'auth'])->prefix('payhere')->name('payhere.')->group(function () {
    Route::post('/initiate', [PayHereController::class, 'initiate'])->name('initiate');
    Route::any('/return', [PayHereController::class, 'return'])->name('return');
    Route::any('/cancel', [PayHereController::class, 'cancel'])->name('cancel');
});

Route::middleware(['web'])->prefix('payhere')->name('payhere.')->group(function () {
    Route::any('/notify', [PayHereController::class, 'notify'])->name('notify');
});
