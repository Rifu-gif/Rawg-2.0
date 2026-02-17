<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Game extends Model
{
    protected $fillable = [
        'rawg_id',
        'name',
        'slug',
        'released_at',
        'rating',
        'background_image',
        'description',
        'metacritic',
        'website',
    ];

    protected $casts = [
        'released_at' => 'date',
        'rating' => 'float',
        'metacritic' => 'integer',
    ];

    public function detail(): HasOne
    {
        return $this->hasOne(GameDetail::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class, 'game_genre');
    }

    public function platforms(): BelongsToMany
    {
        return $this->belongsToMany(Platform::class, 'game_platform');
    }
}
