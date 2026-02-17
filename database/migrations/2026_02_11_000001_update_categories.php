<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Category;

return new class extends Migration
{
    
    public function up(): void
    {
        
        Category::whereIn('name', ['Technology', 'Health', 'Science', 'Sports', 'Politics', 'Entertainment'])->delete();

        
        $categories = [
            'Shooters (FPS and TPS)',
            'Multiplayer online battle arena (MOBA)',
            'Role-playing (RPG, ARPG, and More)',
            'Simulation and sports',
            'Puzzlers and party games',
            'Action-adventure',
            'Survival and horror',
        ];

        foreach ($categories as $name) {
            Category::create(['name' => $name]);
        }
    }

    
    public function down(): void
    {
        
        Category::whereIn('name', [
            'Shooters (FPS and TPS)',
            'Multiplayer online battle arena (MOBA)',
            'Role-playing (RPG, ARPG, and More)',
            'Simulation and sports',
            'Puzzlers and party games',
            'Action-adventure',
            'Survival and horror',
        ])->delete();

        
        $oldCategories = ['Technology', 'Health', 'Science', 'Sports', 'Politics', 'Entertainment'];
        foreach ($oldCategories as $name) {
            Category::create(['name' => $name]);
        }
    }
};
