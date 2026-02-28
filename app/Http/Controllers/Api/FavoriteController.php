<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Clap;
use App\Models\Comment;
use App\Models\Game;
use App\Models\GameFavorite;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $favorites = $request->user()->favoriteGames()->with(['genres', 'platforms'])->paginate(20);

        return response()->json($favorites);
    }

    public function store(Request $request, Game $game): JsonResponse
    {
        GameFavorite::firstOrCreate([
            'user_id' => $request->user()->id,
            'game_id' => $game->id,
        ]);

        return response()->json(['message' => 'Added to favorites'], 201);
    }

    public function destroy(Request $request, Game $game): JsonResponse
    {
        GameFavorite::where('user_id', $request->user()->id)
            ->where('game_id', $game->id)
            ->delete();

        return response()->json(['message' => 'Removed from favorites']);
    }

    public function postIndex(Request $request): JsonResponse
    {
        $likedPostIds = Clap::query()
            ->where('user_id', $request->user()->id)
            ->pluck('post_id')
            ->all();

        $posts = $request->user()
            ->favoritePosts()
            ->with(['category', 'user', 'media', 'comments.user'])
            ->withCount('claps')
            ->latest('post_favorites.created_at')
            ->paginate(20);

        $posts->getCollection()->transform(function (Post $post) use ($likedPostIds) {
            return $this->serializePost($post, true, in_array($post->id, $likedPostIds, true));
        });

        return response()->json($posts);
    }

    public function storePost(Request $request, Post $post): JsonResponse
    {
        $request->user()->favoritePosts()->syncWithoutDetaching([$post->id]);

        return response()->json(['message' => 'Post added to favorites'], 201);
    }

    public function destroyPost(Request $request, Post $post): JsonResponse
    {
        $request->user()->favoritePosts()->detach($post->id);

        return response()->json(['message' => 'Post removed from favorites']);
    }

    private function serializePost(Post $post, bool $isFavorited, bool $isLiked): array
    {
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
            'is_favorited' => $isFavorited,
            'is_liked' => $isLiked,
            'likes_count' => (int) ($post->claps_count ?? 0),
            'comments' => $post->comments->map(function (Comment $comment) {
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
    }
}
