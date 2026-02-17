<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Favorite extends Model
{
    protected $fillable = [
        'user_id',
        'game_id',
        'game_name',
        'game_image',
        'game_rating',
        'game_description',
    ];

    protected $casts = [
        'game_rating' => 'float',
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
