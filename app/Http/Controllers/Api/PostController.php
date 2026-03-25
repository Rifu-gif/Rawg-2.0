<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Clap;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var User|null $viewer */
        $viewer = $request->user('sanctum');
        $favoritePostIds = $viewer
            ? $viewer->favoritePosts()->pluck('posts.id')->all()
            : [];
        $likedPostIds = $viewer
            ? Clap::query()->where('user_id', $viewer->id)->pluck('post_id')->all()
            : [];

        $posts = Post::query()
            ->with(['category', 'user', 'media', 'comments.user'])
            ->withCount('claps')
            ->latest()
            ->paginate(20);

        $posts->getCollection()->transform(function (Post $post) use ($favoritePostIds, $likedPostIds) {
            return $this->serializePost(
                $post,
                in_array($post->id, $favoritePostIds, true),
                in_array($post->id, $likedPostIds, true)
            );
        });

        return response()->json($posts);
    }

    public function myPosts(Request $request): JsonResponse
    {
        $favoritePostIds = $request->user()->favoritePosts()->pluck('posts.id')->all();
        $likedPostIds = Clap::query()->where('user_id', $request->user()->id)->pluck('post_id')->all();

        $posts = Post::query()
            ->with(['category', 'user', 'media', 'comments.user'])
            ->withCount('claps')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        $posts->getCollection()->transform(function (Post $post) use ($favoritePostIds, $likedPostIds) {
            return $this->serializePost(
                $post,
                in_array($post->id, $favoritePostIds, true),
                in_array($post->id, $likedPostIds, true)
            );
        });

        return response()->json($posts);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:4096'],
        ]);

        $post = Post::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'category_id' => (int) $validated['category_id'],
            'user_id' => $request->user()->id,
            'published_at' => now(),
        ]);

        if ($request->hasFile('image')) {
            $post->addMediaFromRequest('image')->toMediaCollection();
        }

        $post->load(['category', 'user', 'media', 'comments.user']);
        $post->loadCount('claps');

        $isFavorited = $request->user()->favoritePosts()->where('posts.id', $post->id)->exists();
        $isLiked = Clap::query()
            ->where('user_id', $request->user()->id)
            ->where('post_id', $post->id)
            ->exists();

        return response()->json($this->serializePost($post, $isFavorited, $isLiked), 201);
    }

    public function update(Request $request, Post $post): JsonResponse
    {
        if ((int) $post->user_id !== (int) $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:4096'],
        ]);

        $post->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'category_id' => (int) $validated['category_id'],
        ]);

        if ($request->hasFile('image')) {
            $post->addMediaFromRequest('image')->toMediaCollection();
        }

        $post->load(['category', 'user', 'media', 'comments.user']);
        $post->loadCount('claps');

        $isFavorited = $request->user()->favoritePosts()->where('posts.id', $post->id)->exists();
        $isLiked = Clap::query()
            ->where('user_id', $request->user()->id)
            ->where('post_id', $post->id)
            ->exists();

        return response()->json($this->serializePost($post, $isFavorited, $isLiked));
    }

    public function destroy(Request $request, Post $post): JsonResponse
    {
        if ((int) $post->user_id !== (int) $request->user()->id) {
            abort(403);
        }

        $post->delete();

        return response()->json(['message' => 'Post deleted']);
    }

    public function toggleLike(Request $request, Post $post): JsonResponse
    {
        $existing = Clap::query()
            ->where('user_id', $request->user()->id)
            ->where('post_id', $post->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $isLiked = false;
        } else {
            Clap::query()->create([
                'user_id' => $request->user()->id,
                'post_id' => $post->id,
            ]);
            $isLiked = true;
        }

        $likesCount = Clap::query()->where('post_id', $post->id)->count();

        return response()->json([
            'message' => $isLiked ? 'Post liked' : 'Post unliked',
            'is_liked' => $isLiked,
            'likes_count' => $likesCount,
        ]);
    }

    public function storeComment(Request $request, Post $post): JsonResponse
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'max:1000'],
        ]);

        $comment = $post->comments()->create([
            'user_id' => $request->user()->id,
            'content' => $validated['content'],
        ]);

        $comment->load('user');

        return response()->json([
            'id' => $comment->id,
            'content' => $comment->content,
            'created_at' => optional($comment->created_at)->toISOString(),
            'user' => [
                'id' => $comment->user->id,
                'name' => $comment->user->name,
                'username' => $comment->user->username,
            ],
        ], 201);
    }

    public function destroyComment(Request $request, Post $post, Comment $comment): JsonResponse
    {
        if ((int) $comment->post_id !== (int) $post->id) {
            abort(404);
        }

        $canDelete = (int) $comment->user_id === (int) $request->user()->id
            || (int) $post->user_id === (int) $request->user()->id;

        if (!$canDelete) {
            abort(403);
        }

        $comment->delete();

        return response()->json(['message' => 'Comment deleted']);
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
                'image' => $post->user->image,
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
