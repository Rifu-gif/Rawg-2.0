'use client';

import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useEffect, useMemo, useState } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { api } from '@/lib/api';
import { useAuthToken } from '@/lib/auth';
import type { Game, Genre, Paginated, Platform } from '@/lib/types';

async function fetchGames(params: Record<string, string | number | undefined>) {
  const { data } = await api.get<Paginated<Game>>('/games', { params });
  return data;
}

async function fetchGenres() {
  const { data } = await api.get<Genre[]>('/genres');
  return data;
}

async function fetchPlatforms() {
  const { data } = await api.get<Platform[]>('/platforms');
  return data;
}

export default function HomePage() {
  const router = useRouter();
  const queryClient = useQueryClient();
  const token = useAuthToken();
  const isAuthenticated = Boolean(token);

  const [search, setSearch] = useState('');
  const [genre, setGenre] = useState('');
  const [platform, setPlatform] = useState('');
  const [page, setPage] = useState(1);

  const gamesQuery = useQuery({
    queryKey: ['games', { search, genre, platform, page }],
    queryFn: () =>
      fetchGames({
        search: search || undefined,
        genre: genre || undefined,
        platform: platform || undefined,
        page,
        per_page: 12,
      }),
    enabled: isAuthenticated,
  });

  const genresQuery = useQuery({
    queryKey: ['genres'],
    queryFn: fetchGenres,
    enabled: isAuthenticated,
  });

  const platformsQuery = useQuery({
    queryKey: ['platforms'],
    queryFn: fetchPlatforms,
    enabled: isAuthenticated,
  });

  const topGenres = useMemo(() => genresQuery.data?.slice(0, 6) ?? [], [genresQuery.data]);
  const totalPages = gamesQuery.data?.last_page ?? 1;
  const loadedGames = gamesQuery.data?.total ?? 0;

  const favoritesQuery = useQuery({
    queryKey: ['favorites'],
    queryFn: async () => (await api.get<Paginated<Game>>('/favorites')).data,
    enabled: isAuthenticated,
  });

  const toggleFavoriteGame = useMutation({
    mutationFn: async (gameId: number) => {
      const isFavorited = favoritesQuery.data?.data?.some((g) => g.id === gameId);
      if (isFavorited) {
        await api.delete(`/favorites/games/${gameId}`);
      } else {
        await api.post(`/favorites/games/${gameId}`);
      }
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['favorites'] });
    },
  });

  useEffect(() => {
    if (!isAuthenticated) {
      router.replace('/auth/login');
    }
  }, [isAuthenticated, router]);

  if (!isAuthenticated) {
    return null;
  }

  return (
    <div className="min-h-screen bg-slate-950 py-10">
      <div className="mx-auto w-full max-w-6xl px-5">
        <section className="mb-6 rounded-xl bg-gradient-to-r from-slate-900 via-slate-800 to-slate-950 px-6 py-8 shadow-xl">
          <h1 className="text-5xl font-black tracking-tight text-white">Games Database</h1>
          <p className="mt-2 text-xl text-slate-200">Browse the games currently synced into your local library from RAWG</p>
        </section>

        <section className="mb-6 rounded-xl border border-slate-700 bg-slate-900 px-5 py-5 shadow-xl">
          <h2 className="text-4xl font-black tracking-tight text-white">Search and Filters</h2>
          <p className="mt-1 text-base text-slate-300">Library size: {loadedGames} synced games</p>

          <div className="mt-5 space-y-4">
            <div>
              <label className="mb-2 block text-sm font-semibold text-slate-100">Search Games</label>
              <input
                value={search}
                onChange={(e) => {
                  setSearch(e.target.value);
                  setPage(1);
                }}
                placeholder="Search for games..."
                className="w-full rounded-lg border border-slate-600 bg-slate-800 px-4 py-3 text-white placeholder:text-slate-400 outline-none transition focus:border-blue-500"
              />
            </div>

            <div className="grid grid-cols-1 gap-3 md:grid-cols-3">
              <div>
                <label className="mb-2 block text-sm font-semibold text-slate-100">Genre</label>
                <select
                  value={genre}
                  onChange={(e) => {
                    setGenre(e.target.value);
                    setPage(1);
                  }}
                  className="w-full rounded-lg border border-slate-600 bg-slate-800 px-4 py-3 text-white outline-none transition focus:border-blue-500"
                >
                  <option value="">All Genres</option>
                  {genresQuery.data?.map((g) => (
                    <option key={g.id} value={g.slug}>
                      {g.name}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label className="mb-2 block text-sm font-semibold text-slate-100">Platform</label>
                <select
                  value={platform}
                  onChange={(e) => {
                    setPlatform(e.target.value);
                    setPage(1);
                  }}
                  className="w-full rounded-lg border border-slate-600 bg-slate-800 px-4 py-3 text-white outline-none transition focus:border-blue-500"
                >
                  <option value="">All Platforms</option>
                  {platformsQuery.data?.map((p) => (
                    <option key={p.id} value={p.slug}>
                      {p.name}
                    </option>
                  ))}
                </select>
              </div>

              <div className="flex items-end">
                <button
                  type="button"
                  onClick={() => {
                    setSearch('');
                    setGenre('');
                    setPlatform('');
                    setPage(1);
                  }}
                  className="w-full rounded-lg bg-slate-700 px-5 py-3 text-base font-semibold text-white transition hover:bg-slate-600"
                >
                  Clear Filters
                </button>
              </div>
            </div>

            <div className="flex flex-wrap gap-2">
              {topGenres.map((g) => (
                <button
                  key={g.id}
                  onClick={() => {
                    setGenre(g.slug);
                    setPage(1);
                  }}
                  className={`rounded-md px-4 py-2 text-sm font-semibold transition ${
                    genre === g.slug ? 'bg-blue-600 text-white' : 'bg-slate-800 text-slate-100 hover:bg-slate-700'
                  }`}
                >
                  {g.name}
                </button>
              ))}
            </div>
          </div>
        </section>

        {gamesQuery.isLoading && (
          <div className="rounded-xl bg-white p-8 text-center shadow-sm">
            <p className="text-lg text-slate-600">Loading games...</p>
          </div>
        )}

        {!gamesQuery.isLoading && (gamesQuery.data?.data?.length ?? 0) === 0 && (
          <div className="rounded-xl bg-white p-8 text-center shadow-sm">
            <p className="text-lg text-slate-600">No games found. Try adjusting your filters or search query.</p>
          </div>
        )}

        <section className="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
          {gamesQuery.data?.data?.map((game) => (
            <Link
              key={game.id}
              href={`/games/${game.id}`}
              className="group relative overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm transition hover:shadow-md"
            >
              <button
                type="button"
                onClick={(event) => {
                  event.preventDefault();
                  event.stopPropagation();
                  toggleFavoriteGame.mutate(game.id);
                }}
                className="absolute right-2 top-2 z-10 rounded-full bg-black/60 px-2 py-1 text-lg leading-none text-yellow-300 backdrop-blur hover:bg-black/80"
                aria-label={favoritesQuery.data?.data?.some((g) => g.id === game.id) ? 'Unfavorite game' : 'Favorite game'}
              >
                {favoritesQuery.data?.data?.some((g) => g.id === game.id) ? '★' : '☆'}
              </button>
              <div className="h-44 overflow-hidden bg-slate-200">
                {game.background_image ? (
                  // eslint-disable-next-line @next/next/no-img-element
                  <img
                    src={game.background_image}
                    alt={game.name}
                    className="h-full w-full object-cover transition-transform duration-200 group-hover:scale-[1.03]"
                  />
                ) : (
                  <div className="flex h-full items-center justify-center bg-slate-300">
                    <span className="text-slate-500">No Image</span>
                  </div>
                )}
              </div>

              <div className="p-3">
                <h3 className="line-clamp-2 text-3xl font-black leading-[1.02] tracking-tight text-slate-900">
                  {game.name}
                </h3>

                {game.rating && (
                  <div className="mt-2 flex items-center text-slate-700">
                    <span className="text-yellow-500">★</span>
                    <span className="ml-1 text-base">{game.rating.toFixed(1)}/5</span>
                  </div>
                )}

                {game.released_at && <p className="mt-2 text-xs text-slate-500">{game.released_at}</p>}

                <div className="mt-3 flex flex-wrap gap-1">
                  {game.platforms?.slice(0, 3).map((p) => (
                    <span key={p.id} className="rounded bg-blue-100 px-2 py-1 text-xs font-medium text-blue-800">
                      {p.name}
                    </span>
                  ))}
                </div>
              </div>
            </Link>
          ))}
        </section>

        <section className="relative overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/80 p-6 shadow-2xl">
          <div className="pointer-events-none absolute -left-16 top-0 h-40 w-40 rounded-full bg-cyan-500/10 blur-3xl" />
          <div className="pointer-events-none absolute -right-20 bottom-0 h-48 w-48 rounded-full bg-blue-600/10 blur-3xl" />

          <div className="relative flex flex-col items-center gap-3 text-center sm:flex-row sm:justify-center">
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
