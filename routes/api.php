<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\ReviewController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

Route::get('games', [GameController::class, 'index']);
Route::get('games/genre/{genre}', [GameController::class, 'byGenre']);
Route::get('games/platform/{platform}', [GameController::class, 'byPlatform']);
Route::get('games/{game}', [GameController::class, 'show']);

Route::get('reviews', [ReviewController::class, 'index']);
Route::get('reviews/{review}', [ReviewController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me', [AuthController::class, 'me']);

    Route::get('favorites', [FavoriteController::class, 'index']);
    Route::post('favorites/{game}', [FavoriteController::class, 'store']);
    Route::delete('favorites/{game}', [FavoriteController::class, 'destroy']);

    Route::post('reviews', [ReviewController::class, 'store']);
    Route::put('reviews/{review}', [ReviewController::class, 'update']);
    Route::delete('reviews/{review}', [ReviewController::class, 'destroy']);
});
