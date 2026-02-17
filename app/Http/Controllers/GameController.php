<?php

namespace App\Http\Controllers;

use App\Services\RawgService;
use App\Models\Favorite;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GameController extends Controller
{
    protected RawgService $rawgService;

    public function __construct(RawgService $rawgService)
    {
        $this->rawgService = $rawgService;
    }

    
    public function index(Request $request): View
    {
        $filters = [
            'page' => $request->get('page', 1),
            'page_size' => $request->get('page_size', 20),
            'search' => $request->get('search'),
            'genre' => $request->get('genre'),
            'platform' => $request->get('platform'),
            'ordering' => $request->get('ordering', '-rating'),
        ];

        $games = $this->rawgService->getGames($filters);
        $genres = $this->rawgService->getGenres();
        $platforms = $this->rawgService->getPlatforms();

        
        $favoriteGameIds = auth()->check() 
            ? auth()->user()->favorites()->pluck('game_id')->toArray()
            : [];

        return view('games.index', [
            'games' => $games['results'] ?? [],
            'total' => $games['count'] ?? 0,
            'nextPage' => $games['next'] ?? null,
            'genres' => $genres,
            'platforms' => $platforms,
            'currentPage' => $filters['page'],
            'favoriteGameIds' => $favoriteGameIds,
        ]);
    }


    public function show(int $id): View
    {
        $game = $this->rawgService->getGame($id);
        $screenshots = $this->rawgService->getGameScreenshots($id);

        if (!$game) {
            abort(404);
        }

        
        $isFavorited = auth()->check()
            ? auth()->user()->favorites()->where('game_id', $id)->exists()
            : false;

        return view('games.show', [
            'game' => $game,
            'screenshots' => $screenshots,
            'isFavorited' => $isFavorited,
        ]);
    }

  
    public function search(Request $request): View
    {
        $query = $request->get('q');
        $page = $request->get('page', 1);

        $results = $this->rawgService->searchGames($query, $page);

        
        $favoriteGameIds = auth()->check()
            ? auth()->user()->favorites()->pluck('game_id')->toArray()
            : [];

        return view('games.search', [
            'games' => $results['results'] ?? [],
            'query' => $query,
            'total' => $results['count'] ?? 0,
            'currentPage' => $page,
            'favoriteGameIds' => $favoriteGameIds,
        ]);
    }

    
    public function toggleFavorite(Request $request, int $gameId): JsonResponse
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = auth()->user();
        $favorite = $user->favorites()->where('game_id', $gameId)->first();

        if ($favorite) {
            $favorite->delete();
            return response()->json(['favorited' => false, 'message' => 'Removed from favorites']);
        } else {
            $game = $this->rawgService->getGame($gameId);

            if (!$game) {
                return response()->json(['error' => 'Game not found'], 404);
            }

            
            $user->favorites()->create([
                'game_id' => $gameId,
                'game_name' => $game['name'] ?? '',
                'game_image' => $game['background_image'] ?? null,
                'game_rating' => $game['rating'] ?? null,
                'game_description' => $game['description'] ?? null,
            ]);

            return response()->json(['favorited' => true, 'message' => 'Added to favorites']);
        }
    }

    
    public function favorites(Request $request): View
    {
        if (!auth()->check()) {
            abort(401);
        }

        $favorites = auth()->user()->favorites()
            ->latest('created_at')
            ->paginate(20);

        return view('games.favorites', [
            'favorites' => $favorites,
        ]);
    }
}
