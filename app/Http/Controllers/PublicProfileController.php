<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class PublicProfileController extends Controller
{
    public function show(User $user)
    {
        $posts = $user->posts()
            ->where('published_at', '<=', now())
            ->withCount('claps')
            ->with(['user', 'media', 'category'])
            ->latest()
            ->paginate(10);

        return view('profile.show', [
            'user' => $user,
            'posts' => $posts,
        ]);
    }
}