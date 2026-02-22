'use client';

import { useMemo, useState } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { api } from '@/lib/api';
import type { AuthUser, Game, Review } from '@/lib/types';
import { tokenStore } from '@/lib/auth';
import Link from 'next/link';

type PageProps = { params: { id: string } };

export default function GameDetailPage({ params }: PageProps) {
  const gameId = Number(params.id);
  const queryClient = useQueryClient();
  const token = tokenStore.get();

  const gameQuery = useQuery({
    queryKey: ['game', gameId],
    queryFn: async () => {
      const { data } = await api.get<Game>(`/games/${gameId}`);
      return data;
    },
  });

  const meQuery = useQuery({
    queryKey: ['me'],
    queryFn: async () => {
      const { data } = await api.get<AuthUser>('/auth/me');
      return data;
    },
    enabled: Boolean(token),
  });

  const favoritesQuery = useQuery({
    queryKey: ['favorites'],
    queryFn: async () => (await api.get<{ data: Game[] }>('/favorites')).data,
    enabled: Boolean(token),
  });

  const myReview = useMemo(() => {
    const userId = meQuery.data?.id;
    if (!userId) return null;
    return gameQuery.data?.reviews?.find((r) => r.user_id === userId) ?? null;
  }, [gameQuery.data?.reviews, meQuery.data?.id]);

  const [rating, setRating] = useState(myReview?.rating ?? 8);
  const [title, setTitle] = useState(myReview?.title ?? '');
  const [body, setBody] = useState(myReview?.body ?? '');

  const saveReview = useMutation({
    mutationFn: async () => {
      if (myReview) {
        const { data } = await api.put<Review>(`/reviews/${myReview.id}`, {
          rating,
          title,
          body,
        });
        return data;
      }
      const { data } = await api.post<Review>('/reviews', {
        game_id: gameId,
        rating,
        title,
        body,
      });
      return data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['game', gameId] });
    },
  });

  const toggleFavorite = useMutation({
    mutationFn: async () => {
      const isFavorited = favoritesQuery.data?.data?.some((g) => g.id === gameId);
      if (isFavorited) {
        await api.delete(`/favorites/${gameId}`);
        return false;
      }
      await api.post(`/favorites/${gameId}`);
      return true;
    },
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['favorites'] }),
  });

  const deleteReview = useMutation({
    mutationFn: async () => {
      if (!myReview) return null;
      await api.delete(`/reviews/${myReview.id}`);
      return true;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['game', gameId] });
    },
  });

  if (gameQuery.isLoading) {
    return <div className="rounded-3xl border border-[var(--line)] p-10">Loading game...</div>;
  }

  const game = gameQuery.data;
  if (!game) {
    return <div className="rounded-3xl border border-[var(--line)] p-10">Game not found.</div>;
  }

  return (
    <div className="space-y-10">
      <section className="grid gap-8 lg:grid-cols-[1.2fr_0.8fr]">
        <div className="overflow-hidden rounded-3xl border border-[var(--line)] bg-white">
          {game.background_image ? (
            // eslint-disable-next-line @next/next/no-img-element
            <img src={game.background_image} alt={game.name} className="h-80 w-full object-cover" />
          ) : (
            <div className="flex h-80 items-center justify-center bg-[var(--ink-2)] text-white/70">No image</div>
          )}
          <div className="space-y-4 p-6">
            <div className="flex items-center justify-between">
              <h1 className="text-3xl font-semibold [font-family:var(--font-display)]">{game.name}</h1>
              {game.rating && (
                <span className="rounded-full bg-[var(--brand-3)] px-4 py-2 text-sm font-semibold">
                  {game.rating.toFixed(1)} / 5
                </span>
              )}
            </div>
            <p className="text-sm text-[var(--ink-3)]">
              Released: {game.released_at ?? 'TBA'}
            </p>
            <div className="flex flex-wrap gap-2">
              {game.genres?.map((g) => (
                <span key={g.id} className="rounded-full border border-[var(--line)] px-3 py-1 text-xs">
                  {g.name}
                </span>
              ))}
              {game.platforms?.map((p) => (
                <span key={p.id} className="rounded-full bg-[var(--ink-2)] px-3 py-1 text-xs text-white">
                  {p.name}
                </span>
              ))}
            </div>
            <p className="text-sm leading-6 text-[var(--ink-2)]">
              {game.detail?.description_raw || game.description || 'No description available.'}
            </p>
          </div>
        </div>

        <aside className="space-y-4">
          <div className="glass-card rounded-3xl p-6">
            <h2 className="text-lg font-semibold">Quick stats</h2>
            <div className="mt-4 space-y-2 text-sm text-[var(--ink-3)]">
              <p>Metacritic: {game.metacritic ?? 'N/A'}</p>
              <p>ESRB: {game.detail?.esrb_rating ?? 'N/A'}</p>
              <p>Website: {game.website ? <a className="text-[var(--brand-1)]" href={game.website}>Visit</a> : 'N/A'}</p>
            </div>
            {token && (
              <button
                className="mt-5 w-full rounded-full border border-[var(--line)] px-4 py-2 text-sm"
                onClick={() => toggleFavorite.mutate()}
              >
                {favoritesQuery.data?.data?.some((g) => g.id === gameId) ? 'Remove from favorites' : 'Add to favorites'}
              </button>
            )}
          </div>
          <div className="glass-card rounded-3xl p-6">
            <h2 className="text-lg font-semibold">Your review</h2>
            {!token && (
              <p className="mt-3 text-sm text-[var(--ink-3)]">
                Sign in to add a review. <Link className="text-[var(--brand-1)]" href="/auth/login">Login</Link>
              </p>
            )}
            {token && (
              <form
                className="mt-4 space-y-3"
                onSubmit={(e) => {
                  e.preventDefault();
                  saveReview.mutate();
                }}
              >
                <label className="text-xs uppercase tracking-[0.2em] text-[var(--ink-3)]">Rating</label>
                <input
                  type="number"
                  min={1}
                  max={10}
                  value={rating}
                  onChange={(e) => setRating(Number(e.target.value))}
                  className="w-full rounded-xl border border-[var(--line)] px-3 py-2"
                />
                <input
                  value={title}
                  onChange={(e) => setTitle(e.target.value)}
                  placeholder="Short title"
                  className="w-full rounded-xl border border-[var(--line)] px-3 py-2"
                />
                <textarea
                  value={body}
                  onChange={(e) => setBody(e.target.value)}
                  placeholder="Share what stood out"
                  className="min-h-[120px] w-full rounded-xl border border-[var(--line)] px-3 py-2"
                />
                <div className="flex items-center gap-3">
                  <button
                    type="submit"
                    className="rounded-full bg-[var(--ink-1)] px-4 py-2 text-sm font-semibold text-white"
                    disabled={saveReview.isPending}
                  >
                    {myReview ? 'Update review' : 'Publish review'}
                  </button>
                  {myReview && (
                    <button
                      type="button"
                      className="rounded-full border border-[var(--line)] px-4 py-2 text-sm"
                      onClick={() => deleteReview.mutate()}
                    >
                      Delete
                    </button>
                  )}
                </div>
              </form>
            )}
          </div>
        </aside>
      </section>

      <section className="space-y-4">
        <h2 className="text-2xl font-semibold [font-family:var(--font-display)]">Community reviews</h2>
        <div className="grid gap-4 md:grid-cols-2">
          {game.reviews?.length ? (
            game.reviews.map((review) => (
              <div key={review.id} className="rounded-2xl border border-[var(--line)] bg-white p-5">
                <div className="flex items-center justify-between">
                  <p className="text-sm font-semibold">{review.user?.name ?? 'Player'}</p>
                  <span className="rounded-full bg-[var(--brand-3)] px-3 py-1 text-xs font-semibold">
                    {review.rating}/10
                  </span>
                </div>
                <p className="mt-2 text-sm font-semibold text-[var(--ink-1)]">{review.title}</p>
                <p className="mt-2 text-sm text-[var(--ink-3)]">{review.body}</p>
              </div>
            ))
          ) : (
            <div className="rounded-2xl border border-dashed border-[var(--line)] p-6 text-sm text-[var(--ink-3)]">
              No reviews yet. Be the first to add one.
            </div>
          )}
        </div>
      </section>
    </div>
  );
}
