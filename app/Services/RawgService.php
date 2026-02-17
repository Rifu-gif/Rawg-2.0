<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Pagination\Paginator;

class RawgService
{
    protected string $apiKey;
    protected string $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.rawg.key');
        $this->apiUrl = config('services.rawg.url');
    }

    /**
     * Fetch games from RAWG API
     */
    public function getGames(array $filters = [])
    {
        $params = [
            'key' => $this->apiKey,
            'page_size' => $filters['page_size'] ?? 20,
            'page' => $filters['page'] ?? 1,
        ];

        if (!empty($filters['search'])) {
            $params['search'] = $filters['search'];
        }

        if (!empty($filters['genre'])) {
            $params['genres'] = $filters['genre'];
        }

        if (!empty($filters['platform'])) {
            $params['platforms'] = $filters['platform'];
        }

        if (!empty($filters['ordering'])) {
            $params['ordering'] = $filters['ordering'];
        }

        try {
            $response = Http::get("{$this->apiUrl}/games", $params);
            
            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            \Log::error('RAWG API Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get a single game by ID
     */
    public function getGame(int $id)
    {
        try {
            $response = Http::get("{$this->apiUrl}/games/{$id}", [
                'key' => $this->apiKey,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            \Log::error('RAWG API Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get game screenshots
     */
    public function getGameScreenshots(int $gameId)
    {
        try {
            $response = Http::get("{$this->apiUrl}/games/{$gameId}/screenshots", [
                'key' => $this->apiKey,
            ]);

            if ($response->successful()) {
                return $response->json()['results'] ?? [];
            }

            return [];
        } catch (\Exception $e) {
            \Log::error('RAWG API Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get genres
     */
    public function getGenres()
    {
        try {
            $response = Http::get("{$this->apiUrl}/genres", [
                'key' => $this->apiKey,
                'page_size' => 50,
            ]);

            if ($response->successful()) {
                return $response->json()['results'] ?? [];
            }

            return [];
        } catch (\Exception $e) {
            \Log::error('RAWG API Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get platforms
     */
    public function getPlatforms()
    {
        try {
            $response = Http::get("{$this->apiUrl}/platforms", [
                'key' => $this->apiKey,
                'page_size' => 50,
            ]);

            if ($response->successful()) {
                return $response->json()['results'] ?? [];
            }

            return [];
        } catch (\Exception $e) {
            \Log::error('RAWG API Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Search games by query
     */
    public function searchGames(string $query, int $page = 1)
    {
        return $this->getGames([
            'search' => $query,
            'page' => $page,
            'page_size' => 20,
        ]);
    }
}
