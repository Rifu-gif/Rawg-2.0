<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostCreateRequest;
use App\Http\Requests\PostUpdateRequest;
use App\Models\Post;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PostController extends Controller
{

    public function index()
    {
        $user = auth()->user();

        $posts = Post::with(['user', 'media'])
            ->where('published_at', '<=', now())
            ->withCount('claps')
            ->latest()
            ->paginate(6);
        
        $categories = Category::query()
            ->selectRaw('MIN(id) as id, name')
            ->groupBy('name')
            ->get();
        
        return view('post.index', [
            'posts' => $posts,
            'categories' => $categories,
        ]);
    }


    public function create()
    {
        $categories = Category::query()
            ->selectRaw('MIN(id) as id, name')
            ->groupBy('name')
            ->get();
        
        return view('post.create', [
            'categories' => $categories,
        ]);
    }


    public function store(PostCreateRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();
        $data['published_at'] = now(); 

        $post = Post::create($data);

        
        if ($request->hasFile('image')) {
            $post->addMediaFromRequest('image')
                ->toMediaCollection();
        }

        return redirect()->route('dashboard')->with('success', 'Post created successfully!');
    }


    public function show(string $username, Post $post)
    {
        if ($post->user->username !== $username) {
            abort(404);
        }

        $post->load([
            'user.media',
            'category',
            'media',
            'comments' => fn ($query) => $query->latest()->with('user.media'),
        ]);

        return view('post.show', [
            'post' => $post,
        ]);
    }


    public function storeComment(Request $request, Post $post)
    {
        $data = $request->validate([
            'content' => ['required', 'string', 'max:1000'],
        ]);

        $post->comments()->create([
            'user_id' => Auth::id(),
            'content' => $data['content'],
        ]);

        return back()->with('success', 'Comment posted successfully.');
    }


    public function edit(Post $post)
    {
        if ($post->user_id !== Auth::id()) {
            abort(403);
        }
        $categories = Category::query()
            ->selectRaw('MIN(id) as id, name')
            ->groupBy('name')
            ->get();
        return view('post.edit', [
            'post' => $post,
            'categories' => $categories,
        ]);
    }

    
    public function update(PostUpdateRequest $request, Post $post)
    {
        if ($post->user_id !== Auth::id()) {
            abort(403);
        }
        $data = $request->validated();

        $post->update($data);

        if ($data['image'] ?? false) {
            $post->addMediaFromRequest('image')
                ->toMediaCollection();
        }

        return redirect()->route('myPosts');
    }

   
    public function destroy(Post $post)
    {
        if ($post->user_id !== Auth::id()) {
            abort(403);
        }
        $post->delete();

        return redirect()->route('dashboard');
    }

    public function category(Category $category)
    {
        $user = auth()->user();

        $query = $category->posts()
            ->where('published_at', '<=', now())
            ->with(['user', 'media'])
            ->withCount('claps')
            ->latest();
        
        $posts = $query->paginate(8);
        $categories = Category::get();

        return view('post.index', [
            'posts' => $posts,
            'categories' => $categories,
            'selectedCategory' => $category,
        ]);
    }

    public function myPosts()
    {
        $user = auth()->user();
        $posts = $user->posts()
            ->with(['user', 'media'])
            ->withCount('claps')
            ->latest()
            ->paginate(8);
        $categories = Category::get();

        return view('post.index', [
            'posts' => $posts,
            'categories' => $categories,
        ]);
    }
}
