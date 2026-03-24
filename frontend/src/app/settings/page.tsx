'use client';

import { useEffect, useState } from 'react';
import { isAxiosError } from 'axios';
import { useRouter } from 'next/navigation';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { api } from '@/lib/api';
import { tokenStore, useAuthToken } from '@/lib/auth';
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
  const [weeklyRecommendationEmails, setWeeklyRecommendationEmails] = useState(true);
  const [imageFile, setImageFile] = useState<File | null>(null);
  const [imagePreview, setImagePreview] = useState<string | null>(null);
  const [removeAvatar, setRemoveAvatar] = useState(false);
  const [message, setMessage] = useState('');
  const [error, setError] = useState('');
  const [showDeleteConfirm, setShowDeleteConfirm] = useState(false);
  const [deletePassword, setDeletePassword] = useState('');
  const [deleteError, setDeleteError] = useState('');

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
    setWeeklyRecommendationEmails(meQuery.data.weekly_recommendation_emails ?? true);
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
      formData.append('weekly_recommendation_emails', weeklyRecommendationEmails ? '1' : '0');
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

  const deleteAccount = useMutation({
    mutationFn: async () => {
      await api.delete('/auth/account', {
        data: {
          password: deletePassword,
        },
      });
    },
    onSuccess: () => {
      tokenStore.clear();
      queryClient.clear();
      router.replace('/auth/login?deleted=1');
    },
    onError: (err: unknown) => {
      if (isAxiosError(err)) {
        const validationErrors = err.response?.data?.errors as Record<string, string[]> | undefined;
        const firstValidationMessage = validationErrors ? Object.values(validationErrors).flat()[0] : null;
        setDeleteError(firstValidationMessage || err.response?.data?.message || 'Could not delete account.');
        return;
      }
      setDeleteError('Could not delete account.');
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

              <div className="rounded-xl border border-slate-700 bg-slate-900/40 p-4">
                <label className="flex items-start gap-3">
                  <input
                    type="checkbox"
                    checked={weeklyRecommendationEmails}
                    onChange={(e) => setWeeklyRecommendationEmails(e.target.checked)}
                    className="mt-1 h-4 w-4 rounded border-slate-500 bg-slate-950 text-blue-500 focus:ring-blue-500"
                  />
                  <span>
                    <span className="block text-sm font-medium text-slate-200">Weekly recommendation emails</span>
                    <span className="mt-1 block text-sm text-slate-400">
                      Receive a weekly email with game recommendations based on your favorites and high-rated reviews. You can unsubscribe anytime.
                    </span>
                  </span>
                </label>
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

              <div className="rounded-xl border border-red-500/30 bg-red-900/10 p-4">
                <p className="text-sm font-semibold text-red-300">Danger Zone</p>
                <p className="mt-1 text-sm text-red-200/90">Delete your account permanently. This action cannot be undone.</p>

                {!showDeleteConfirm ? (
                  <button
                    type="button"
                    onClick={() => {
                      setDeleteError('');
                      setShowDeleteConfirm(true);
                    }}
                    className="mt-4 inline-flex items-center rounded-lg border border-red-400/70 bg-red-500/10 px-4 py-2 text-sm font-semibold text-red-200 transition-colors hover:bg-red-500/20"
                  >
                    Delete Account
                  </button>
                ) : (
                  <div className="mt-4 space-y-3">
                    <label className="block text-sm font-medium text-red-100">Confirm password to delete account</label>
                    <input
                      type="password"
                      value={deletePassword}
                      onChange={(e) => setDeletePassword(e.target.value)}
                      placeholder="Enter your password"
                      className="block w-full rounded-lg border border-red-300/40 bg-slate-900/50 px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-red-500"
                    />
                    {deleteError && <p className="text-sm text-red-300">{deleteError}</p>}
                    <div className="flex flex-wrap gap-2">
                      <button
                        type="button"
                        onClick={() => {
                          setDeletePassword('');
                          setDeleteError('');
                          setShowDeleteConfirm(false);
                        }}
                        className="rounded-lg border border-slate-500 px-4 py-2 text-sm font-semibold text-slate-200 hover:bg-slate-700/30"
                      >
                        Cancel
                      </button>
                      <button
                        type="button"
                        disabled={deleteAccount.isPending || deletePassword.length === 0}
                        onClick={() => {
                          setDeleteError('');
                          deleteAccount.mutate();
                        }}
                        className="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:cursor-not-allowed disabled:bg-red-400"
                      >
                        {deleteAccount.isPending ? 'Deleting...' : 'Confirm Delete'}
                      </button>
                    </div>
                  </div>
                )}
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  );
}
