<?php

namespace Database\Seeders;

use App\Services\RawgService;
use App\Models\Platform;
use Illuminate\Database\Seeder;

class PlatformSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(RawgService::class);
        $platforms = $service->getPlatforms();

        if (!is_array($platforms)) {
            return;
        }

        foreach ($platforms as $platform) {
            if (!isset($platform['id'], $platform['name'], $platform['slug'])) {
                continue;
            }

            Platform::updateOrCreate(
                ['rawg_id' => $platform['id']],
                ['name' => $platform['name'], 'slug' => $platform['slug']]
            );
        }
    }
}
