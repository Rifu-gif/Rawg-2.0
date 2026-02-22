'use client';

import { useMemo, useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { api } from '@/lib/api';
import type { Game, Genre, Paginated, Platform } from '@/lib/types';
import Link from 'next/link';
import clsx from 'clsx';

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
  });

  const genresQuery = useQuery({ queryKey: ['genres'], queryFn: fetchGenres });
  const platformsQuery = useQuery({ queryKey: ['platforms'], queryFn: fetchPlatforms });

  const totalPages = gamesQuery.data?.last_page ?? 1;

  const heroStats = useMemo(() => {
    const total = gamesQuery.data?.total ?? 0;
    return {
      total,
      tag: total > 0 ? `Loaded ${total} games` : 'No games loaded',
    };
  }, [gamesQuery.data]);

  return (
    <div className="space-y-8">
      <section className="glass-card rounded-3xl p-8">
        <div className="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
          <div>
            <p className="text-xs uppercase tracking-[0.2em] text-[var(--brand-2)]">Discover</p>
            <h1 className="mt-2 text-4xl font-semibold text-[var(--ink-1)] [font-family:var(--font-display)]">
              Explore standout games and build your favorites list
            </h1>
            <p className="mt-3 text-sm text-[var(--ink-3)]">{heroStats.tag}</p>
          </div>
          <div className="flex flex-col gap-3">
            <input
              value={search}
              onChange={(e) => {
                setSearch(e.target.value);
                setPage(1);
              }}
              placeholder="Search by name"
              className="w-full rounded-full border border-[var(--line)] bg-white px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--brand-1)]"
            />
            <div className="flex gap-3">
              <select
                value={genre}
                onChange={(e) => {
                  setGenre(e.target.value);
                  setPage(1);
                }}
                className="w-full rounded-full border border-[var(--line)] bg-white px-4 py-2 text-sm"
              >
                <option value="">All genres</option>
                {genresQuery.data?.map((g) => (
                  <option key={g.id} value={g.slug}>
                    {g.name}
                  </option>
                ))}
              </select>
              <select
                value={platform}
                onChange={(e) => {
                  setPlatform(e.target.value);
                  setPage(1);
                }}
                className="w-full rounded-full border border-[var(--line)] bg-white px-4 py-2 text-sm"
              >
                <option value="">All platforms</option>
                {platformsQuery.data?.map((p) => (
                  <option key={p.id} value={p.slug}>
                    {p.name}
                  </option>
                ))}
              </select>
            </div>
          </div>
        </div>
      </section>

      <section className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        {gamesQuery.isLoading && (
          <div className="col-span-full rounded-2xl border border-dashed border-[var(--line)] p-12 text-center">
            Loading games...
          </div>
        )}

        {gamesQuery.data?.data?.map((game) => (
          <Link
            key={game.id}
            href={`/games/${game.id}`}
            className="group overflow-hidden rounded-3xl border border-[var(--line)] bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl"
          >
            <div className="h-48 overflow-hidden bg-[var(--ink-2)]">
              {game.background_image ? (
                // eslint-disable-next-line @next/next/no-img-element
                <img
                  src={game.background_image}
                  alt={game.name}
                  className="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                />
              ) : (
                <div className="flex h-full items-center justify-center text-sm text-white/70">No image</div>
              )}
            </div>
            <div className="space-y-3 p-5">
              <div className="flex items-center justify-between">
                <h3 className="text-lg font-semibold text-[var(--ink-1)]">{game.name}</h3>
                {game.rating && (
                  <span className="rounded-full bg-[var(--brand-3)] px-3 py-1 text-xs font-semibold text-[var(--ink-1)]">
                    {game.rating.toFixed(1)}
                  </span>
                )}
              </div>
              <div className="flex flex-wrap gap-2 text-xs text-[var(--ink-3)]">
                {game.genres?.slice(0, 3).map((g) => (
                  <span key={g.id} className="rounded-full border border-[var(--line)] px-3 py-1">
                    {g.name}
                  </span>
                ))}
              </div>
            </div>
          </Link>
        ))}
      </section>

      <div className="flex items-center justify-between">
        <button
          className={clsx(
            'rounded-full px-4 py-2 text-sm font-semibold',
            page <= 1 ? 'cursor-not-allowed bg-gray-200 text-gray-500' : 'bg-[var(--ink-1)] text-white'
          )}
          onClick={() => setPage((p) => Math.max(1, p - 1))}
          disabled={page <= 1}
        >
          Previous
        </button>
        <span className="text-sm text-[var(--ink-3)]">
          Page {page} of {totalPages}
        </span>
        <button
          className={clsx(
            'rounded-full px-4 py-2 text-sm font-semibold',
            page >= totalPages ? 'cursor-not-allowed bg-gray-200 text-gray-500' : 'bg-[var(--ink-1)] text-white'
          )}
          onClick={() => setPage((p) => Math.min(totalPages, p + 1))}
          disabled={page >= totalPages}
        >
          Next
        </button>
      </div>
    </div>
  );
}
