<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rawg_id')->unique();
            $table->string('name');
            $table->string('slug')->index();
            $table->date('released_at')->nullable();
            $table->decimal('rating', 3, 2)->nullable();
            $table->string('background_image')->nullable();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('metacritic')->nullable();
            $table->string('website')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
