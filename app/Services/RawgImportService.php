<?php

namespace App\Services;

use App\Models\Game;
use App\Models\GameDetail;
use App\Models\Genre;
use App\Models\Platform;
use Illuminate\Support\Str;

class RawgImportService
{
    public function __construct(private readonly RawgService $rawgService)
    {
    }

    public function importGames(
        int $pages = 1,
        int $pageSize = 20,
        int $startPage = 1,
        string $ordering = '-rating',
        ?callable $progress = null
    ): array {
        $pages = max(1, $pages);
        $pageSize = max(1, min($pageSize, 40));
        $startPage = max(1, $startPage);

        $processed = 0;
        $created = 0;
        $updated = 0;
        $skipped = 0;

        for ($page = $startPage; $page < $startPage + $pages; $page++) {
            $response = $this->rawgService->getGames([
                'page' => $page,
                'page_size' => $pageSize,
                'ordering' => $ordering,
            ]);

            if (!is_array($response) || !isset($response['results']) || !is_array($response['results'])) {
                $progress && $progress("Skipping RAWG page {$page}: no valid results returned.");
                continue;
            }

            $progress && $progress("Importing RAWG page {$page} with " . count($response['results']) . ' games.');

            foreach ($response['results'] as $item) {
                if (!isset($item['id'], $item['name'])) {
                    $skipped++;
                    continue;
                }

                $existing = Game::query()->where('rawg_id', $item['id'])->first();

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

                $existing ? $updated++ : $created++;
                $processed++;

                $detail = $this->rawgService->getGame($item['id']);
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

                    $record = Genre::firstOrCreate(
                        ['rawg_id' => $genre['id']],
                        ['name' => $genre['name'], 'slug' => $genre['slug']]
                    );

                    $genreIds[] = $record->id;
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

                    $record = Platform::firstOrCreate(
                        ['rawg_id' => $platform['id']],
                        ['name' => $platform['name'], 'slug' => $platform['slug']]
                    );

                    $platformIds[] = $record->id;
                }

                if (!empty($platformIds)) {
                    $game->platforms()->syncWithoutDetaching($platformIds);
                }
            }
        }

        return [
            'processed' => $processed,
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
        ];
    }
}
