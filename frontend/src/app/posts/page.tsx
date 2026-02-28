'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { api } from '@/lib/api';
import { useAuthToken } from '@/lib/auth';
import type { AuthUser, Paginated, UserPost } from '@/lib/types';

export default function PostsPage() {
  const router = useRouter();
  const queryClient = useQueryClient();
  const token = useAuthToken();
  const isAuthenticated = Boolean(token);
  const [page, setPage] = useState(1);
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

  const postsQuery = useQuery({
    queryKey: ['posts', 'all', page],
    queryFn: async () => (await api.get<Paginated<UserPost>>('/posts', { params: { page } })).data,
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

  const addComment = useMutation({
    mutationFn: async ({ postId, content }: { postId: number; content: string }) => {
      await api.post(`/posts/${postId}/comments`, { content });
    },
    onSuccess: (_data, variables) => {
      setCommentDrafts((prev) => ({ ...prev, [variables.postId]: '' }));
      queryClient.invalidateQueries({ queryKey: ['posts', 'all'] });
      queryClient.invalidateQueries({ queryKey: ['favorites', 'posts'] });
      queryClient.invalidateQueries({ queryKey: ['posts', 'mine', 'profile'] });
    },
  });

  const deleteComment = useMutation({
    mutationFn: async ({ postId, commentId }: { postId: number; commentId: number }) => {
      await api.delete(`/posts/${postId}/comments/${commentId}`);
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['posts', 'all'] });
      queryClient.invalidateQueries({ queryKey: ['favorites', 'posts'] });
      queryClient.invalidateQueries({ queryKey: ['posts', 'mine', 'profile'] });
    },
  });

  if (!isAuthenticated) return null;

  const currentUserId = meQuery.data?.id ?? null;
  const totalPages = postsQuery.data?.last_page ?? 1;

  function resolveImageUrl(image: string | null | undefined) {
    if (!image || image.trim() === '') return null;
    if (/^https?:\/\//i.test(image)) return image;
    const apiBase = process.env.NEXT_PUBLIC_API_BASE_URL || 'http://laravel-project.test/api';
    const appBase = apiBase.replace(/\/api\/?$/, '');
    return `${appBase}/storage/${image}`;
  }

  return (
    <div className="min-h-screen bg-slate-950 px-4 py-8">
      <div className="mx-auto w-full max-w-7xl space-y-6">
        <section className="rounded-2xl border border-slate-700 bg-slate-900/80 p-6 shadow-lg">
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
                  <button
                    type="button"
                    onClick={() => toggleFavoritePost.mutate({ postId: post.id, isFavorited })}
                    className="absolute right-3 top-3 rounded-full bg-black/60 px-2 py-1 text-lg leading-none text-yellow-300 hover:bg-black/80"
                  >
                    {isFavorited ? '★' : '☆'}
                  </button>
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
                  <h3 className="mt-1 pr-8 text-2xl font-bold text-white">{post.title || 'Untitled post'}</h3>
                  {post.category?.name && (
                    <p className="mt-1 text-[11px] font-semibold uppercase tracking-wide text-cyan-300">
                      {post.category.name}
                    </p>
                  )}
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
                            <p className="text-xs text-slate-300">
                              {comment.user ? `${comment.user.name} (@${comment.user.username})` : 'User'}
                            </p>
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
                        onChange={(event) =>
                          setCommentDrafts((prev) => ({ ...prev, [post.id]: event.target.value }))
                        }
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
            })}
          </div>

          <div className="mt-6 flex flex-col items-center justify-center gap-3 sm:flex-row">
            <button
              type="button"
              onClick={() => setPage((p) => Math.max(1, p - 1))}
              disabled={page <= 1}
              className="w-full rounded-full bg-gradient-to-r from-cyan-500 to-blue-600 px-6 py-2.5 text-base font-semibold text-white transition hover:shadow-lg hover:shadow-cyan-500/30 disabled:cursor-not-allowed disabled:from-slate-700 disabled:to-slate-700 sm:w-auto"
            >
              ← Previous
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
              Next →
            </button>
          </div>
        </section>
      </div>
    </div>
  );
}
