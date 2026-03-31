<?php

namespace App\Services;

use App\Actions\Recommendations\BuildGameRecommendations;
use App\Models\User;

class GameRecommendationService
{
    public function buildForUser(?User $viewer): array
    {
        return app(BuildGameRecommendations::class)->handle($viewer);
    }
}
