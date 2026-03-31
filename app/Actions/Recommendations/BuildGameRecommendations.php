<?php

namespace App\Actions\Recommendations;

use App\Models\Game;
use App\Models\User;
use Illuminate\Support\Collection;

class BuildGameRecommendations
{
    private const FAVORITES_RECOMMENDATION_LIMIT = 10;
    private const REVIEW_RECOMMENDATION_LIMIT = 6;
    private const FALLBACK_RECOMMENDATION_LIMIT = 10;

    public function handle(?User $viewer): array
    {
        if (!$viewer) {
            return $this->buildFallbackRecommendations();
        }

        $favoriteGames = $viewer->favoriteGames()
            ->with(['genres:id,name,slug', 'platforms:id,name,slug'])
            ->get();

        $reviews = $viewer->reviews()
            ->with(['game.genres:id,name,slug', 'game.platforms:id,name,slug'])
            ->get();

        $favoriteGenreWeights = [];
        $favoritePlatformWeights = [];

        foreach ($favoriteGames as $game) {
            foreach ($game->genres as $genre) {
                $favoriteGenreWeights[$genre->id] = ($favoriteGenreWeights[$genre->id] ?? 0) + 1;
            }
            foreach ($game->platforms as $platform) {
                $favoritePlatformWeights[$platform->id] = ($favoritePlatformWeights[$platform->id] ?? 0) + 1;
            }
        }

        $reviewGenreWeights = [];
        $reviewPlatformWeights = [];
        $highRatedReviews = $reviews->where('rating', '>=', 8);
        foreach ($highRatedReviews as $review) {
            $weight = max(1, (int) $review->rating - 7);
            if (!$review->game) {
                continue;
            }
            foreach ($review->game->genres as $genre) {
                $reviewGenreWeights[$genre->id] = ($reviewGenreWeights[$genre->id] ?? 0) + $weight;
            }
            foreach ($review->game->platforms as $platform) {
                $reviewPlatformWeights[$platform->id] = ($reviewPlatformWeights[$platform->id] ?? 0) + $weight;
            }
        }

        $hasFavoritesSignals = !empty($favoriteGenreWeights) || !empty($favoritePlatformWeights);
        $hasReviewSignals = !empty($reviewGenreWeights) || !empty($reviewPlatformWeights);

        if (!$hasFavoritesSignals && !$hasReviewSignals) {
            return $this->buildFallbackRecommendations();
        }

        $excludedGameIds = $favoriteGames->pluck('id')->all();
        $gameCandidates = Game::query()
            ->with(['genres:id,name,slug', 'platforms:id,name,slug'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->whereNotIn('id', $excludedGameIds)
            ->latest()
            ->limit(180)
            ->get()
            ->map(function (Game $game) use (
                $favoriteGames,
                $favoriteGenreWeights,
                $favoritePlatformWeights,
                $reviewGenreWeights,
                $reviewPlatformWeights
            ) {
                $favoriteScore = 0;
                $reviewScore = 0;
                $favoriteReasons = [];
                $reviewReasons = [];

                $sharedFavoriteGenres = [];
                foreach ($game->genres as $genre) {
                    if (isset($favoriteGenreWeights[$genre->id])) {
                        $favoriteScore += 2 * $favoriteGenreWeights[$genre->id];
                        $sharedFavoriteGenres[] = $genre->name;
                    }
                    if (isset($reviewGenreWeights[$genre->id])) {
                        $reviewScore += 2 * $reviewGenreWeights[$genre->id];
                    }
                }

                $sharedFavoritePlatforms = [];
                $sharedReviewPlatforms = [];
                $sharedReviewGenres = [];

                foreach ($game->genres as $genre) {
                    if (isset($reviewGenreWeights[$genre->id])) {
                        $sharedReviewGenres[] = $genre->name;
                    }
                }

                foreach ($game->platforms as $platform) {
                    if (isset($favoritePlatformWeights[$platform->id])) {
                        $favoriteScore += $favoritePlatformWeights[$platform->id];
                        $sharedFavoritePlatforms[] = $platform->name;
                    }
                    if (isset($reviewPlatformWeights[$platform->id])) {
                        $reviewScore += $reviewPlatformWeights[$platform->id];
                        $sharedReviewPlatforms[] = $platform->name;
                    }
                }

                if (!empty($sharedFavoriteGenres)) {
                    $favoriteReasons[] = 'Favorites-based: shared genres (' . implode(', ', array_slice($sharedFavoriteGenres, 0, 2)) . ').';
                }
                if (!empty($sharedFavoritePlatforms)) {
                    $favoriteReasons[] = 'Favorites-based: shared platforms (' . implode(', ', array_slice($sharedFavoritePlatforms, 0, 2)) . ').';
                }

                if (!empty($sharedReviewGenres)) {
                    $reviewReasons[] = 'Review-based: matches highly rated genres (' . implode(', ', array_slice($sharedReviewGenres, 0, 2)) . ').';
                }
                if (!empty($sharedReviewPlatforms)) {
                    $reviewReasons[] = 'Review-based: matches platforms from your highest-rated reviews (' . implode(', ', array_slice($sharedReviewPlatforms, 0, 2)) . ').';
                }

                $communityTieBreaker = (int) round(((float) ($game->reviews_avg_rating ?? $game->rating ?? 0)) / 2);
                $favoriteSource = $this->resolveFavoriteSource($favoriteGames, $sharedFavoriteGenres, $sharedFavoritePlatforms);

                return [
                    'game' => $game,
                    'favorite_score' => $favoriteScore + $communityTieBreaker,
                    'favorite_reasons' => $favoriteReasons,
                    'favorite_explanation' => $this->buildFavoritesExplanation($favoriteSource, $sharedFavoriteGenres, $sharedFavoritePlatforms),
                    'favorite_source_id' => $favoriteSource['id'] ?? null,
                    'review_score' => $reviewScore + $communityTieBreaker,
                    'review_reasons' => $reviewReasons,
                    'review_explanation' => $this->buildReviewExplanation($sharedReviewGenres, $sharedReviewPlatforms),
                ];
            });

        $favoriteGamesRecommendations = collect();
        if ($hasFavoritesSignals) {
            $favoriteCandidates = $this->sortCandidatesDeterministically(
                $gameCandidates
                    ->filter(fn (array $candidate) => $candidate['favorite_score'] > 0 && !empty($candidate['favorite_reasons']))
                    ->values(),
                'favorite_score'
            );

            $favoriteGamesRecommendations = $this->selectDiverseCandidates(
                $favoriteCandidates,
                'favorite_score',
                'favorite',
                self::FAVORITES_RECOMMENDATION_LIMIT
            )->map(fn (array $candidate) => $this->serializeRecommendedGame(
                $candidate['game'],
                $candidate['favorite_score'],
                $candidate['favorite_reasons'],
                $candidate['favorite_explanation']
            ));
        }

        $reviewGamesRecommendations = collect();
        if ($hasReviewSignals) {
            $reviewCandidates = $this->sortCandidatesDeterministically(
                $gameCandidates
                    ->filter(fn (array $candidate) => $candidate['review_score'] > 0 && !empty($candidate['review_reasons']))
                    ->values(),
                'review_score'
            );

            $reviewGamesRecommendations = $this->selectDiverseCandidates(
                $reviewCandidates,
                'review_score',
                'review',
                self::REVIEW_RECOMMENDATION_LIMIT
            )->map(fn (array $candidate) => $this->serializeRecommendedGame(
                $candidate['game'],
                $candidate['review_score'],
                $candidate['review_reasons'],
                $candidate['review_explanation']
            ));
        }

        $fallback = $this->buildFallbackRecommendations();
        $fallbackGames = collect($fallback['games']['favorites_based_similarity'] ?? []);

        if ($favoriteGamesRecommendations->isEmpty()) {
            $favoriteGamesRecommendations = $fallbackGames
                ->take(self::FALLBACK_RECOMMENDATION_LIMIT)
                ->map(function (array $game) {
                    $game['recommendation_reasons'] = ['Fallback: shown because there is not enough favorites history yet.'];
                    $game['recommendation_explanation'] = 'Trending this week based on community favorites and reviews.';
                    return $game;
                });
        }

        if ($reviewGamesRecommendations->isEmpty()) {
            $reviewGamesRecommendations = $fallbackGames
                ->take(self::REVIEW_RECOMMENDATION_LIMIT)
                ->map(function (array $game) {
                    $game['recommendation_reasons'] = ['Fallback: shown because there is not enough high-rated review history yet.'];
                    $game['recommendation_explanation'] = 'Popular among players with similar tastes in highly rated games.';
                    return $game;
                });
        }

        return [
            'strategies_used' => [
                'favorites_based_similarity',
                'review_based_similarity',
                'merged_similarity',
            ],
            'insufficient_data' => !$hasFavoritesSignals || !$hasReviewSignals,
            'summary' => 'Game recommendations are generated using deterministic rules from your favorites and high-rated reviews.',
            'games' => [
                'favorites_based_similarity' => $favoriteGamesRecommendations->values()->all(),
                'review_based_similarity' => $reviewGamesRecommendations->values()->all(),
                'merged_similarity' => $this->mergeRecommendationsDeterministically(
                    $favoriteGamesRecommendations->values()->all(),
                    $reviewGamesRecommendations->values()->all()
                ),
            ],
        ];
    }

    private function buildFallbackRecommendations(): array
    {
        $games = Game::query()
            ->with(['genres:id,name,slug', 'platforms:id,name,slug'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->orderByDesc('reviews_avg_rating')
            ->orderByDesc('rating')
            ->orderBy('name')
            ->orderBy('id')
            ->take(self::FALLBACK_RECOMMENDATION_LIMIT)
            ->get()
            ->map(fn (Game $game) => $this->serializeRecommendedGame(
                $game,
                max(1, (int) round(((float) ($game->reviews_avg_rating ?? $game->rating ?? 0)) * 2)),
                ['Fallback: shown because there is not enough favorites/review history yet.'],
                'Trending this week based on community favorites and reviews.'
            ))
            ->values();

        if ($games->isEmpty()) {
            $games = collect([$this->buildPlaceholderRecommendation()]);
        }

        $serializedGames = $games->all();

        return [
            'strategies_used' => [
                'fallback_popularity',
                'merged_similarity',
            ],
            'insufficient_data' => true,
            'summary' => 'Not enough favorites or reviews yet, so popular games are shown until you add more interactions.',
            'games' => [
                'favorites_based_similarity' => $serializedGames,
                'review_based_similarity' => $serializedGames,
                'merged_similarity' => $serializedGames,
            ],
        ];
    }

    private function buildPlaceholderRecommendation(): array
    {
        return [
            'id' => 0,
            'name' => 'Game catalog is still being prepared',
            'slug' => 'catalog-pending',
            'background_image' => null,
            'rating' => null,
            'reviews_count' => 0,
            'genres' => [],
            'platforms' => [],
            'recommendation_score' => 1,
            'recommendation_reasons' => ['Fallback: the game catalog does not contain enough records yet.'],
            'recommendation_explanation' => 'Recommendations will appear here once games are available in the catalog.',
        ];
    }

    private function serializeRecommendedGame(Game $game, int $score, array $reasons, string $explanation): array
    {
        return [
            'id' => $game->id,
            'name' => $game->name,
            'slug' => $game->slug,
            'background_image' => $game->background_image,
            'rating' => $game->rating,
            'reviews_count' => (int) ($game->reviews_count ?? 0),
            'genres' => $game->genres->map(fn ($genre) => [
                'id' => $genre->id,
                'name' => $genre->name,
                'slug' => $genre->slug,
            ])->values()->all(),
            'platforms' => $game->platforms->map(fn ($platform) => [
                'id' => $platform->id,
                'name' => $platform->name,
                'slug' => $platform->slug,
            ])->values()->all(),
            'recommendation_score' => $score,
            'recommendation_reasons' => array_values(array_unique($reasons)),
            'recommendation_explanation' => $explanation,
        ];
    }

    private function buildFavoritesExplanation(?array $favoriteSource, array $sharedGenres, array $sharedPlatforms): string
    {
        if ($favoriteSource && !empty($favoriteSource['signals'])) {
            return 'Because you favorited ' . $favoriteSource['name'] . ' (' . implode(', ', array_slice($favoriteSource['signals'], 0, 2)) . ').';
        }

        if (!empty($sharedGenres)) {
            return 'Because it shares genres you often favorite, like ' . implode(', ', array_slice($sharedGenres, 0, 2)) . '.';
        }

        if (!empty($sharedPlatforms)) {
            return 'Because it matches platforms from your favorite games, like ' . implode(', ', array_slice($sharedPlatforms, 0, 2)) . '.';
        }

        return 'Because it is similar to the games you have favorited.';
    }

    private function buildReviewExplanation(array $sharedGenres, array $sharedPlatforms): string
    {
        if (!empty($sharedGenres)) {
            return 'Popular among players who like ' . implode(', ', array_slice($sharedGenres, 0, 2)) . ' games.';
        }

        if (!empty($sharedPlatforms)) {
            return 'Matches platforms from games you rated highly, like ' . implode(', ', array_slice($sharedPlatforms, 0, 2)) . '.';
        }

        return 'Recommended from patterns in your highest-rated reviews.';
    }

    private function sortCandidatesDeterministically(Collection $candidates, string $scoreKey): Collection
    {
        return $candidates->sort(function (array $left, array $right) use ($scoreKey) {
            $comparisons = [
                $right[$scoreKey] <=> $left[$scoreKey],
                ((float) ($right['game']->reviews_avg_rating ?? 0)) <=> ((float) ($left['game']->reviews_avg_rating ?? 0)),
                ((float) ($right['game']->rating ?? 0)) <=> ((float) ($left['game']->rating ?? 0)),
                strcmp((string) $left['game']->name, (string) $right['game']->name),
                $left['game']->id <=> $right['game']->id,
            ];

            foreach ($comparisons as $comparison) {
                if ($comparison !== 0) {
                    return $comparison;
                }
            }

            return 0;
        })->values();
    }

    private function selectDiverseCandidates(Collection $candidates, string $scoreKey, string $strategy, int $limit = 4): Collection
    {
        $selected = collect();
        $usedFranchises = [];
        $usedPrimaryGenres = [];
        $usedExplanations = [];
        $usedSources = [];

        foreach ($candidates as $candidate) {
            if ($selected->count() >= $limit) {
                break;
            }

            /** @var Game $game */
            $game = $candidate['game'];
            $franchiseKey = $this->franchiseKey($game->name);
            $primaryGenre = $game->genres->first()?->slug ?? $game->genres->first()?->name ?? null;
            $explanation = $candidate[str_replace('_score', '_explanation', $scoreKey)] ?? '';
            $sourceId = $strategy === 'favorite' ? ($candidate['favorite_source_id'] ?? null) : null;

            $sameFranchise = $franchiseKey !== '' && isset($usedFranchises[$franchiseKey]);
            $samePrimaryGenre = $primaryGenre !== null && isset($usedPrimaryGenres[$primaryGenre]);
            $sameExplanation = $explanation !== '' && isset($usedExplanations[$explanation]);
            $sameSource = $sourceId !== null && isset($usedSources[$sourceId]);

            if (
                $sameFranchise ||
                $sameExplanation ||
                ($strategy === 'favorite' && $sameSource && $selected->count() < $limit - 1) ||
                ($samePrimaryGenre && $selected->count() < $limit - 1)
            ) {
                continue;
            }

            $selected->push($candidate);

            if ($franchiseKey !== '') {
                $usedFranchises[$franchiseKey] = true;
            }
            if ($primaryGenre !== null) {
                $usedPrimaryGenres[$primaryGenre] = true;
            }
            if ($explanation !== '') {
                $usedExplanations[$explanation] = true;
            }
            if ($sourceId !== null) {
                $usedSources[$sourceId] = true;
            }
        }

        if ($selected->count() < $limit) {
            foreach ($candidates as $candidate) {
                if ($selected->count() >= $limit) {
                    break;
                }

                $alreadySelected = $selected->contains(fn (array $item) => $item['game']->id === $candidate['game']->id);
                if (!$alreadySelected) {
                    $selected->push($candidate);
                }
            }
        }

        return $selected->take($limit)->values();
    }

    private function mergeRecommendationsDeterministically(array $favoriteRecommendations, array $reviewRecommendations): array
    {
        $merged = [];

        foreach (['favorites_based_similarity' => $favoriteRecommendations, 'review_based_similarity' => $reviewRecommendations] as $strategy => $games) {
            foreach ($games as $game) {
                $gameId = (int) $game['id'];
                $currentScore = (int) $game['recommendation_score'];

                if (!isset($merged[$gameId])) {
                    $merged[$gameId] = $game + ['matched_strategies' => [], 'best_strategy_score' => $currentScore];
                    $merged[$gameId]['matched_strategies'] = [];
                } else {
                    $merged[$gameId]['recommendation_score'] += $currentScore;
                    $merged[$gameId]['recommendation_reasons'] = array_values(array_unique(array_merge(
                        $merged[$gameId]['recommendation_reasons'],
                        $game['recommendation_reasons']
                    )));

                    if ($currentScore > (int) $merged[$gameId]['best_strategy_score']) {
                        $merged[$gameId]['recommendation_explanation'] = $game['recommendation_explanation'];
                        $merged[$gameId]['best_strategy_score'] = $currentScore;
                    }
                }

                $merged[$gameId]['matched_strategies'][] = $strategy;
                $merged[$gameId]['matched_strategies'] = array_values(array_unique($merged[$gameId]['matched_strategies']));
            }
        }

        $mergedCollection = collect(array_values($merged))->sort(function (array $left, array $right) {
            $comparisons = [
                $right['recommendation_score'] <=> $left['recommendation_score'],
                strcmp((string) $left['name'], (string) $right['name']),
                $left['id'] <=> $right['id'],
            ];

            foreach ($comparisons as $comparison) {
                if ($comparison !== 0) {
                    return $comparison;
                }
            }

            return 0;
        });

        return $mergedCollection
            ->map(function (array $game) {
                unset($game['best_strategy_score']);
                return $game;
            })
            ->values()
            ->all();
    }

    private function resolveFavoriteSource($favoriteGames, array $sharedGenres, array $sharedPlatforms): ?array
    {
        $bestMatch = null;
        $bestScore = -1;

        foreach ($favoriteGames as $favoriteGame) {
            $favoriteGenreNames = $favoriteGame->genres->pluck('name')->all();
            $favoritePlatformNames = $favoriteGame->platforms->pluck('name')->all();

            $overlapGenres = array_values(array_intersect($sharedGenres, $favoriteGenreNames));
            $overlapPlatforms = array_values(array_intersect($sharedPlatforms, $favoritePlatformNames));
            $signals = array_slice(array_merge($overlapGenres, $overlapPlatforms), 0, 2);
            $score = (count($overlapGenres) * 2) + count($overlapPlatforms);

            if (
                $score > $bestScore ||
                ($score === $bestScore && $bestMatch && strcmp((string) $favoriteGame->name, (string) $bestMatch['name']) < 0)
            ) {
                if (!empty($signals)) {
                    $bestScore = $score;
                    $bestMatch = [
                        'id' => $favoriteGame->id,
                        'name' => $favoriteGame->name,
                        'signals' => $signals,
                    ];
                }
            }
        }

        return $bestMatch;
    }

    private function franchiseKey(string $name): string
    {
        $normalized = strtolower($name);
        $normalized = preg_replace('/[:\-].*$/', '', $normalized) ?? $normalized;
        $normalized = preg_replace('/\b(i|ii|iii|iv|v|vi|vii|viii|ix|x|\d+)\b/i', '', $normalized) ?? $normalized;
        $normalized = preg_replace('/\b(game of the year|remastered|edition|complete|enhanced|definitive|blood and wine)\b/i', '', $normalized) ?? $normalized;
        $normalized = preg_replace('/[^a-z0-9\s]/', ' ', $normalized) ?? $normalized;
        $normalized = preg_replace('/\s+/', ' ', trim($normalized)) ?? '';

        $parts = array_values(array_filter(explode(' ', $normalized)));
        return implode(' ', array_slice($parts, 0, 2));
    }
}
