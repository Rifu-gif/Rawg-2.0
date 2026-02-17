<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Database\Seeders\GenreSeeder;
use Database\Seeders\PlatformSeeder;
use Database\Seeders\GameSeeder;

class DatabaseSeeder extends Seeder
{
    
    public function run(): void
    {
        $categories = [
            'Shooters (FPS and TPS)',
            'Multiplayer online battle arena (MOBA)',
            'Role-playing (RPG, ARPG, and More)',
            'Simulation and sports',
            'Puzzlers and party games',
            'Action-adventure',
            'Survival and horror',
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(['name' => $category], ['name' => $category]);
        }

        $this->call([
            GenreSeeder::class,
            PlatformSeeder::class,
            GameSeeder::class,
        ]);

    }
}
