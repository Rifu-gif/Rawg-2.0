<?php

use App\Http\Controllers\ClapController;
use App\Http\Controllers\FollowerController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicProfileController;
use App\Http\Controllers\GameController;
use Illuminate\Support\Facades\Route;

// Landing page for guests, redirect to dashboard for logged-in users
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
});

Route::get('/@{user:username}', [PublicProfileController::class, 'show'])
    ->name('profile.show');

// Dashboard - PROTECTED - only for authenticated users
Route::get('/dashboard', [PostController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/@{username}/{post:slug}', [PostController::class, 'show'])
    ->name('post.show');

Route::get('/category/{category}', [PostController::class, 'category'])
    ->name('post.byCategory');

Route::middleware(['auth', 'verified'])->group(function() {

    Route::get('/post/create', [PostController::class, 'create'])
        ->name('post.create');

    Route::post('/post/create', [PostController::class, 'store'])
        ->name('post.store');

    Route::get('/post/{post:slug}', [PostController::class, 'edit'])
        ->name('post.edit');

    Route::put('/post/{post}', [PostController::class, 'update'])
        ->name('post.update');

    Route::delete('/post/{post}', [PostController::class, 'destroy'])
        ->name('post.destroy');
        
    Route::get('/my-posts', [PostController::class, 'myPosts'])
        ->name('myPosts');

    Route::post('/follow/{user}', [FollowerController::class, 'followUnfollow'])
        ->name('follow');

    Route::post('/clap/{post}', [ClapController::class, 'clap'])
        ->name('clap');

    Route::post('/post/{post:slug}/comments', [PostController::class, 'storeComment'])
        ->name('post.comments.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Game Favorites Routes
    Route::post('/games/{id}/toggle-favorite', [GameController::class, 'toggleFavorite'])->name('games.toggle-favorite');
    Route::get('/favorites', [GameController::class, 'favorites'])->name('games.favorites');
});

// Game Routes
Route::get('/games', [GameController::class, 'index'])->name('games.index');
Route::get('/games/search', [GameController::class, 'search'])->name('games.search');
Route::get('/games/{id}', [GameController::class, 'show'])->name('games.show');

// API Routes
Route::get('/api/users/search', function (\Illuminate\Http\Request $request) {
    $query = $request->get('q', '');
    
    if (strlen($query) < 1) {
        return response()->json([]);
    }
    
    $users = \App\Models\User::where('name', 'like', "%{$query}%")
        ->orWhere('username', 'like', "%{$query}%")
        ->limit(10)
        ->select('id', 'name', 'username')
        ->get();
    
    return response()->json($users);
});

require __DIR__ . '/auth.php';
