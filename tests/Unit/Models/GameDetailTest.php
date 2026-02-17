<?php

use App\Models\Game;
use App\Models\GameDetail;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('game detail casts tba as boolean', function () {
    $game = Game::create([
        'rawg_id' => 1001,
        'name' => 'Hollow Blade',
        'slug' => 'hollow-blade',
    ]);

    $detail = GameDetail::create([
        'game_id' => $game->id,
        'description_raw' => 'Soon',
        'esrb_rating' => 'T',
        'tba' => 1,
    ]);

    expect($detail->tba)->toBeTrue();
});

test('game detail belongs to a game', function () {
    $game = Game::create([
        'rawg_id' => 1002,
        'name' => 'Sky Echo',
        'slug' => 'sky-echo',
    ]);

    $detail = GameDetail::create([
        'game_id' => $game->id,
        'description_raw' => 'Description',
        'esrb_rating' => 'E10+',
        'tba' => false,
    ]);

    expect($detail->game())->toBeInstanceOf(BelongsTo::class)
        ->and($detail->game->is($game))->toBeTrue()
        ->and($game->detail->is($detail))->toBeTrue();
});
