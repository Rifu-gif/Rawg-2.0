<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Category;

// Delete all old categories
Category::truncate();

// Create new categories
$newCategories = [
    'Shooters (FPS and TPS)',
    'Multiplayer online battle arena (MOBA)',
    'Role-playing (RPG, ARPG, and More)',
    'Simulation and sports',
    'Puzzlers and party games',
    'Action-adventure',
    'Survival and horror',
];

foreach ($newCategories as $name) {
    Category::create(['name' => $name]);
}

echo "Categories updated successfully!\n";
echo "Total categories: " . Category::count() . "\n";
