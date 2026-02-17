<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\GameDetail;
use App\Models\Genre;
use App\Models\Platform;
use App\Services\RawgService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GameSeeder extends Seeder
{
    private const PAGE_SIZE = 20;
    private const PAGES = 2;

    public function run(): void
    {
        $service = app(RawgService::class);

        for ($page = 1; $page <= self::PAGES; $page++) {
            $response = $service->getGames([
                'page' => $page,
                'page_size' => self::PAGE_SIZE,
                'ordering' => '-rating',
            ]);

            if (!is_array($response) || !isset($response['results'])) {
                continue;
            }

            foreach ($response['results'] as $item) {
                if (!isset($item['id'], $item['name'])) {
                    continue;
                }

                $game = Game::updateOrCreate(
                    ['rawg_id' => $item['id']],
                    [
                        'name' => $item['name'],
                        'slug' => $item['slug'] ?? Str::slug($item['name']),
                        'released_at' => $item['released'] ?? null,
                        'rating' => $item['rating'] ?? null,
                        'background_image' => $item['background_image'] ?? null,
                        'description' => $item['short_description'] ?? null,
                        'metacritic' => $item['metacritic'] ?? null,
                        'website' => $item['website'] ?? null,
                    ]
                );

                $detail = $service->getGame($item['id']);
                if (is_array($detail)) {
                    GameDetail::updateOrCreate(
                        ['game_id' => $game->id],
                        [
                            'description_raw' => $detail['description_raw'] ?? null,
                            'esrb_rating' => $detail['esrb_rating']['name'] ?? null,
                            'tba' => $detail['tba'] ?? false,
                        ]
                    );

                    $game->update([
                        'description' => $detail['description_raw'] ?? $detail['description'] ?? $game->description,
                        'website' => $detail['website'] ?? $game->website,
                    ]);
                }

                $genreIds = [];
                foreach ($item['genres'] ?? [] as $genre) {
                    if (!isset($genre['id'], $genre['name'], $genre['slug'])) {
                        continue;
                    }

                    $g = Genre::firstOrCreate(
                        ['rawg_id' => $genre['id']],
                        ['name' => $genre['name'], 'slug' => $genre['slug']]
                    );
                    $genreIds[] = $g->id;
                }
                if (!empty($genreIds)) {
                    $game->genres()->syncWithoutDetaching($genreIds);
                }

                $platformIds = [];
                foreach ($item['platforms'] ?? [] as $platformInfo) {
                    $platform = $platformInfo['platform'] ?? null;
                    if (!is_array($platform) || !isset($platform['id'], $platform['name'], $platform['slug'])) {
                        continue;
                    }

                    $p = Platform::firstOrCreate(
                        ['rawg_id' => $platform['id']],
                        ['name' => $platform['name'], 'slug' => $platform['slug']]
                    );
                    $platformIds[] = $p->id;
                }
                if (!empty($platformIds)) {
                    $game->platforms()->syncWithoutDetaching($platformIds);
                }
            }
        }
    }
}
