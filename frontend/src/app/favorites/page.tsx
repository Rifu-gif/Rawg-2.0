'use client';

import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useEffect } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { api } from '@/lib/api';
import { useAuthToken } from '@/lib/auth';
import type { Game, Paginated, UserPost } from '@/lib/types';

export default function FavoritesPage() {
  const router = useRouter();
  const queryClient = useQueryClient();
  const token = useAuthToken();
  const isAuthenticated = Boolean(token);

  useEffect(() => {
    if (!isAuthenticated) {
      router.replace('/auth/login');
    }
  }, [isAuthenticated, router]);

  const favoriteGamesQuery = useQuery({
    queryKey: ['favorites'],
    queryFn: async () => (await api.get<Paginated<Game>>('/favorites')).data,
    enabled: isAuthenticated,
  });

  const favoritePostsQuery = useQuery({
    queryKey: ['favorites', 'posts'],
    queryFn: async () => (await api.get<Paginated<UserPost>>('/favorites/posts')).data,
    enabled: isAuthenticated,
  });

  const toggleFavoriteGame = useMutation({
    mutationFn: async (gameId: number) => {
      const isFavorited = favoriteGamesQuery.data?.data?.some((game) => game.id === gameId);
      if (isFavorited) await api.delete(`/favorites/games/${gameId}`);
      else await api.post(`/favorites/games/${gameId}`);
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['favorites'] });
      queryClient.invalidateQueries({ queryKey: ['games'] });
    },
  });

  const toggleFavoritePost = useMutation({
    mutationFn: async ({ postId, isFavorited }: { postId: number; isFavorited: boolean }) => {
      if (isFavorited) await api.delete(`/favorites/posts/${postId}`);
      else await api.post(`/favorites/posts/${postId}`);
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['favorites', 'posts'] });
      queryClient.invalidateQueries({ queryKey: ['posts', 'mine', 'profile'] });
      queryClient.invalidateQueries({ queryKey: ['posts', 'all'] });
    },
  });

  const toggleLikePost = useMutation({
    mutationFn: async (postId: number) => api.post(`/posts/${postId}/like`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['favorites', 'posts'] });
      queryClient.invalidateQueries({ queryKey: ['posts', 'mine', 'profile'] });
      queryClient.invalidateQueries({ queryKey: ['posts', 'all'] });
    },
  });

  if (!isAuthenticated) return null;

  const favoritePostIds = new Set((favoritePostsQuery.data?.data ?? []).map((post) => post.id));

  const renderPostCard = (post: UserPost, forcedFavorited?: boolean) => {
    const isFavorited = forcedFavorited ?? (favoritePostIds.has(post.id) || Boolean(post.is_favorited));
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
        <p className="text-[11px] text-slate-400">{post.user ? `${post.user.name} (@${post.user.username})` : 'Unknown user'}</p>
        <h3 className="mt-1 pr-24 text-2xl font-bold text-white">
          <Link href={`/posts/${post.id}`} className="hover:text-cyan-300">
            {post.title || 'Untitled post'}
          </Link>
        </h3>
        {post.category?.name && <p className="mt-1 text-[11px] font-semibold uppercase tracking-wide text-cyan-300">{post.category.name}</p>}
        {post.image_url && (
          <Link href={`/posts/${post.id}`}>
            {/* eslint-disable-next-line @next/next/no-img-element */}
            <img src={post.image_url} alt={post.title} className="mt-2 h-44 w-full rounded-lg object-cover" />
          </Link>
        )}
      </article>
    );
  };

  return (
    <div className="min-h-screen bg-slate-950 px-4 py-8">
      <div className="mx-auto w-full max-w-7xl space-y-6">
        <section className="rounded-2xl border border-slate-700 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 px-6 py-7 shadow-xl">
          <h1 className="text-4xl font-black tracking-tight text-white">Your Favorites</h1>
          <p className="mt-2 text-slate-300">Favorite games and posts in one place.</p>
        </section>

        <section className="rounded-2xl border border-slate-700 bg-slate-900/80 p-6 shadow-lg">
          <h2 className="text-2xl font-bold text-white">Favorite Games</h2>
          <div className="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            {(favoriteGamesQuery.data?.data?.length ?? 0) === 0 && (
              <div className="rounded-xl border border-dashed border-slate-600 p-6 text-slate-300">No favorite games yet.</div>
            )}

            {favoriteGamesQuery.data?.data?.map((game) => (
              <article key={game.id} className="relative rounded-xl border border-slate-700 bg-slate-800/80 p-3">
                <button
                  type="button"
                  onClick={() => toggleFavoriteGame.mutate(game.id)}
                  className="absolute right-3 top-3 rounded-full bg-black/60 px-2 py-1 text-lg leading-none text-yellow-300 hover:bg-black/80"
                >
                  ★
                </button>
                <div className="h-44 overflow-hidden rounded-lg bg-slate-700">
                  {game.background_image ? (
                    // eslint-disable-next-line @next/next/no-img-element
                    <img src={game.background_image} alt={game.name} className="h-full w-full object-cover" />
                  ) : null}
                </div>
                <h3 className="mt-3 text-lg font-semibold text-white">{game.name}</h3>
                <Link href={`/games/${game.id}`} className="mt-3 inline-block rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white">
                  View Game
                </Link>
              </article>
            ))}
          </div>
        </section>

        <section className="rounded-2xl border border-slate-700 bg-slate-900/80 p-6 shadow-lg">
          <h2 className="text-2xl font-bold text-white">Favorite Posts</h2>
          <p className="mt-1 text-sm text-slate-300">Open a post to read full content and comments.</p>
          <div className="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            {(favoritePostsQuery.data?.data?.length ?? 0) === 0 && (
              <div className="rounded-xl border border-dashed border-slate-600 p-6 text-slate-300">No favorite posts yet.</div>
            )}
            {favoritePostsQuery.data?.data?.map((post) => renderPostCard(post, true))}
          </div>
        </section>
      </div>
    </div>
  );
}
