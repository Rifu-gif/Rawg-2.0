'use client';

import { useQuery } from '@tanstack/react-query';
import { api } from '@/lib/api';
import type { Game, Paginated } from '@/lib/types';
import { tokenStore } from '@/lib/auth';
import Link from 'next/link';

export default function DashboardPage() {
  const token = tokenStore.get();

  const favoritesQuery = useQuery({
    queryKey: ['favorites'],
    queryFn: async () => (await api.get<Paginated<Game>>('/favorites')).data,
    enabled: Boolean(token),
  });

  if (!token) {
    return (
      <div className="glass-card rounded-3xl p-8">
        <h1 className="text-2xl font-semibold [font-family:var(--font-display)]">Favorites</h1>
        <p className="mt-3 text-sm text-[var(--ink-3)]">
          Sign in to see your favorites. <Link className="text-[var(--brand-1)]" href="/auth/login">Login</Link>
        </p>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <h1 className="text-3xl font-semibold [font-family:var(--font-display)]">Your favorites</h1>
      <div className="grid gap-6 md:grid-cols-2">
        {favoritesQuery.data?.data?.length ? (
          favoritesQuery.data.data.map((game) => (
            <div key={game.id} className="rounded-3xl border border-[var(--line)] bg-white p-5">
              <div className="flex gap-4">
                <div className="h-20 w-20 overflow-hidden rounded-2xl bg-[var(--ink-2)]">
                  {game.background_image ? (
                    // eslint-disable-next-line @next/next/no-img-element
                    <img src={game.background_image} alt={game.name} className="h-full w-full object-cover" />
                  ) : null}
                </div>
                <div>
                  <p className="text-lg font-semibold">{game.name}</p>
                  <p className="text-xs text-[var(--ink-3)]">{game.released_at ?? 'TBA'}</p>
                  <Link className="mt-2 inline-block text-sm text-[var(--brand-1)]" href={`/games/${game.id}`}>
                    View details
                  </Link>
                </div>
              </div>
            </div>
          ))
        ) : (
          <div className="rounded-2xl border border-dashed border-[var(--line)] p-6 text-sm text-[var(--ink-3)]">
            No favorites yet.
          </div>
        )}
      </div>
    </div>
  );
}
