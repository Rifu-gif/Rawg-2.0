<?php

use App\Models\Game;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('authenticated user can add a game to favorites', function () {
    $user = User::factory()->create();
    $game = Game::create([
        'rawg_id' => 7001,
        'name' => 'Nova Drift',
        'slug' => 'nova-drift',
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson("/api/favorites/{$game->id}");

    $response->assertCreated()
        ->assertJson([
            'message' => 'Added to favorites',
        ]);

    $this->assertDatabaseHas('game_favorites', [
        'user_id' => $user->id,
        'game_id' => $game->id,
    ]);
});

test('authenticated user can remove a game from favorites', function () {
    $user = User::factory()->create();
    $game = Game::create([
        'rawg_id' => 7002,
        'name' => 'Solar Tide',
        'slug' => 'solar-tide',
    ]);

    $user->favoriteGames()->attach($game->id);
    Sanctum::actingAs($user);

    $response = $this->deleteJson("/api/favorites/{$game->id}");

    $response->assertOk()
        ->assertJson([
            'message' => 'Removed from favorites',
        ]);

    $this->assertDatabaseMissing('game_favorites', [
        'user_id' => $user->id,
        'game_id' => $game->id,
    ]);
});

test('guest cannot add favorites', function () {
    $game = Game::create([
        'rawg_id' => 7003,
        'name' => 'Cloud Forge',
        'slug' => 'cloud-forge',
    ]);

    $this->postJson("/api/favorites/{$game->id}")
        ->assertUnauthorized();
});
