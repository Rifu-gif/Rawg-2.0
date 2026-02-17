<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameDetail extends Model
{
    protected $fillable = [
        'game_id',
        'description_raw',
        'esrb_rating',
        'tba',
    ];

    protected $casts = [
        'tba' => 'boolean',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}
