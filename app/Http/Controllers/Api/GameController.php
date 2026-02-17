<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Game::query()->with(['genres', 'platforms']);

        if ($search = $request->query('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($genre = $request->query('genre')) {
            $query->whereHas('genres', function ($q) use ($genre) {
                $q->where('id', $genre)
                    ->orWhere('slug', $genre)
                    ->orWhere('rawg_id', $genre);
            });
        }

        if ($platform = $request->query('platform')) {
            $query->whereHas('platforms', function ($q) use ($platform) {
                $q->where('id', $platform)
                    ->orWhere('slug', $platform)
                    ->orWhere('rawg_id', $platform);
            });
        }

        $perPage = (int) $request->query('per_page', 20);
        $perPage = max(1, min($perPage, 50));

        return response()->json($query->paginate($perPage));
    }

    public function show(Game $game): JsonResponse
    {
        $game->load(['detail', 'genres', 'platforms', 'reviews.user']);

        return response()->json($game);
    }

    public function byGenre(string $genre): JsonResponse
    {
        $request = request();
        $request->merge(['genre' => $genre]);

        return $this->index($request);
    }

    public function byPlatform(string $platform): JsonResponse
    {
        $request = request();
        $request->merge(['platform' => $platform]);

        return $this->index($request);
    }
}
