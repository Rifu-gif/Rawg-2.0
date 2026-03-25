'use client';

import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useEffect, useState } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { api } from '@/lib/api';
import { useAuthToken } from '@/lib/auth';
import type { AuthUser, Game, Paginated, UserPost } from '@/lib/types';

export default function FavoritesPage() {
  const router = useRouter();
  const queryClient = useQueryClient();
  const token = useAuthToken();
  const isAuthenticated = Boolean(token);
  const [commentDrafts, setCommentDrafts] = useState<Record<number, string>>({});

  useEffect(() => {
    if (!isAuthenticated) {
      router.replace('/auth/login');
    }
  }, [isAuthenticated, router]);

  const meQuery = useQuery({
    queryKey: ['me'],
    queryFn: async () => (await api.get<AuthUser>('/auth/me')).data,
    enabled: isAuthenticated,
  });

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
    },
  });

  const toggleLikePost = useMutation({
    mutationFn: async (postId: number) => api.post(`/posts/${postId}/like`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['favorites', 'posts'] });
      queryClient.invalidateQueries({ queryKey: ['posts', 'mine', 'profile'] });
    },
  });

  const addComment = useMutation({
    mutationFn: async ({ postId, content }: { postId: number; content: string }) => {
      await api.post(`/posts/${postId}/comments`, { content });
    },
    onSuccess: (_data, variables) => {
      setCommentDrafts((prev) => ({ ...prev, [variables.postId]: '' }));
      queryClient.invalidateQueries({ queryKey: ['favorites', 'posts'] });
      queryClient.invalidateQueries({ queryKey: ['posts', 'mine', 'profile'] });
    },
  });

  const deleteComment = useMutation({
    mutationFn: async ({ postId, commentId }: { postId: number; commentId: number }) => {
      await api.delete(`/posts/${postId}/comments/${commentId}`);
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['favorites', 'posts'] });
      queryClient.invalidateQueries({ queryKey: ['posts', 'mine', 'profile'] });
    },
  });

  if (!isAuthenticated) return null;

  const favoritePostIds = new Set((favoritePostsQuery.data?.data ?? []).map((post) => post.id));
  const currentUserId = meQuery.data?.id ?? null;

  const renderPostCard = (post: UserPost, forcedFavorited?: boolean) => {
    const isFavorited = forcedFavorited ?? (favoritePostIds.has(post.id) || Boolean(post.is_favorited));
    return (
      <article key={post.id} className="relative rounded-xl border border-slate-700 bg-slate-800/80 p-3">
        <button
          type="button"
          onClick={() => toggleFavoritePost.mutate({ postId: post.id, isFavorited })}
          className="absolute right-3 top-3 rounded-full bg-black/60 px-2 py-1 text-lg leading-none text-yellow-300 hover:bg-black/80"
        >
          {isFavorited ? '★' : '☆'}
        </button>
        <p className="text-[11px] text-slate-400">{post.user ? `${post.user.name} (@${post.user.username})` : 'Unknown user'}</p>
        <h3 className="mt-1 pr-8 text-2xl font-bold text-white">{post.title || 'Untitled post'}</h3>
        {post.category?.name && <p className="mt-1 text-[11px] font-semibold uppercase tracking-wide text-cyan-300">{post.category.name}</p>}
        {post.image_url && (
          // eslint-disable-next-line @next/next/no-img-element
          <img src={post.image_url} alt={post.title} className="mt-2 h-40 w-full rounded-lg object-cover" />
        )}
        <p className="mt-2 text-xs text-slate-300">{post.content || 'No content provided.'}</p>

        <div className="mt-2">
          <button
            type="button"
            onClick={() => toggleLikePost.mutate(post.id)}
            className="rounded-lg border border-slate-500 px-3 py-1 text-xs font-semibold text-slate-200 hover:bg-slate-700"
          >
            👍 {post.is_liked ? 'Unlike' : 'Like'} ({post.likes_count ?? 0})
          </button>
        </div>

        <div className="mt-2 space-y-2 rounded-lg border border-slate-700 bg-slate-900/50 p-2">
          <p className="text-xs font-semibold text-cyan-300">Comments</p>
          {(post.comments ?? []).map((comment) => {
            const canDeleteComment = comment.user?.id === currentUserId || post.user?.id === currentUserId;
            return (
              <div key={comment.id} className="rounded border border-slate-700 bg-slate-800/60 px-2 py-1.5">
                <div className="flex items-center justify-between gap-2">
                  <p className="text-xs text-slate-300">{comment.user ? `${comment.user.name} (@${comment.user.username})` : 'User'}</p>
                </div>
                <p className="text-sm text-slate-200">{comment.content}</p>
              </div>
            );
          })}
          <form
            className="flex gap-2"
            onSubmit={(event) => {
              event.preventDefault();
              const content = commentDrafts[post.id]?.trim();
              if (!content) return;
              addComment.mutate({ postId: post.id, content });
            }}
          >
            <input
              value={commentDrafts[post.id] ?? ''}
              onChange={(event) => setCommentDrafts((prev) => ({ ...prev, [post.id]: event.target.value }))}
              placeholder="Write a comment..."
              className="flex-1 rounded-lg border border-slate-600 bg-slate-900/60 px-3 py-1.5 text-xs text-white"
            />
            <button type="submit" className="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white">
              Post
            </button>
          </form>
        </div>
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
