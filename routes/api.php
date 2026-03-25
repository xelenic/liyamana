<?php

use App\Http\Controllers\Api\AddressBookController;
use App\Http\Controllers\Api\AuthController as ApiAuthController;
use App\Http\Controllers\Api\CreditsController;
use App\Http\Controllers\Api\OrdersController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Base URL: /api. Use Accept: application/json. For protected routes send
| Authorization: Bearer {token} (from POST /api/login or /api/register).
|
*/

// Public: Auth
Route::post('/login', [ApiAuthController::class, 'login']);
Route::post('/register', [ApiAuthController::class, 'register']);

// Protected: require Authorization: Bearer {token} or session (stateful domain)
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [ApiAuthController::class, 'logout']);
    Route::get('/me', [ApiAuthController::class, 'me']);

    // Profile
    Route::get('/user/profile', [ProfileController::class, 'show']);
    Route::put('/user/profile', [ProfileController::class, 'update']);
    Route::put('/user/password', [ProfileController::class, 'updatePassword']);

    // Address Book
    Route::get('/user/address-book', [AddressBookController::class, 'index']);
    Route::post('/user/address-book', [AddressBookController::class, 'store']);
    Route::put('/user/address-book/{id}', [AddressBookController::class, 'update']);
    Route::delete('/user/address-book/{id}', [AddressBookController::class, 'destroy']);

    // Orders
    Route::get('/orders', [OrdersController::class, 'index']);
    Route::get('/orders/{id}', [OrdersController::class, 'show']);

    // Credits
    Route::get('/credits', [CreditsController::class, 'index']);
    Route::get('/credits/transactions', [CreditsController::class, 'transactions']);

    // Notifications
    Route::get('/user/notifications', [NotificationController::class, 'index']);
    Route::post('/user/notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::post('/user/notifications/{id}/read', [NotificationController::class, 'markRead']);
});
