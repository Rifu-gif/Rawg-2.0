'use client';

import { use, useState } from 'react';
import Link from 'next/link';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { api } from '@/lib/api';
import { tokenStore } from '@/lib/auth';
import type { AuthUser, UserPost } from '@/lib/types';

type PageProps = { params: Promise<{ id: string }> };

export default function PostDetailPage({ params }: PageProps) {
  const { id } = use(params);
  const postId = Number(id);
  const queryClient = useQueryClient();
  const token = tokenStore.get();
  const [commentDraft, setCommentDraft] = useState('');

  const meQuery = useQuery({
    queryKey: ['me'],
    queryFn: async () => (await api.get<AuthUser>('/auth/me')).data,
    enabled: Boolean(token),
  });

  const postQuery = useQuery({
    queryKey: ['post', postId],
    queryFn: async () => (await api.get<UserPost>(`/posts/${postId}`)).data,
    enabled: Number.isFinite(postId),
  });

  const toggleFavoritePost = useMutation({
    mutationFn: async ({ isFavorited }: { isFavorited: boolean }) => {
      if (isFavorited) await api.delete(`/favorites/posts/${postId}`);
      else await api.post(`/favorites/posts/${postId}`);
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['post', postId] });
      queryClient.invalidateQueries({ queryKey: ['posts', 'all'] });
      queryClient.invalidateQueries({ queryKey: ['favorites', 'posts'] });
    },
  });

  const toggleLikePost = useMutation({
    mutationFn: async () => api.post(`/posts/${postId}/like`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['post', postId] });
      queryClient.invalidateQueries({ queryKey: ['posts', 'all'] });
      queryClient.invalidateQueries({ queryKey: ['favorites', 'posts'] });
    },
  });

  const addComment = useMutation({
    mutationFn: async (content: string) => api.post(`/posts/${postId}/comments`, { content }),
    onSuccess: () => {
      setCommentDraft('');
      queryClient.invalidateQueries({ queryKey: ['post', postId] });
      queryClient.invalidateQueries({ queryKey: ['posts', 'all'] });
      queryClient.invalidateQueries({ queryKey: ['favorites', 'posts'] });
    },
  });

  const deleteComment = useMutation({
    mutationFn: async (commentId: number) => api.delete(`/posts/${postId}/comments/${commentId}`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['post', postId] });
      queryClient.invalidateQueries({ queryKey: ['posts', 'all'] });
      queryClient.invalidateQueries({ queryKey: ['favorites', 'posts'] });
    },
  });

  const post = postQuery.data;
  const wordCount = post?.content ? post.content.trim().split(/\s+/).filter(Boolean).length : 0;
  const readMinutes = Math.max(1, Math.ceil(wordCount / 180));

  if (postQuery.isLoading) {
    return <div className="rounded-3xl border border-[var(--line)] p-10">Loading post...</div>;
  }

  if (!post) {
    return <div className="rounded-3xl border border-[var(--line)] p-10">Post not found.</div>;
  }

  const currentUserId = meQuery.data?.id ?? null;
  const comments = post.comments ?? [];

  return (
    <div className="space-y-10">
      <section className="grid gap-8 lg:grid-cols-[1.2fr_0.8fr]">
        <div className="overflow-hidden rounded-3xl border border-[var(--line)] bg-white">
          {post.image_url ? (
            // eslint-disable-next-line @next/next/no-img-element
            <img src={post.image_url} alt={post.title} className="h-80 w-full object-cover" />
          ) : (
            <div className="flex h-80 items-center justify-center bg-[var(--ink-2)] text-white/70">No image</div>
          )}

          <div className="space-y-4 p-6">
            <div className="flex items-center justify-between gap-4">
              <h1 className="text-3xl font-semibold [font-family:var(--font-display)]">{post.title || 'Untitled post'}</h1>
              <span className="rounded-full bg-[var(--brand-3)] px-4 py-2 text-sm font-semibold">
                {post.likes_count ?? 0} likes
              </span>
            </div>
            <p className="text-sm text-[var(--ink-3)]">
              By {post.user?.name ?? 'Unknown'} (@{post.user?.username ?? 'unknown'}) {post.published_at ? `| ${new Date(post.published_at).toLocaleDateString()}` : ''}
            </p>
            <div className="flex flex-wrap gap-2">
              <span className="rounded-full border border-[var(--line)] px-3 py-1 text-xs">
                {post.category?.name ?? 'Uncategorized'}
              </span>
              <span className="rounded-full bg-[var(--ink-2)] px-3 py-1 text-xs text-white">
                {readMinutes} min read
              </span>
            </div>
            <p className="text-sm leading-7 text-[var(--ink-2)] whitespace-pre-wrap">
              {post.content || 'No content provided.'}
            </p>
          </div>
        </div>

        <aside className="space-y-4">
          <div className="glass-card rounded-3xl p-6">
            <h2 className="text-lg font-semibold">Quick stats</h2>
            <div className="mt-4 space-y-2 text-sm text-[var(--ink-3)]">
              <p>Category: {post.category?.name ?? 'N/A'}</p>
              <p>Likes: {post.likes_count ?? 0}</p>
              <p>Comments: {comments.length}</p>
              <p>Reading time: {readMinutes} min</p>
            </div>
            <div className="mt-5 space-y-2">
              <button
                className="w-full rounded-full border border-[var(--line)] px-4 py-2 text-sm"
                onClick={() => toggleLikePost.mutate()}
                disabled={!token || toggleLikePost.isPending}
              >
                {post.is_liked ? 'Unlike post' : 'Like post'}
              </button>
              <button
                className="w-full rounded-full border border-[var(--line)] px-4 py-2 text-sm"
                onClick={() => toggleFavoritePost.mutate({ isFavorited: Boolean(post.is_favorited) })}
                disabled={!token || toggleFavoritePost.isPending}
              >
                {post.is_favorited ? 'Remove from favorites' : 'Add to favorites'}
              </button>
            </div>
            {!token && (
              <p className="mt-3 text-sm text-[var(--ink-3)]">
                Sign in to interact with this post. <Link className="text-[var(--brand-1)]" href="/auth/login">Login</Link>
              </p>
            )}
          </div>

          <div className="glass-card rounded-3xl p-6">
            <h2 className="text-lg font-semibold">Add comment</h2>
            {!token && (
              <p className="mt-3 text-sm text-[var(--ink-3)]">
                Sign in to comment. <Link className="text-[var(--brand-1)]" href="/auth/login">Login</Link>
              </p>
            )}
            {token && (
              <form
                className="mt-4 space-y-3"
                onSubmit={(event) => {
                  event.preventDefault();
                  const content = commentDraft.trim();
                  if (!content) return;
                  addComment.mutate(content);
                }}
              >
                <textarea
                  value={commentDraft}
                  onChange={(event) => setCommentDraft(event.target.value)}
                  placeholder="Write your thoughts..."
                  className="min-h-[140px] w-full rounded-xl border border-[var(--line)] px-3 py-2"
                />
                <button
                  type="submit"
                  className="rounded-full bg-[var(--ink-1)] px-4 py-2 text-sm font-semibold text-white"
                  disabled={addComment.isPending}
                >
                  Post comment
                </button>
              </form>
            )}
          </div>
        </aside>
      </section>

      <section className="space-y-4">
        <h2 className="text-2xl font-semibold [font-family:var(--font-display)]">Comments</h2>
        <div className="space-y-4">
          {comments.length ? (
            comments.map((comment) => {
              const canDelete = comment.user?.id === currentUserId || post.user?.id === currentUserId;
              return (
                <div key={comment.id} className="rounded-2xl border border-[var(--line)] bg-white p-5">
                  <div className="flex items-center justify-between gap-3">
                    <p className="text-sm font-semibold">
                      {comment.user?.name ?? 'User'} (@{comment.user?.username ?? 'unknown'})
                    </p>
                    {canDelete && token && (
                      <button
                        type="button"
                        className="rounded-full border border-[var(--line)] px-3 py-1 text-xs"
                        onClick={() => deleteComment.mutate(comment.id)}
                      >
                        Delete
                      </button>
                    )}
                  </div>
                  <p className="mt-2 text-sm text-[var(--ink-3)]">{comment.content}</p>
                </div>
              );
            })
          ) : (
            <div className="rounded-2xl border border-dashed border-[var(--line)] p-6 text-sm text-[var(--ink-3)]">
              No comments yet. Be the first to add one.
            </div>
          )}
        </div>
      </section>
    </div>
  );
}
