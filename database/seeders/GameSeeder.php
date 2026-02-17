<?php

namespace Database\Seeders;

use App\Services\RawgImportService;
use Illuminate\Database\Seeder;

class GameSeeder extends Seeder
{
    private const PAGE_SIZE = 20;
    private const PAGES = 2;

    public function run(): void
    {
        app(RawgImportService::class)->importGames(
            pages: self::PAGES,
            pageSize: self::PAGE_SIZE,
            ordering: '-rating'
        );
    }
}
