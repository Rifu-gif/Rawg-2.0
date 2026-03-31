'use client';

import { useEffect, useRef, useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { api } from '@/lib/api';
import { useAuthToken } from '@/lib/auth';
import type { AuthUser, PostsFeedResponse } from '@/lib/types';

const FAVORITE_RECOMMENDATION_DISPLAY_LIMIT = 10;
const REVIEW_RECOMMENDATION_DISPLAY_LIMIT = 6;

export default function PostsPage() {
  const router = useRouter();
  const queryClient = useQueryClient();
  const token = useAuthToken();
  const isAuthenticated = Boolean(token);
  const [page, setPage] = useState(1);
  const favoritesRecScrollerRef = useRef<HTMLDivElement | null>(null);
  const reviewRecScrollerRef = useRef<HTMLDivElement | null>(null);

  useEffect(() => {
    if (!isAuthenticated) {
      router.replace('/auth/login');
    }
  }, [isAuthenticated, router]);

  const postsQuery = useQuery({
    queryKey: ['posts', 'all', page],
    queryFn: async () => (await api.get<PostsFeedResponse>('/posts', { params: { page } })).data,
    enabled: isAuthenticated,
  });
  const meQuery = useQuery({
    queryKey: ['me'],
    queryFn: async () => (await api.get<AuthUser>('/auth/me')).data,
    enabled: isAuthenticated,
  });

  const toggleFavoritePost = useMutation({
    mutationFn: async ({ postId, isFavorited }: { postId: number; isFavorited: boolean }) => {
      if (isFavorited) await api.delete(`/favorites/posts/${postId}`);
      else await api.post(`/favorites/posts/${postId}`);
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['posts', 'all'] });
      queryClient.invalidateQueries({ queryKey: ['favorites', 'posts'] });
      queryClient.invalidateQueries({ queryKey: ['posts', 'mine', 'profile'] });
    },
  });

  const toggleLikePost = useMutation({
    mutationFn: async (postId: number) => api.post(`/posts/${postId}/like`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['posts', 'all'] });
      queryClient.invalidateQueries({ queryKey: ['favorites', 'posts'] });
      queryClient.invalidateQueries({ queryKey: ['posts', 'mine', 'profile'] });
    },
  });
  const toggleWeeklyRecommendationEmails = useMutation({
    mutationFn: async () => {
      if (!meQuery.data) throw new Error('Profile not loaded');
      const nextValue = !(meQuery.data.weekly_recommendation_emails ?? true);
      const formData = new FormData();
      formData.append('name', meQuery.data.name);
      formData.append('username', meQuery.data.username);
      formData.append('email', meQuery.data.email);
      formData.append('bio', meQuery.data.bio ?? '');
      formData.append('weekly_recommendation_emails', nextValue ? '1' : '0');
      const { data } = await api.put<AuthUser>('/auth/profile', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });
      return data;
    },
    onSuccess: (user) => {
      queryClient.setQueryData(['me'], user);
      queryClient.invalidateQueries({ queryKey: ['me'] });
    },
  });

  const totalPages = postsQuery.data?.last_page ?? 1;
  const recommendations = postsQuery.data?.recommendations;
  const favoriteBasedGames = (recommendations?.games.favorites_based_similarity ?? []).slice(0, FAVORITE_RECOMMENDATION_DISPLAY_LIMIT);
  const reviewBasedGames = (recommendations?.games.review_based_similarity ?? []).slice(0, REVIEW_RECOMMENDATION_DISPLAY_LIMIT);

  function resolveImageUrl(image: string | null | undefined) {
    if (!image || image.trim() === '') return null;
    if (/^https?:\/\//i.test(image)) return image;
    const apiBase = process.env.NEXT_PUBLIC_API_BASE_URL || 'http://laravel-project.test/api';
    const appBase = apiBase.replace(/\/api\/?$/, '');
    return `${appBase}/storage/${image}`;
  }

  function handleRecommendationWheel(
    event: React.WheelEvent<HTMLDivElement>,
    scrollerRef: React.MutableRefObject<HTMLDivElement | null>
  ) {
    const scroller = scrollerRef.current;
    if (!scroller) return;
    if (Math.abs(event.deltaY) > 0 || Math.abs(event.deltaX) > 0) {
      event.preventDefault();
      scroller.scrollLeft += event.deltaY + event.deltaX;
    }
  }

  if (!isAuthenticated) return null;

  return (
    <div className="min-h-screen bg-slate-950 px-4 py-8">
      <div className="mx-auto w-full max-w-7xl space-y-6">
        <section className="rounded-2xl border border-slate-700 bg-slate-900/80 p-6 shadow-lg">
          {recommendations && (
            <div className="mb-6 rounded-xl border border-cyan-500/30 bg-slate-900/70 p-4">
              <div className="mb-4 rounded-xl border border-cyan-400/30 bg-cyan-500/5 p-4">
                <p className="text-sm text-cyan-100">
                  Want to stay on top of trending games based on your favorites and reviews? Subscribe and we&apos;ll send you a weekly recommendation email.
                </p>
                <button
                  type="button"
                  disabled={meQuery.isLoading || toggleWeeklyRecommendationEmails.isPending}
                  onClick={() => toggleWeeklyRecommendationEmails.mutate()}
                  className="mt-3 rounded-full border border-cyan-300/50 bg-cyan-500/15 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-cyan-100 transition hover:bg-cyan-500/25 disabled:cursor-not-allowed disabled:opacity-60"
                >
                  {toggleWeeklyRecommendationEmails.isPending
                    ? 'Updating...'
                    : meQuery.data?.weekly_recommendation_emails ?? true
                      ? 'Unsubscribe from weekly emails'
                      : 'Subscribe to weekly emails'}
                </button>
              </div>
              <div className="mb-3 flex flex-wrap items-center gap-2">
                <h2 className="text-lg font-bold text-white">Recommended for You</h2>
                <span className="rounded-full border border-cyan-400/50 bg-cyan-500/10 px-2.5 py-0.5 text-[11px] font-semibold text-cyan-200">
                  {recommendations.insufficient_data ? 'Partial fallback mode' : 'Personalized mode'}
                </span>
              </div>

              <div className="mt-4 space-y-4">
                <div className="rounded-lg border border-slate-700 bg-slate-800/50 p-3">
                  <h3 className="text-sm font-semibold text-cyan-300">Favorites-Based Similarity</h3>
                  <p className="mt-1 text-xs text-slate-400">Games that share genres or platforms with your favorite games.</p>
                  {favoriteBasedGames.length === 0 && <p className="mt-2 text-xs text-slate-400">No favorites-based game recommendations available yet.</p>}
                  {favoriteBasedGames.length > 0 && (
                    <div
                      ref={favoritesRecScrollerRef}
                      className="mt-2 overflow-x-auto"
                      onWheel={(event) => handleRecommendationWheel(event, favoritesRecScrollerRef)}
                    >
                      <div className="flex w-max gap-4">
                        {favoriteBasedGames.map((game) => (
                          <article key={`favorite-recommended-game-${game.id}`} className="w-72 shrink-0 overflow-hidden rounded-lg border border-slate-700 bg-slate-900/50">
                            {game.background_image ? (
                              <Link href={`/games/${game.id}`}>
                                {/* eslint-disable-next-line @next/next/no-img-element */}
                                <img src={game.background_image} alt={game.name} className="h-44 w-full object-cover" />
                              </Link>
                            ) : (
                              <div className="flex h-44 items-center justify-center bg-slate-800 text-slate-400">No image</div>
                            )}
                            <div className="p-3">
                              <div className="flex items-center justify-between gap-2">
                                <p className="text-base font-semibold text-white">{game.name}</p>
                                <Link href={`/games/${game.id}`} className="text-xs font-semibold text-cyan-300 hover:text-cyan-200">
                                  View
                                </Link>
                              </div>
                              <p className="text-xs text-slate-400">score {game.recommendation_score} | rating {game.rating ?? 'N/A'}</p>
                              <p className="mt-1 text-xs text-slate-300">
                                Genres: {(game.genres ?? []).slice(0, 2).map((genre) => genre.name).join(', ') || 'N/A'}
                              </p>
                              <p className="mt-2 text-sm leading-6 text-cyan-200">{game.recommendation_explanation}</p>
                            </div>
                          </article>
                        ))}
                      </div>
                    </div>
                  )}
                </div>

                <div className="rounded-lg border border-slate-700 bg-slate-800/50 p-3">
                  <h3 className="text-sm font-semibold text-cyan-300">Review-Based Similarity</h3>
                  <p className="mt-1 text-xs text-slate-400">Games prioritized by genres and platforms from your highest-rated reviews.</p>
                  {reviewBasedGames.length === 0 && <p className="mt-2 text-xs text-slate-400">No review-based game recommendations available yet.</p>}
                  {reviewBasedGames.length > 0 && (
                    <div
                      ref={reviewRecScrollerRef}
                      className="mt-2 overflow-x-auto"
                      onWheel={(event) => handleRecommendationWheel(event, reviewRecScrollerRef)}
                    >
                      <div className="flex w-max gap-4">
                        {reviewBasedGames.map((game) => (
                          <article key={`review-recommended-game-${game.id}`} className="w-72 shrink-0 overflow-hidden rounded-lg border border-slate-700 bg-slate-900/50">
                            {game.background_image ? (
                              <Link href={`/games/${game.id}`}>
                                {/* eslint-disable-next-line @next/next/no-img-element */}
                                <img src={game.background_image} alt={game.name} className="h-44 w-full object-cover" />
                              </Link>
                            ) : (
                              <div className="flex h-44 items-center justify-center bg-slate-800 text-slate-400">No image</div>
                            )}
                            <div className="p-3">
                              <div className="flex items-center justify-between gap-2">
                                <p className="text-base font-semibold text-white">{game.name}</p>
                                <Link href={`/games/${game.id}`} className="text-xs font-semibold text-cyan-300 hover:text-cyan-200">
                                  View
                                </Link>
                              </div>
                              <p className="text-xs text-slate-400">
                                score {game.recommendation_score} | rating {game.rating ?? 'N/A'}
                              </p>
                              <p className="mt-1 text-xs text-slate-300">
                                Genres: {(game.genres ?? []).slice(0, 2).map((genre) => genre.name).join(', ') || 'N/A'}
                              </p>
                              <p className="mt-2 text-sm leading-6 text-cyan-200">{game.recommendation_explanation}</p>
                            </div>
                          </article>
                        ))}
                      </div>
                    </div>
                  )}
                </div>
              </div>
            </div>
          )}

          {postsQuery.isLoading && <p className="text-slate-300">Loading posts...</p>}
          {!postsQuery.isLoading && (postsQuery.data?.data?.length ?? 0) === 0 && (
            <div className="rounded-xl border border-dashed border-slate-600 p-6 text-slate-300">No posts yet.</div>
          )}

          <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            {postsQuery.data?.data?.map((post) => {
              const isFavorited = Boolean(post.is_favorited);
              const avatarUrl = resolveImageUrl(post.user?.image);
              return (
                <article key={post.id} className="relative rounded-xl border border-slate-700 bg-slate-800/80 p-3">
                  <div className="absolute right-3 top-3 flex items-center gap-2">
                    <button
                      type="button"
                      onClick={() => toggleLikePost.mutate(post.id)}
                      className="rounded-full border border-rose-300/40 bg-black/50 px-2 py-1 text-xs font-semibold text-rose-300 hover:bg-black/70"
                    >
                      {post.is_liked ? '♥' : '♡'} {post.likes_count ?? 0}
                    </button>
                    <button
                      type="button"
                      onClick={() => toggleFavoritePost.mutate({ postId: post.id, isFavorited })}
                      className="rounded-full bg-black/60 px-2 py-1 text-lg leading-none text-yellow-300 hover:bg-black/80"
                    >
                      {isFavorited ? '★' : '☆'}
                    </button>
                  </div>

                  <div className="flex items-center gap-2 text-[11px] text-slate-400">
                    {avatarUrl ? (
                      // eslint-disable-next-line @next/next/no-img-element
                      <img
                        src={avatarUrl}
                        alt={post.user?.name ? `${post.user.name} avatar` : 'User avatar'}
                        className="h-7 w-7 rounded-full object-cover ring-1 ring-slate-700"
                      />
                    ) : (
                      <span className="inline-flex h-7 w-7 items-center justify-center rounded-full bg-slate-700 text-[10px] font-semibold text-slate-200">
                        {(post.user?.name || post.user?.username || 'U').slice(0, 2).toUpperCase()}
                      </span>
                    )}
                    <span>{post.user ? `${post.user.name} (@${post.user.username})` : 'Unknown user'}</span>
                  </div>
                  <h3 className="mt-1 pr-24 text-2xl font-bold text-white">
                    <Link href={`/posts/${post.id}`} className="hover:text-cyan-300">
                      {post.title || 'Untitled post'}
                    </Link>
                  </h3>
                  {post.category?.name && (
                    <p className="mt-1 text-[11px] font-semibold uppercase tracking-wide text-cyan-300">
                      {post.category.name}
                    </p>
                  )}
                  {post.image_url && (
                    <Link href={`/posts/${post.id}`}>
                      {/* eslint-disable-next-line @next/next/no-img-element */}
                      <img src={post.image_url} alt={post.title} className="mt-2 h-44 w-full rounded-lg object-cover" />
                    </Link>
                  )}
                </article>
              );
            })}
          </div>

          <div className="mt-6 flex flex-col items-center justify-center gap-3 sm:flex-row">
            <button
              type="button"
              onClick={() => setPage((p) => Math.max(1, p - 1))}
              disabled={page <= 1}
              className="w-full rounded-full bg-gradient-to-r from-cyan-500 to-blue-600 px-6 py-2.5 text-base font-semibold text-white transition hover:shadow-lg hover:shadow-cyan-500/30 disabled:cursor-not-allowed disabled:from-slate-700 disabled:to-slate-700 sm:w-auto"
            >
              Previous
            </button>
            <span className="rounded-full border border-slate-700 bg-slate-950/70 px-5 py-2.5 text-base font-semibold text-slate-200">
              Page <span className="text-white">{page}</span> of <span className="text-white">{totalPages}</span>
            </span>
            <button
              type="button"
              onClick={() => setPage((p) => Math.min(totalPages, p + 1))}
              disabled={page >= totalPages}
              className="w-full rounded-full bg-gradient-to-r from-cyan-500 to-blue-600 px-6 py-2.5 text-base font-semibold text-white transition hover:shadow-lg hover:shadow-cyan-500/30 disabled:cursor-not-allowed disabled:from-slate-700 disabled:to-slate-700 sm:w-auto"
            >
              Next
            </button>
          </div>
        </section>
      </div>
    </div>
  );
}
