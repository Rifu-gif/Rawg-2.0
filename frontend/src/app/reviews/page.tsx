'use client';

import { useMemo, useState } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { api } from '@/lib/api';
import type { AuthUser, Game, Paginated, Review } from '@/lib/types';
import { tokenStore } from '@/lib/auth';
import Link from 'next/link';

export default function ReviewsPage() {
  const token = tokenStore.get();
  const queryClient = useQueryClient();
  const [search, setSearch] = useState('');
  const [selectedGame, setSelectedGame] = useState<Game | null>(null);
  const [rating, setRating] = useState(8);
  const [title, setTitle] = useState('');
  const [body, setBody] = useState('');

  const meQuery = useQuery({
    queryKey: ['me'],
    queryFn: async () => (await api.get<AuthUser>('/auth/me')).data,
    enabled: Boolean(token),
  });

  const gamesQuery = useQuery({
    queryKey: ['games', { search }],
    queryFn: async () => (await api.get<Paginated<Game>>('/games', { params: { search, per_page: 8 } })).data,
    enabled: search.length > 1,
  });

  const reviewsQuery = useQuery({
    queryKey: ['reviews'],
    queryFn: async () => (await api.get<Paginated<Review>>('/reviews')).data,
    enabled: Boolean(token),
  });

  const myReviews = useMemo(() => {
    const userId = meQuery.data?.id;
    if (!userId || !reviewsQuery.data) return [];
    return reviewsQuery.data.data.filter((r) => r.user_id === userId);
  }, [meQuery.data?.id, reviewsQuery.data]);

  const createReview = useMutation({
    mutationFn: async () => {
      if (!selectedGame) throw new Error('Select a game');
      const { data } = await api.post<Review>('/reviews', {
        game_id: selectedGame.id,
        rating,
        title,
        body,
      });
      return data;
    },
    onSuccess: () => {
      setTitle('');
      setBody('');
      queryClient.invalidateQueries({ queryKey: ['reviews'] });
    },
  });

  const deleteReview = useMutation({
    mutationFn: async (reviewId: number) => {
      await api.delete(`/reviews/${reviewId}`);
      return true;
    },
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['reviews'] }),
  });

  if (!token) {
    return (
      <div className="glass-card rounded-3xl p-8">
        <h1 className="text-2xl font-semibold [font-family:var(--font-display)]">Reviews</h1>
        <p className="mt-3 text-sm text-[var(--ink-3)]">
          Sign in to add or edit reviews. <Link className="text-[var(--brand-1)]" href="/auth/login">Login</Link>
        </p>
      </div>
    );
  }

  return (
    <div className="space-y-8">
      <section className="glass-card rounded-3xl p-8">
        <h1 className="text-2xl font-semibold [font-family:var(--font-display)]">Your reviews</h1>
        <div className="mt-6 grid gap-4 md:grid-cols-2">
          {myReviews.length ? (
            myReviews.map((review) => (
              <div key={review.id} className="rounded-2xl border border-[var(--line)] bg-white p-5">
                <div className="flex items-center justify-between">
                  <p className="text-sm font-semibold">Game #{review.game_id}</p>
                  <span className="rounded-full bg-[var(--brand-3)] px-3 py-1 text-xs font-semibold">
                    {review.rating}/10
                  </span>
                </div>
                <p className="mt-2 text-sm font-semibold">{review.title}</p>
                <p className="mt-2 text-sm text-[var(--ink-3)]">{review.body}</p>
                <button
                  className="mt-4 rounded-full border border-[var(--line)] px-3 py-1 text-xs"
                  onClick={() => deleteReview.mutate(review.id)}
                >
                  Delete
                </button>
              </div>
            ))
          ) : (
            <div className="rounded-2xl border border-dashed border-[var(--line)] p-6 text-sm text-[var(--ink-3)]">
              You have no reviews yet.
            </div>
          )}
        </div>
      </section>

      <section className="glass-card rounded-3xl p-8">
        <h2 className="text-xl font-semibold [font-family:var(--font-display)]">Add a new review</h2>
        <div className="mt-4 space-y-4">
          <input
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            placeholder="Search game name"
            className="w-full rounded-full border border-[var(--line)] bg-white px-4 py-2 text-sm"
          />
          {gamesQuery.data && (
            <div className="grid gap-2 md:grid-cols-2">
              {gamesQuery.data.data.map((game) => (
                <button
                  key={game.id}
                  onClick={() => setSelectedGame(game)}
                  className={`rounded-xl border px-3 py-2 text-left text-sm ${
                    selectedGame?.id === game.id
                      ? 'border-[var(--brand-1)] bg-[var(--brand-1)]/10'
                      : 'border-[var(--line)] bg-white'
                  }`}
                >
                  {game.name}
                </button>
              ))}
            </div>
          )}
          {selectedGame && (
            <p className="text-sm text-[var(--ink-3)]">Selected: {selectedGame.name}</p>
          )}
          <div className="grid gap-3 md:grid-cols-3">
            <input
              type="number"
              min={1}
              max={10}
              value={rating}
              onChange={(e) => setRating(Number(e.target.value))}
              className="rounded-xl border border-[var(--line)] px-3 py-2"
            />
            <input
              value={title}
              onChange={(e) => setTitle(e.target.value)}
              placeholder="Title"
              className="rounded-xl border border-[var(--line)] px-3 py-2 md:col-span-2"
            />
          </div>
          <textarea
            value={body}
            onChange={(e) => setBody(e.target.value)}
            placeholder="Write your review"
            className="min-h-[120px] w-full rounded-xl border border-[var(--line)] px-3 py-2"
          />
          <button
            className="rounded-full bg-[var(--ink-1)] px-4 py-2 text-sm font-semibold text-white"
            onClick={() => createReview.mutate()}
          >
            Publish review
          </button>
        </div>
      </section>
    </div>
  );
}
