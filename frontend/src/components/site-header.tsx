'use client';

import Link from 'next/link';
import { usePathname, useRouter } from 'next/navigation';
import { useQuery } from '@tanstack/react-query';
import { useEffect, useRef, useState } from 'react';
import { api } from '@/lib/api';
import { tokenStore, useAuthToken } from '@/lib/auth';
import type { AuthUser, SearchUser } from '@/lib/types';

export default function SiteHeader() {
  const router = useRouter();
  const pathname = usePathname();
  const token = useAuthToken();
  const isAuthenticated = Boolean(token);
  const isAuthPage = pathname === '/auth/login' || pathname === '/auth/register';
  const [menuOpen, setMenuOpen] = useState(false);
  const [avatarLoadFailed, setAvatarLoadFailed] = useState(false);
  const menuRef = useRef<HTMLDivElement | null>(null);
  const searchRef = useRef<HTMLDivElement | null>(null);
  const [search, setSearch] = useState('');
  const [searchOpen, setSearchOpen] = useState(false);

  const meQuery = useQuery({
    queryKey: ['me'],
    queryFn: async () => (await api.get<AuthUser>('/auth/me')).data,
    enabled: isAuthenticated,
    retry: false,
  });

  useEffect(() => {
    if (isAuthenticated && meQuery.isError) {
      tokenStore.clear();
    }
  }, [isAuthenticated, meQuery.isError]);

  useEffect(() => {
    function onClickOutside(event: MouseEvent) {
      if (menuRef.current && !menuRef.current.contains(event.target as Node)) {
        setMenuOpen(false);
      }
      if (searchRef.current && !searchRef.current.contains(event.target as Node)) {
        setSearchOpen(false);
      }
    }

    if (menuOpen || searchOpen) {
      document.addEventListener('mousedown', onClickOutside);
    }

    return () => document.removeEventListener('mousedown', onClickOutside);
  }, [menuOpen, searchOpen]);

  const searchUsersQuery = useQuery({
    queryKey: ['users', 'search', search],
    queryFn: async () => (await api.get<SearchUser[]>('/users/search', { params: { q: search } })).data,
    enabled: search.trim().length > 0,
  });

  const displayName = meQuery.data?.username || meQuery.data?.name || 'Account';
  const secondaryName = meQuery.data?.username ? `@${meQuery.data.username}` : '';
  const avatarLetter = displayName.charAt(0).toUpperCase() || 'A';
  const avatarUrl = resolveImageUrl(meQuery.data?.image);

  useEffect(() => {
    setAvatarLoadFailed(false);
  }, [avatarUrl]);

  if (isAuthPage) {
    return null;
  }

  async function handleLogout() {
    try {
      await api.post('/auth/logout');
    } finally {
      tokenStore.clear();
      setMenuOpen(false);
      router.push('/auth/login');
      router.refresh();
    }
  }

  function resolveImageUrl(image: string | null | undefined) {
    if (!image || image.trim() === '') return null;
    if (/^https?:\/\//i.test(image)) return image;
    const apiBase = process.env.NEXT_PUBLIC_API_BASE_URL || 'http://laravel-project.test/api';
    const appBase = apiBase.replace(/\/api\/?$/, '');
    return `${appBase}/storage/${image}`;
  }

  return (
    <header className="sticky top-0 z-50 border-b border-slate-800 bg-black shadow-md">
      <div className="mx-auto flex h-20 w-full max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
        <Link href="/posts" className="flex items-center">
          <img
            src="/gamelogo.jpg"
            alt="Game console logo"
            className="h-14 w-14 rounded-full object-cover ring-1 ring-slate-700"
          />
        </Link>

        <div className="hidden items-center gap-6 md:flex">
          <div className="relative" ref={searchRef}>
            <input
              value={search}
              onChange={(e) => {
                setSearch(e.target.value);
                setSearchOpen(true);
              }}
              onFocus={() => setSearchOpen(true)}
              onKeyDown={(e) => {
                if (e.key === 'Enter' && searchUsersQuery.data?.[0]) {
                  const first = searchUsersQuery.data[0];
                  setSearchOpen(false);
                  setSearch('');
                  router.push(first.username === meQuery.data?.username ? '/profile' : `/users/${first.username}`);
                }
              }}
              placeholder="Search users..."
              className="w-48 rounded-lg border border-slate-700 bg-slate-900 px-4 py-2 text-sm text-slate-200 placeholder:text-slate-500 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-cyan-500"
            />
            <span className="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-500">S</span>
            {searchOpen && search.trim().length > 0 && (
              <div className="absolute left-0 right-0 z-50 mt-2 max-h-72 overflow-auto rounded-lg border border-slate-700 bg-slate-900 shadow-xl">
                {searchUsersQuery.isLoading && (
                  <p className="px-3 py-2 text-xs text-slate-400">Searching...</p>
                )}
                {!searchUsersQuery.isLoading && (searchUsersQuery.data?.length ?? 0) === 0 && (
                  <p className="px-3 py-2 text-xs text-slate-400">No users found.</p>
                )}
                {searchUsersQuery.data?.map((user) => (
                  <button
                    key={user.id}
                    type="button"
                    onClick={() => {
                      setSearchOpen(false);
                      setSearch('');
                      router.push(user.username === meQuery.data?.username ? '/profile' : `/users/${user.username}`);
                    }}
                    className="block w-full border-b border-slate-800 px-3 py-2 text-left hover:bg-slate-800"
                  >
                    <p className="text-sm font-semibold text-slate-100">{user.name}</p>
                    <p className="text-xs text-slate-400">@{user.username}</p>
                  </button>
                ))}
              </div>
            )}
          </div>

          <Link
            href="/"
            className="inline-flex items-center rounded-lg px-5 py-3 text-base font-semibold text-slate-200 transition-all duration-200 hover:bg-slate-900 hover:text-cyan-300"
          >
            Games
          </Link>

          {isAuthenticated && (
            <Link
              href="/favorites"
              className="inline-flex items-center rounded-lg px-5 py-3 text-base font-semibold text-slate-200 transition-all duration-200 hover:bg-slate-900 hover:text-cyan-300"
            >
              Favorites
            </Link>
          )}

          <Link
            href="/posts/new"
            className="inline-flex items-center rounded-lg bg-gradient-to-r from-cyan-500 to-blue-600 px-8 py-3.5 text-lg font-bold text-white transition-all duration-300 hover:scale-105 hover:shadow-lg"
          >
            + New Post
          </Link>

          {isAuthenticated ? (
            <div className="relative" ref={menuRef}>
              <button
                type="button"
                onClick={() => setMenuOpen((v) => !v)}
                className="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-slate-200 transition-all duration-200 hover:bg-slate-900 hover:text-white"
              >
                {avatarUrl && !avatarLoadFailed ? (
                  // eslint-disable-next-line @next/next/no-img-element
                  <img
                    src={avatarUrl}
                    alt="Avatar"
                    className="h-9 w-9 rounded-full object-cover"
                    onError={() => setAvatarLoadFailed(true)}
                  />
                ) : (
                  <span className="inline-flex h-9 w-9 items-center justify-center rounded-full bg-slate-200 text-xs font-semibold text-slate-900">
                    {avatarLetter}
                  </span>
                )}
                {displayName}
                <span className="text-xs text-slate-500">v</span>
              </button>

              {menuOpen && (
                <div className="absolute right-0 z-50 mt-2 w-56 overflow-hidden rounded-lg border border-slate-700 bg-slate-900 shadow-xl">
                  <div className="border-b border-slate-700 px-4 py-3">
                    <p className="text-sm font-semibold text-slate-100">{meQuery.data?.name || displayName}</p>
                    <p className="text-xs text-slate-400">{secondaryName}</p>
                  </div>
                  <Link
                    href="/profile"
                    onClick={() => setMenuOpen(false)}
                    className="block px-4 py-3 text-sm text-slate-200 transition-colors hover:bg-slate-800"
                  >
                    View Profile
                  </Link>
                  <Link
                    href="/favorites"
                    onClick={() => setMenuOpen(false)}
                    className="block px-4 py-3 text-sm text-slate-200 transition-colors hover:bg-slate-800"
                  >
                    Favorites
                  </Link>
                  <Link
                    href="/settings"
                    onClick={() => setMenuOpen(false)}
                    className="block border-t border-slate-700 px-4 py-3 text-sm text-slate-200 transition-colors hover:bg-slate-800"
                  >
                    Settings
                  </Link>
                  <button
                    type="button"
                    onClick={handleLogout}
                    className="block w-full border-t border-slate-700 px-4 py-3 text-left text-sm text-red-400 transition-colors hover:bg-red-500/10"
                  >
                    Log Out
                  </button>
                </div>
              )}
            </div>
          ) : (
            <div className="flex items-center gap-2">
              <Link
                href="/auth/login"
                className="px-4 py-2 text-sm font-medium text-slate-200 transition-colors hover:text-cyan-300"
              >
                Sign In
              </Link>
              <Link
                href="/auth/register"
                className="rounded-lg bg-gradient-to-r from-cyan-500 to-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition-all duration-300 hover:scale-105 hover:shadow-lg"
              >
                Get Started
              </Link>
            </div>
          )}
        </div>
      </div>
    </header>
  );
}
