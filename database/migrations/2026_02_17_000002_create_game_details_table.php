<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->onDelete('cascade');
            $table->longText('description_raw')->nullable();
            $table->string('esrb_rating')->nullable();
            $table->boolean('tba')->default(false);
            $table->timestamps();
            $table->unique('game_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_details');
    }
};
