<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\GenreController;
use App\Http\Controllers\Api\PlatformController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\ReviewController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

Route::get('games', [GameController::class, 'index']);
Route::get('games/genre/{genre}', [GameController::class, 'byGenre']);
Route::get('games/platform/{platform}', [GameController::class, 'byPlatform']);
Route::get('games/{game}', [GameController::class, 'show']);
Route::get('genres', [GenreController::class, 'index']);
Route::get('platforms', [PlatformController::class, 'index']);
Route::get('post-categories', [CategoryController::class, 'index']);
Route::get('posts', [PostController::class, 'index']);

Route::get('reviews', [ReviewController::class, 'index']);
Route::get('reviews/{review}', [ReviewController::class, 'show']);

Route::get('users/search', function (\Illuminate\Http\Request $request) {
    $query = trim((string) $request->get('q', ''));

    if (strlen($query) < 1) {
        return response()->json([]);
    }

    $users = User::query()
        ->where('name', 'like', "%{$query}%")
        ->orWhere('username', 'like', "%{$query}%")
        ->limit(10)
        ->select('id', 'name', 'username')
        ->get();

    return response()->json($users);
});

Route::get('users/{username}', function (string $username) {
    $viewer = request()->user('sanctum');

    $user = User::query()
        ->where('username', $username)
        ->withCount(['followers', 'following'])
        ->firstOrFail();

    $user->load(['posts' => function ($query) {
        $query->with(['category', 'user', 'media', 'comments.user'])
            ->withCount('claps')
            ->latest()
            ->limit(20);
    }]);

    $favoritePostIds = $viewer
        ? $viewer->favoritePosts()->pluck('posts.id')->all()
        : [];
    $likedPostIds = $viewer
        ? \App\Models\Clap::query()->where('user_id', $viewer->id)->pluck('post_id')->all()
        : [];

    $posts = $user->posts->map(function ($post) use ($favoritePostIds, $likedPostIds) {
        return [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'content' => $post->content,
            'published_at' => optional($post->published_at)->toISOString(),
            'created_at' => optional($post->created_at)->toISOString(),
            'updated_at' => optional($post->updated_at)->toISOString(),
            'category' => $post->category ? [
                'id' => $post->category->id,
                'name' => $post->category->name,
            ] : null,
            'user' => $post->user ? [
                'id' => $post->user->id,
                'name' => $post->user->name,
                'username' => $post->user->username,
            ] : null,
            'image_url' => $post->imageUrl(),
            'likes_count' => (int) ($post->claps_count ?? 0),
            'is_favorited' => in_array($post->id, $favoritePostIds, true),
            'is_liked' => in_array($post->id, $likedPostIds, true),
            'comments' => $post->comments->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'created_at' => optional($comment->created_at)->toISOString(),
                    'user' => $comment->user ? [
                        'id' => $comment->user->id,
                        'name' => $comment->user->name,
                        'username' => $comment->user->username,
                    ] : null,
                ];
            })->values()->all(),
        ];
    })->values();

    $isFollowing = $viewer
        ? $viewer->following()->where('users.id', $user->id)->exists()
        : false;

    return response()->json([
        'id' => $user->id,
        'name' => $user->name,
        'username' => $user->username,
        'bio' => $user->bio,
        'image' => $user->image,
        'followers_count' => (int) ($user->followers_count ?? 0),
        'following_count' => (int) ($user->following_count ?? 0),
        'is_following' => $isFollowing,
        'posts' => $posts,
    ]);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::put('auth/profile', [AuthController::class, 'updateProfile']);

    Route::get('favorites', [FavoriteController::class, 'index']);
    Route::get('favorites/posts', [FavoriteController::class, 'postIndex']);
    Route::post('favorites/posts/{post}', [FavoriteController::class, 'storePost']);
    Route::delete('favorites/posts/{post}', [FavoriteController::class, 'destroyPost']);

    Route::post('favorites/games/{game}', [FavoriteController::class, 'store']);
    Route::delete('favorites/games/{game}', [FavoriteController::class, 'destroy']);
    Route::post('favorites/{game}', [FavoriteController::class, 'store']);
    Route::delete('favorites/{game}', [FavoriteController::class, 'destroy']);

    Route::post('reviews', [ReviewController::class, 'store']);
    Route::put('reviews/{review}', [ReviewController::class, 'update']);
    Route::delete('reviews/{review}', [ReviewController::class, 'destroy']);

    Route::get('posts/mine', [PostController::class, 'myPosts']);
    Route::post('posts', [PostController::class, 'store']);
    Route::put('posts/{post}', [PostController::class, 'update']);
    Route::delete('posts/{post}', [PostController::class, 'destroy']);
    Route::post('posts/{post}/like', [PostController::class, 'toggleLike']);
    Route::post('posts/{post}/comments', [PostController::class, 'storeComment']);
    Route::delete('posts/{post}/comments/{comment}', [PostController::class, 'destroyComment']);

    Route::post('users/{user}/follow', function (User $user, \Illuminate\Http\Request $request) {
        $viewer = $request->user();

        if ((int) $viewer->id === (int) $user->id) {
            return response()->json(['message' => 'You cannot follow yourself.'], 422);
        }

        $alreadyFollowing = $viewer->following()->where('users.id', $user->id)->exists();

        if ($alreadyFollowing) {
            $viewer->following()->detach($user->id);
            $isFollowing = false;
        } else {
            $viewer->following()->syncWithoutDetaching([$user->id]);
            $isFollowing = true;
        }

        $user->loadCount(['followers', 'following']);

        return response()->json([
            'message' => $isFollowing ? 'Followed user.' : 'Unfollowed user.',
            'is_following' => $isFollowing,
            'followers_count' => (int) ($user->followers_count ?? 0),
            'following_count' => (int) ($user->following_count ?? 0),
        ]);
    });
});
