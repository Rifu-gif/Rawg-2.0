'use client';

import { useEffect, useState } from 'react';
import { isAxiosError } from 'axios';
import { useRouter } from 'next/navigation';
import { useMutation, useQuery } from '@tanstack/react-query';
import { api } from '@/lib/api';
import { useAuthToken } from '@/lib/auth';
import type { PostCategory, UserPost } from '@/lib/types';

export default function NewPostPage() {
  const router = useRouter();
  const token = useAuthToken();
  const isAuthenticated = Boolean(token);

  const [title, setTitle] = useState('');
  const [content, setContent] = useState('');
  const [categoryId, setCategoryId] = useState('');
  const [imageFile, setImageFile] = useState<File | null>(null);
  const [error, setError] = useState('');

  useEffect(() => {
    if (!isAuthenticated) {
      router.replace('/auth/login');
    }
  }, [isAuthenticated, router]);

  const categoriesQuery = useQuery({
    queryKey: ['post-categories'],
    queryFn: async () => (await api.get<PostCategory[]>('/post-categories')).data,
    enabled: isAuthenticated,
  });

  const createPost = useMutation({
    mutationFn: async () => {
      const formData = new FormData();
      formData.append('title', title);
      formData.append('content', content);
      formData.append('category_id', categoryId);
      if (imageFile) {
        formData.append('image', imageFile);
      }

      const { data } = await api.post<UserPost>('/posts', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      return data;
    },
    onSuccess: () => {
      router.push('/profile#my-posts');
    },
    onError: (err) => {
      if (isAxiosError(err)) {
        const validationErrors = err.response?.data?.errors as Record<string, string[]> | undefined;
        const firstValidationMessage = validationErrors ? Object.values(validationErrors).flat()[0] : null;
        setError(firstValidationMessage || err.response?.data?.message || 'Could not create post.');
        return;
      }
      setError('Could not create post.');
    },
  });

  if (!isAuthenticated) return null;

  return (
    <div className="min-h-screen bg-gradient-to-r from-[#071224] via-[#10233d] to-[#0b1b30] py-10">
      <div className="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div className="mb-8 rounded-3xl border border-blue-500/30 bg-gradient-to-r from-[#1d4f92] to-[#2747bd] p-6 shadow-2xl">
          <h1 className="text-4xl font-bold text-white">Create New Post</h1>
          <p className="mt-2 text-lg text-cyan-100">Add an image, write your description, and choose a category.</p>
        </div>

        <div className="rounded-3xl border border-slate-700 bg-slate-900/50 p-6 shadow-xl">
          <form
            className="space-y-5"
            onSubmit={(e) => {
              e.preventDefault();
              setError('');
              createPost.mutate();
            }}
          >
            <div>
              <label className="block text-sm font-medium text-slate-200">Title</label>
              <input
                value={title}
                onChange={(e) => setTitle(e.target.value)}
                required
                className="mt-2 block w-full rounded-lg border border-slate-600 bg-slate-900/40 px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <div>
              <label className="block text-sm font-medium text-slate-200">Category</label>
              <select
                value={categoryId}
                onChange={(e) => setCategoryId(e.target.value)}
                required
                className="mt-2 block w-full rounded-lg border border-slate-600 bg-slate-900/40 px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Select category</option>
                {categoriesQuery.data?.map((category) => (
                  <option key={category.id} value={category.id}>
                    {category.name}
                  </option>
                ))}
              </select>
            </div>

            <div>
              <label className="block text-sm font-medium text-slate-200">Image</label>
              <input
                type="file"
                accept="image/*"
                onChange={(e) => setImageFile(e.target.files?.[0] ?? null)}
                className="mt-2 block w-full text-sm text-slate-300 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-600/20 file:px-4 file:py-2 file:font-semibold file:text-cyan-200 hover:file:bg-blue-600/30"
              />
            </div>

            <div>
              <label className="block text-sm font-medium text-slate-200">Description</label>
              <textarea
                value={content}
                onChange={(e) => setContent(e.target.value)}
                required
                rows={7}
                className="mt-2 block w-full rounded-lg border border-slate-600 bg-slate-900/40 px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            {error && <p className="text-sm text-red-400">{error}</p>}

            <button
              type="submit"
              disabled={createPost.isPending}
              className="inline-flex items-center rounded-lg bg-blue-600 px-6 py-2 font-semibold text-white shadow-md transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-blue-400"
            >
              {createPost.isPending ? 'Publishing...' : 'Publish Post'}
            </button>
          </form>
        </div>
      </div>
    </div>
  );
}

