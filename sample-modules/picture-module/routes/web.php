<?php

use Illuminate\Support\Facades\Route;

Route::prefix('picture')->name('picture.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/gallery', function () {
        return view('picture-module::gallery');
    })->name('gallery.index');
});
