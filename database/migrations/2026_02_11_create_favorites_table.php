<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('game_id'); // RAWG API game ID
            $table->string('game_name');
            $table->string('game_image')->nullable();
            $table->decimal('game_rating', 3, 2)->nullable();
            $table->text('game_description')->nullable();
            $table->timestamps(); 
            $table->unique(['user_id', 'game_id']);
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};
