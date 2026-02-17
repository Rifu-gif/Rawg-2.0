<?php

namespace Database\Seeders;

use App\Services\RawgService;
use App\Models\Genre;
use Illuminate\Database\Seeder;

class GenreSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(RawgService::class);
        $genres = $service->getGenres();

        if (!is_array($genres)) {
            return;
        }

        foreach ($genres as $genre) {
            if (!isset($genre['id'], $genre['name'], $genre['slug'])) {
                continue;
            }

            Genre::updateOrCreate(
                ['rawg_id' => $genre['id']],
                ['name' => $genre['name'], 'slug' => $genre['slug']]
            );
        }
    }
}
