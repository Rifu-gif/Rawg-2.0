'use client';

import { useEffect, useMemo, useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { api } from '@/lib/api';
import { useAuthToken } from '@/lib/auth';
import type { AuthUser, Paginated, PostCategory, UserPost } from '@/lib/types';

type EditFormState = {
  title: string;
  content: string;
  categoryId: string;
  imageFile: File | null;
};

export default function ProfilePage() {
  const router = useRouter();
  const queryClient = useQueryClient();
  const token = useAuthToken();
  const isAuthenticated = Boolean(token);
  const [avatarLoadFailed, setAvatarLoadFailed] = useState(false);
  const [editingPostId, setEditingPostId] = useState<number | null>(null);
  const [editForm, setEditForm] = useState<EditFormState>({ title: '', content: '', categoryId: '', imageFile: null });

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

  const categoriesQuery = useQuery({
    queryKey: ['post-categories'],
    queryFn: async () => (await api.get<PostCategory[]>('/post-categories')).data,
    enabled: isAuthenticated,
  });

  const postsQuery = useQuery({
    queryKey: ['posts', 'mine', 'profile'],
    queryFn: async () => (await api.get<Paginated<UserPost>>('/posts/mine')).data,
    enabled: isAuthenticated,
  });

  const myPosts = useMemo(() => {
    if (!postsQuery.data?.data) return [];
    return [...postsQuery.data.data].sort((a, b) => Date.parse(b.created_at ?? '') - Date.parse(a.created_at ?? ''));
  }, [postsQuery.data]);

  const reviewedGamesCount = useMemo(() => postsQuery.data?.total ?? myPosts.length, [myPosts.length, postsQuery.data?.total]);

  const toggleFavoritePost = useMutation({
    mutationFn: async ({ postId, isFavorited }: { postId: number; isFavorited: boolean }) => {
      if (isFavorited) await api.delete(`/favorites/posts/${postId}`);
      else await api.post(`/favorites/posts/${postId}`);
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['posts', 'mine', 'profile'] });
      queryClient.invalidateQueries({ queryKey: ['favorites', 'posts'] });
      queryClient.invalidateQueries({ queryKey: ['posts', 'all'] });
    },
  });

  const toggleLikePost = useMutation({
    mutationFn: async (postId: number) => api.post(`/posts/${postId}/like`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['posts', 'mine', 'profile'] });
      queryClient.invalidateQueries({ queryKey: ['favorites', 'posts'] });
      queryClient.invalidateQueries({ queryKey: ['posts', 'all'] });
    },
  });

  const updatePost = useMutation({
    mutationFn: async ({ postId, form }: { postId: number; form: EditFormState }) => {
      const payload = new FormData();
      payload.append('title', form.title);
      payload.append('content', form.content);
      payload.append('category_id', form.categoryId);
      if (form.imageFile) payload.append('image', form.imageFile);
      await api.post(`/posts/${postId}?_method=PUT`, payload, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
    },
    onSuccess: () => {
      setEditingPostId(null);
      setEditForm({ title: '', content: '', categoryId: '', imageFile: null });
      queryClient.invalidateQueries({ queryKey: ['posts', 'mine', 'profile'] });
      queryClient.invalidateQueries({ queryKey: ['favorites', 'posts'] });
      queryClient.invalidateQueries({ queryKey: ['posts', 'all'] });
    },
  });

  const deletePost = useMutation({
    mutationFn: async (postId: number) => {
      await api.delete(`/posts/${postId}`);
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['posts', 'mine', 'profile'] });
      queryClient.invalidateQueries({ queryKey: ['favorites', 'posts'] });
      queryClient.invalidateQueries({ queryKey: ['posts', 'all'] });
    },
  });

  function resolveImageUrl(image: string | null | undefined) {
    if (!image || image.trim() === '') return null;
    if (/^https?:\/\//i.test(image)) return image;
    const apiBase = process.env.NEXT_PUBLIC_API_BASE_URL || 'http://laravel-project.test/api';
    const appBase = apiBase.replace(/\/api\/?$/, '');
    return `${appBase}/storage/${image}`;
  }

  const avatarUrl = resolveImageUrl(meQuery.data?.image);

  if (!isAuthenticated) return null;
  if (meQuery.isLoading) return null;

  const user = meQuery.data;
  if (!user) return null;

  const initials = (user.name || user.username || 'U').slice(0, 2).toUpperCase();
  const bioText = user.bio || 'This is your profile.';

  return (
    <div className="min-h-screen bg-gradient-to-r from-[#071224] via-[#10233d] to-[#0b1b30] px-4 py-4">
      <div className="mx-auto max-w-7xl">
        <section className="rounded-3xl border border-blue-500/30 bg-gradient-to-r from-[#1d4f92] to-[#2747bd] p-3 text-white shadow-2xl">
          <div className="flex flex-col gap-3 md:flex-row md:items-start">
            {avatarUrl && !avatarLoadFailed ? (
              // eslint-disable-next-line @next/next/no-img-element
              <img
                src={avatarUrl}
                alt={`${user.name} avatar`}
                className="h-44 w-44 overflow-hidden rounded-2xl border-4 border-blue-300/40 object-cover shadow-lg"
                onError={() => setAvatarLoadFailed(true)}
              />
            ) : (
              <div className="flex h-44 w-44 items-center justify-center overflow-hidden rounded-2xl border-4 border-blue-300/40 bg-slate-200 text-5xl font-bold text-slate-700 shadow-lg">
                {initials}
              </div>
            )}

            <div className="flex-1">
              <h1 className="text-3xl font-bold leading-none">{user.name}</h1>
              <p className="mt-1 text-xl font-semibold text-cyan-300">@{user.username}</p>
              <p className="mt-2 text-lg text-cyan-100">{bioText}</p>
              <div className="mt-3 grid gap-2 sm:grid-cols-3">
                <div className="rounded-lg border border-blue-400/40 bg-blue-800/20 px-3 py-2">
                  <p className="text-2xl font-bold leading-none">{reviewedGamesCount}</p>
                  <p className="mt-1 text-base font-semibold text-cyan-200">POSTS</p>
                </div>
                <div className="rounded-lg border border-blue-400/40 bg-blue-800/20 px-3 py-2">
                  <p className="text-2xl font-bold leading-none">{user.followers_count ?? 0}</p>
                  <p className="mt-1 text-base font-semibold text-cyan-200">FOLLOWERS</p>
                </div>
                <div className="rounded-lg border border-blue-400/40 bg-blue-800/20 px-3 py-2">
                  <p className="text-2xl font-bold leading-none">{user.following_count ?? 0}</p>
                  <p className="mt-1 text-base font-semibold text-cyan-200">FOLLOWING</p>
                </div>
              </div>
            </div>
          </div>
        </section>

        <section id="my-posts" className="mt-6 rounded-3xl border border-slate-700 bg-slate-900/50 p-6 shadow-xl">
          <h2 className="text-2xl font-bold text-white">My Posts</h2>
          <p className="mt-1 text-sm text-slate-300">Open a post to read full content and comments.</p>

          <div className="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            {myPosts.length === 0 && (
              <div className="rounded-2xl border border-dashed border-slate-600 p-8 text-base text-slate-300">No posts yet.</div>
            )}

            {myPosts.map((post) => {
              const isEditing = editingPostId === post.id;

              return (
                <article key={post.id} className="relative rounded-xl border border-slate-700 bg-slate-800/70 p-2.5">
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
                      onClick={() => toggleFavoritePost.mutate({ postId: post.id, isFavorited: Boolean(post.is_favorited) })}
                      className="rounded-full bg-black/60 px-2 py-1 text-lg leading-none text-yellow-300 hover:bg-black/80"
                      aria-label={post.is_favorited ? 'Unfavorite post' : 'Favorite post'}
                    >
                      {post.is_favorited ? '★' : '☆'}
                    </button>
                  </div>

                  {!isEditing ? (
                    <>
                      <p className="text-[11px] text-slate-400">{post.created_at ? new Date(post.created_at).toLocaleDateString() : 'No date'}</p>
                      <h3 className="mt-1 pr-24 text-base font-semibold text-white">
                        <Link href={`/posts/${post.id}`} className="hover:text-cyan-300">
                          {post.title || 'Untitled post'}
                        </Link>
                      </h3>
                      {post.category?.name && (
                        <p className="mt-1 text-[11px] font-semibold uppercase tracking-wide text-cyan-300">{post.category.name}</p>
                      )}
                      {post.image_url && (
                        <Link href={`/posts/${post.id}`}>
                          {/* eslint-disable-next-line @next/next/no-img-element */}
                          <img src={post.image_url} alt={post.title} className="mt-2 h-44 w-full rounded-lg object-cover" />
                        </Link>
                      )}
                    </>
                  ) : (
                    <form
                      className="space-y-2"
                      onSubmit={(event) => {
                        event.preventDefault();
                        updatePost.mutate({ postId: post.id, form: editForm });
                      }}
                    >
                      <input
                        value={editForm.title}
                        onChange={(event) => setEditForm((prev) => ({ ...prev, title: event.target.value }))}
                        className="w-full rounded-lg border border-slate-600 bg-slate-900/50 px-3 py-2 text-sm text-white"
                      />
                      <select
                        value={editForm.categoryId}
                        onChange={(event) => setEditForm((prev) => ({ ...prev, categoryId: event.target.value }))}
                        className="w-full rounded-lg border border-slate-600 bg-slate-900/50 px-3 py-2 text-sm text-white"
                      >
                        {categoriesQuery.data?.map((category) => (
                          <option key={category.id} value={category.id}>
                            {category.name}
                          </option>
                        ))}
                      </select>
                      <textarea
                        value={editForm.content}
                        onChange={(event) => setEditForm((prev) => ({ ...prev, content: event.target.value }))}
                        rows={5}
                        className="w-full rounded-lg border border-slate-600 bg-slate-900/50 px-3 py-2 text-sm text-white"
                      />
                      <input
                        type="file"
                        accept="image/*"
                        onChange={(event) =>
                          setEditForm((prev) => ({ ...prev, imageFile: event.target.files?.[0] ?? null }))
                        }
                        className="w-full text-xs text-slate-300 file:mr-2 file:rounded-lg file:border-0 file:bg-blue-600/30 file:px-3 file:py-1 file:text-cyan-200"
                      />
                      <div className="flex gap-2">
                        <button type="submit" className="rounded-lg bg-blue-600 px-3 py-1 text-xs font-semibold text-white">
                          Save
                        </button>
                        <button
                          type="button"
                          onClick={() => setEditingPostId(null)}
                          className="rounded-lg border border-slate-500 px-3 py-1 text-xs font-semibold text-slate-200"
                        >
                          Cancel
                        </button>
                      </div>
                    </form>
                  )}

                  <div className="mt-2 flex flex-wrap items-center gap-1.5">
                    <button
                      type="button"
                      onClick={() => {
                        setEditingPostId(post.id);
                        setEditForm({
                          title: post.title || '',
                          content: post.content || '',
                          categoryId: String(post.category?.id ?? ''),
                          imageFile: null,
                        });
                      }}
                      className="rounded-lg border border-slate-500 px-3 py-1 text-xs font-semibold text-slate-200 hover:bg-slate-700"
                    >
                      Edit
                    </button>
                    <button
                      type="button"
                      onClick={() => deletePost.mutate(post.id)}
                      className="rounded-lg border border-red-400/60 px-3 py-1 text-xs font-semibold text-red-300 hover:bg-red-500/10"
                    >
                      Delete
                    </button>
                  </div>
                </article>
              );
            })}
          </div>
        </section>
      </div>
    </div>
  );
}
