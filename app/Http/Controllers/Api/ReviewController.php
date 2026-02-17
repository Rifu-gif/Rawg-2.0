<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Review::query()->with(['user', 'game']);

        if ($gameId = $request->query('game_id')) {
            $query->where('game_id', $gameId);
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function show(Review $review): JsonResponse
    {
        $review->load(['user', 'game']);

        return response()->json($review);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'game_id' => ['required', 'exists:games,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:10'],
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
        ]);

        $review = Review::create([
            'game_id' => $validated['game_id'],
            'user_id' => $request->user()->id,
            'rating' => $validated['rating'],
            'title' => $validated['title'] ?? null,
            'body' => $validated['body'] ?? null,
        ]);

        return response()->json($review, 201);
    }

    public function update(Request $request, Review $review): JsonResponse
    {
        if ($review->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'rating' => ['sometimes', 'integer', 'min:1', 'max:10'],
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
        ]);

        $review->update($validated);

        return response()->json($review);
    }

    public function destroy(Request $request, Review $review): JsonResponse
    {
        if ($review->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $review->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
