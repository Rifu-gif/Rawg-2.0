'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { api } from '@/lib/api';
import { useAuthToken } from '@/lib/auth';
import type { AuthUser } from '@/lib/types';

export default function SettingsPage() {
  const router = useRouter();
  const queryClient = useQueryClient();
  const token = useAuthToken();
  const isAuthenticated = Boolean(token);

  const [name, setName] = useState('');
  const [username, setUsername] = useState('');
  const [email, setEmail] = useState('');
  const [bio, setBio] = useState('');
  const [imageFile, setImageFile] = useState<File | null>(null);
  const [imagePreview, setImagePreview] = useState<string | null>(null);
  const [removeAvatar, setRemoveAvatar] = useState(false);
  const [message, setMessage] = useState('');
  const [error, setError] = useState('');

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

  useEffect(() => {
    if (!meQuery.data) return;
    setName(meQuery.data.name ?? '');
    setUsername(meQuery.data.username ?? '');
    setEmail(meQuery.data.email ?? '');
    setBio(meQuery.data.bio ?? '');
    setImagePreview(resolveImageUrl(meQuery.data.image));
    setRemoveAvatar(false);
  }, [meQuery.data]);

  const saveProfile = useMutation({
    mutationFn: async () => {
      const formData = new FormData();
      formData.append('name', name);
      formData.append('username', username);
      formData.append('email', email);
      formData.append('bio', bio);
      if (imageFile) {
        formData.append('image', imageFile);
      }
      if (removeAvatar) {
        formData.append('remove_image', '1');
      }

      const { data } = await api.put<AuthUser>('/auth/profile', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });
      return data;
    },
    onSuccess: (user) => {
      setMessage('Profile updated.');
      setError('');
      setImageFile(null);
      setImagePreview(resolveImageUrl(user.image));
      setRemoveAvatar(false);
      queryClient.setQueryData(['me'], user);
      queryClient.invalidateQueries({ queryKey: ['me'] });
      queryClient.invalidateQueries({ queryKey: ['favorites', 'profile'] });
      queryClient.invalidateQueries({ queryKey: ['reviews', 'profile'] });
    },
    onError: () => {
      setMessage('');
      setError('Could not update profile.');
    },
  });

  if (!isAuthenticated) return null;
  if (meQuery.isLoading) {
    return (
      <div className="min-h-screen bg-gray-100 py-12">
        <div className="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
          <div className="rounded-lg bg-white p-8 shadow-md">
            <p className="text-gray-600">Loading settings...</p>
          </div>
        </div>
      </div>
    );
  }

  const avatarLetters = (name || username || 'U').slice(0, 2).toUpperCase();
  function resolveImageUrl(image: string | null | undefined) {
    if (!image) return null;
    if (/^https?:\/\//i.test(image)) return image;
    const apiBase = process.env.NEXT_PUBLIC_API_BASE_URL || 'http://laravel-project.test/api';
    const appBase = apiBase.replace(/\/api\/?$/, '');
    return `${appBase}/storage/${image}`;
  }

  return (
    <div className="min-h-screen bg-gradient-to-r from-[#071224] via-[#10233d] to-[#0b1b30] py-10">
      <div className="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
        <div className="mb-8 rounded-3xl border border-blue-500/30 bg-gradient-to-r from-[#1d4f92] to-[#2747bd] p-6 shadow-2xl">
          <h1 className="text-4xl font-bold text-white md:text-5xl">Settings</h1>
          <p className="mt-2 text-lg text-cyan-100">Manage your account and preferences</p>
        </div>

        <div className="overflow-hidden rounded-3xl border border-slate-700 bg-slate-900/50 shadow-xl">
          <div className="border-b border-slate-700 bg-gradient-to-r from-slate-900/60 to-slate-800/50 px-4 py-6 sm:px-8">
            <h2 className="text-2xl font-bold text-white">Profile Information</h2>
            <p className="mt-1 text-sm text-slate-300">Update your account&apos;s profile information and email address.</p>
          </div>

          <div className="p-4 sm:p-8">
            <form
              className="max-w-3xl space-y-6"
              onSubmit={(e) => {
                e.preventDefault();
                setMessage('');
                setError('');
                saveProfile.mutate();
              }}
            >
              <div className="border-b border-slate-700 pb-6">
                <h3 className="mb-4 text-lg font-semibold text-white">Avatar</h3>
                <div className="flex items-center gap-4">
                  {imagePreview ? (
                    // eslint-disable-next-line @next/next/no-img-element
                    <img src={imagePreview} alt="Profile avatar" className="h-20 w-20 rounded-full object-cover shadow-lg" />
                  ) : (
                    <div className="flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-cyan-500 text-2xl font-bold text-white shadow-lg">
                      {avatarLetters}
                    </div>
                  )}
                  <div className="flex-1">
                    <input
                      type="file"
                      accept="image/*"
                      onChange={(e) => {
                        const file = e.target.files?.[0] ?? null;
                        setImageFile(file);
                        if (file) {
                          setImagePreview(URL.createObjectURL(file));
                          setRemoveAvatar(false);
                        }
                      }}
                      className="block w-full text-sm text-slate-300 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-600/20 file:px-4 file:py-2 file:font-semibold file:text-cyan-200 hover:file:bg-blue-600/30"
                    />
                    <div className="mt-2 flex flex-wrap items-center gap-2">
                      <p className="text-sm text-slate-400">Upload JPG/PNG/WebP up to 2MB.</p>
                      <button
                        type="button"
                        onClick={() => {
                          setImageFile(null);
                          setImagePreview(null);
                          setRemoveAvatar(true);
                        }}
                        className="rounded-md border border-red-400/50 px-3 py-1 text-xs font-semibold text-red-300 hover:bg-red-500/10"
                      >
                        Remove Avatar
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <div>
                <label className="block text-sm font-medium text-slate-200">Name</label>
                <input
                  value={name}
                  onChange={(e) => setName(e.target.value)}
                  placeholder="Name"
                  required
                  className="mt-2 block w-full rounded-lg border border-slate-600 bg-slate-900/40 px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-slate-200">Username</label>
                <div className="mt-2 flex items-center">
                  <span className="mr-2 font-medium text-slate-400">@</span>
                  <input
                    value={username}
                    onChange={(e) => setUsername(e.target.value)}
                    placeholder="Username"
                    required
                    className="block w-full rounded-lg border border-slate-600 bg-slate-900/40 px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
              </div>

              <div>
                <label className="block text-sm font-medium text-slate-200">Email</label>
                <input
                  type="email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  placeholder="Email"
                  required
                  className="mt-2 block w-full rounded-lg border border-slate-600 bg-slate-900/40 px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-slate-200">Bio</label>
                <textarea
                  value={bio}
                  onChange={(e) => setBio(e.target.value)}
                  placeholder="Tell others about yourself"
                  rows={4}
                  className="mt-2 block w-full rounded-lg border border-slate-600 bg-slate-900/40 px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <p className="mt-1 text-sm text-slate-400">Tell others about yourself.</p>
              </div>

              {message && <p className="text-sm font-medium text-green-600">Saved successfully.</p>}
              {error && <p className="text-sm text-red-600">{error}</p>}

              <div className="flex flex-wrap items-center gap-3 border-t border-slate-700 pt-6">
                <button
                  type="submit"
                  disabled={saveProfile.isPending}
                  className="inline-flex items-center rounded-lg bg-blue-600 px-6 py-2 font-semibold text-white shadow-md transition-colors hover:bg-blue-700 hover:shadow-lg disabled:cursor-not-allowed disabled:bg-blue-400"
                >
                  {saveProfile.isPending ? 'Saving...' : 'Save Changes'}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  );
}
