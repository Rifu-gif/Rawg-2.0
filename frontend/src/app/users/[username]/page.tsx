'use client';

import { use, useState } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { api } from '@/lib/api';
import { useAuthToken } from '@/lib/auth';
import type { AuthUser, PublicUserProfile } from '@/lib/types';

type PageProps = { params: Promise<{ username: string }> };

export default function PublicUserPage({ params }: PageProps) {
  const { username } = use(params);
  const token = useAuthToken();
  const isAuthenticated = Boolean(token);
  const queryClient = useQueryClient();

  const [avatarLoadFailed, setAvatarLoadFailed] = useState(false);
  const [commentDrafts, setCommentDrafts] = useState<Record<number, string>>({});

  const meQuery = useQuery({
    queryKey: ['me'],
    queryFn: async () => (await api.get<AuthUser>('/auth/me')).data,
    enabled: isAuthenticated,
  });

  const profileQuery = useQuery({
    queryKey: ['user-profile', username],
    queryFn: async () => (await api.get<PublicUserProfile>(`/users/${username}`)).data,
  });

  const toggleFollow = useMutation({
    mutationFn: async (targetUserId: number) => api.post(`/users/${targetUserId}/follow`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['user-profile', username] });
      queryClient.invalidateQueries({ queryKey: ['me'] });
    },
  });

  const toggleFavoritePost = useMutation({
    mutationFn: async ({ postId, isFavorited }: { postId: number; isFavorited: boolean }) => {
      if (isFavorited) await api.delete(`/favorites/posts/${postId}`);
      else await api.post(`/favorites/posts/${postId}`);
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['user-profile', username] });
      queryClient.invalidateQueries({ queryKey: ['favorites', 'posts'] });
    },
  });

  const toggleLikePost = useMutation({
    mutationFn: async (postId: number) => api.post(`/posts/${postId}/like`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['user-profile', username] });
      queryClient.invalidateQueries({ queryKey: ['favorites', 'posts'] });
    },
  });

  const addComment = useMutation({
    mutationFn: async ({ postId, content }: { postId: number; content: string }) => {
      await api.post(`/posts/${postId}/comments`, { content });
    },
    onSuccess: (_data, variables) => {
      setCommentDrafts((prev) => ({ ...prev, [variables.postId]: '' }));
      queryClient.invalidateQueries({ queryKey: ['user-profile', username] });
    },
  });

  const deleteComment = useMutation({
    mutationFn: async ({ postId, commentId }: { postId: number; commentId: number }) => {
      await api.delete(`/posts/${postId}/comments/${commentId}`);
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['user-profile', username] });
    },
  });

  function resolveImageUrl(image: string | null | undefined) {
    if (!image || image.trim() === '') return null;
    if (/^https?:\/\//i.test(image)) return image;
    const apiBase = process.env.NEXT_PUBLIC_API_BASE_URL || 'http://laravel-project.test/api';
    const appBase = apiBase.replace(/\/api\/?$/, '');
    return `${appBase}/storage/${image}`;
  }

  if (profileQuery.isLoading) {
    return <div className="min-h-screen bg-slate-950 p-6 text-white">Loading user...</div>;
  }

  if (profileQuery.isError || !profileQuery.data) {
    return <div className="min-h-screen bg-slate-950 p-6 text-white">User not found.</div>;
  }

  const user = profileQuery.data;
  const avatarUrl = resolveImageUrl(user.image);
  const initials = (user.name || user.username || 'U').slice(0, 2).toUpperCase();
  const currentUserId = meQuery.data?.id ?? null;
  const isOwnProfile = currentUserId === user.id;

  return (
    <div className="min-h-screen bg-slate-950 px-4 py-6">
      <div className="mx-auto max-w-7xl">
        <section className="rounded-3xl border border-blue-500/30 bg-gradient-to-r from-[#1d4f92] to-[#2747bd] p-4 text-white">
          <div className="flex flex-col gap-4 md:flex-row md:items-start">
            {avatarUrl && !avatarLoadFailed ? (
              // eslint-disable-next-line @next/next/no-img-element
              <img
                src={avatarUrl}
                alt={`${user.name} avatar`}
                onError={() => setAvatarLoadFailed(true)}
                className="h-32 w-32 rounded-2xl border-4 border-blue-300/40 object-cover"
              />
            ) : (
              <div className="flex h-32 w-32 items-center justify-center rounded-2xl border-4 border-blue-300/40 bg-slate-200 text-4xl font-bold text-slate-700">
                {initials}
              </div>
            )}
            <div className="flex-1">
              <h1 className="text-3xl font-bold">{user.name}</h1>
              <p className="text-xl font-semibold text-cyan-300">@{user.username}</p>
              <p className="mt-2 text-cyan-100">{user.bio || 'No bio yet.'}</p>
              <div className="mt-3 flex flex-wrap gap-2">
                <div className="min-w-[130px] rounded-lg border border-blue-400/40 bg-blue-800/20 px-3 py-2">
                  <p className="text-xl font-bold">{user.posts.length}</p>
                  <p className="text-sm text-cyan-200">POSTS</p>
                </div>
                <div className="min-w-[130px] rounded-lg border border-blue-400/40 bg-blue-800/20 px-3 py-2">
                  <p className="text-xl font-bold">{user.followers_count}</p>
                  <p className="text-sm text-cyan-200">FOLLOWERS</p>
                </div>
                <div className="min-w-[130px] rounded-lg border border-blue-400/40 bg-blue-800/20 px-3 py-2">
                  <p className="text-xl font-bold">{user.following_count}</p>
                  <p className="text-sm text-cyan-200">FOLLOWING</p>
                </div>
              </div>
              {isAuthenticated && !isOwnProfile && (
                <button
                  type="button"
                  onClick={() => toggleFollow.mutate(user.id)}
                  className={`mt-3 rounded-lg px-4 py-2 text-sm font-semibold ${
                    user.is_following
                      ? 'border border-slate-300 text-slate-100 hover:bg-slate-800'
                      : 'bg-cyan-500 text-slate-950 hover:bg-cyan-400'
                  }`}
                >
                  {user.is_following ? 'Following' : 'Follow'}
                </button>
              )}
            </div>
          </div>
        </section>

        <section className="mt-6 rounded-3xl border border-slate-700 bg-slate-900/50 p-6">
          <h2 className="text-2xl font-bold text-white">Posts</h2>
          <div className="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            {user.posts.length === 0 && (
              <div className="rounded-2xl border border-dashed border-slate-600 p-6 text-slate-300">No posts yet.</div>
            )}
            {user.posts.map((post) => (
              <article key={post.id} className="relative rounded-xl border border-slate-700 bg-slate-800/70 p-2.5">
                {isAuthenticated && (
                  <button
                    type="button"
                    onClick={() => toggleFavoritePost.mutate({ postId: post.id, isFavorited: Boolean(post.is_favorited) })}
                    className="absolute right-2 top-2 rounded-full bg-black/60 px-2 py-1 text-lg leading-none text-yellow-300 hover:bg-black/80"
                  >
                    {post.is_favorited ? '★' : '☆'}
                  </button>
                )}
                <p className="text-[11px] text-slate-400">{post.created_at ? new Date(post.created_at).toLocaleDateString() : 'No date'}</p>
                <h3 className="mt-1 text-base font-semibold text-white">{post.title || 'Untitled post'}</h3>
                {post.category?.name && (
                  <p className="mt-1 text-[11px] font-semibold uppercase tracking-wide text-cyan-300">{post.category.name}</p>
                )}
                {post.image_url && (
                  // eslint-disable-next-line @next/next/no-img-element
                  <img src={post.image_url} alt={post.title} className="mt-2 h-40 w-full rounded-lg object-cover" />
                )}
                <p className="mt-2 text-xs text-slate-300">{post.content || 'No content provided.'}</p>

                {isAuthenticated ? (
                  <>
                    <div className="mt-2">
                      <button
                        type="button"
                        onClick={() => toggleLikePost.mutate(post.id)}
                        className="rounded-lg border border-slate-500 px-3 py-1 text-xs font-semibold text-slate-200 hover:bg-slate-700"
                      >
                        {post.is_liked ? 'Unlike' : 'Like'} ({post.likes_count ?? 0})
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
                            <p className="text-xs text-slate-200">{comment.content}</p>
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
                  </>
                ) : (
                  <p className="mt-2 text-xs text-slate-400">Sign in to like and comment.</p>
                )}
              </article>
            ))}
          </div>
        </section>
      </div>
    </div>
  );
}
