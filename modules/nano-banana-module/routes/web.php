<?php

use Illuminate\Support\Facades\Route;
use Modules\NanoBananaModule\Http\Controllers\NanoBananaController;
use Modules\NanoBananaModule\Http\Controllers\NanoBananaTemplateController;

/*
|--------------------------------------------------------------------------
| AI Image Template Module Routes
|--------------------------------------------------------------------------
*/

// User-facing routes (auth required)
Route::middleware(['web', 'auth'])->prefix('design')->name('design.')->group(function () {
    Route::get('/nanobanana/template/{id}', [NanoBananaController::class, 'useTemplate'])->name('nanobanana.useTemplate');
    Route::post('/nanobanana/generate', [NanoBananaController::class, 'generate'])->name('nanobanana.generate');
});

// Admin routes - settings must come before resource to avoid 'settings' being caught as id
Route::middleware(['web', 'auth', 'role_or_permission:admin|manage settings'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/nanobanana/settings', [NanoBananaTemplateController::class, 'settings'])->name('nanobanana.settings');
    Route::post('/nanobanana/settings', [NanoBananaTemplateController::class, 'updateSettings'])->name('nanobanana.settings.update');
    Route::prefix('nanobanana')->name('nanobanana.')->group(function () {
        Route::resource('templates', NanoBananaTemplateController::class)->except(['show']);
    });
});
