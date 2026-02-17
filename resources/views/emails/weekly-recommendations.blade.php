<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Weekly Game Recommendations</title>
</head>
<body style="margin:0;padding:24px;background:#0f172a;color:#e2e8f0;font-family:Arial,sans-serif;">
    <div style="max-width:720px;margin:0 auto;background:#111827;border:1px solid #334155;border-radius:16px;overflow:hidden;">
        <div style="padding:24px 24px 12px;background:linear-gradient(90deg,#0f172a,#1e293b);">
            <h1 style="margin:0;color:#ffffff;font-size:28px;">Weekly Recommendations</h1>
            <p style="margin:10px 0 0;color:#cbd5e1;font-size:15px;">
                Hi {{ $user->name }}, here are fresh game picks based on your favorites and highest-rated reviews.
            </p>
        </div>

        <div style="padding:24px;">
            @foreach ([
                'Favorites-Based Similarity' => $recommendations['games']['favorites_based_similarity'] ?? [],
                'Review-Based Similarity' => $recommendations['games']['review_based_similarity'] ?? [],
            ] as $sectionTitle => $games)
                <div style="margin-bottom:28px;">
                    <h2 style="margin:0 0 12px;color:#67e8f9;font-size:20px;">{{ $sectionTitle }}</h2>

                    @forelse ($games as $game)
                        <div style="margin-bottom:14px;padding:16px;border:1px solid #334155;border-radius:12px;background:#0f172a;">
                            <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;">
                                <div>
                                    <div style="color:#ffffff;font-size:18px;font-weight:700;">{{ $game['name'] }}</div>
                                    <div style="margin-top:4px;color:#94a3b8;font-size:13px;">
                                        Score {{ $game['recommendation_score'] }} | Rating {{ $game['rating'] ?? 'N/A' }}
                                    </div>
                                    <div style="margin-top:6px;color:#cbd5e1;font-size:13px;">
                                        Genres:
                                        {{ collect($game['genres'] ?? [])->pluck('name')->take(2)->implode(', ') ?: 'N/A' }}
                                    </div>
                                    <div style="margin-top:10px;color:#a5f3fc;font-size:15px;line-height:1.6;">
                                        {{ $game['recommendation_explanation'] }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p style="margin:0;color:#94a3b8;font-size:14px;">No recommendations available for this section yet.</p>
                    @endforelse
                </div>
            @endforeach

            <div style="margin-top:32px;padding-top:16px;border-top:1px solid #334155;color:#94a3b8;font-size:13px;">
                <p style="margin:0 0 10px;">You are receiving this because weekly recommendation emails are enabled on your account.</p>
                <p style="margin:0;">
                    <a href="{{ $unsubscribeUrl }}" style="color:#67e8f9;text-decoration:none;">Unsubscribe from weekly recommendation emails</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
