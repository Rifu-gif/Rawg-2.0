<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameFavorite;
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
}
