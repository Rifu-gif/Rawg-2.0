import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { render, screen, waitFor } from '@testing-library/react';
import type { ReactNode } from 'react';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import HomePage from './page';

const { getMock } = vi.hoisted(() => ({
  getMock: vi.fn(),
}));

vi.mock('@/lib/api', () => ({
  api: {
    get: getMock,
  },
}));

vi.mock('next/link', () => ({
  default: ({ href, children, ...props }: { href: string; children: ReactNode }) => (
    <a href={href} {...props}>
      {children}
    </a>
  ),
}));

describe('home page', () => {
  beforeEach(() => {
    getMock.mockReset();
    getMock.mockImplementation((url: string) => {
      if (url === '/games') {
        return Promise.resolve({
          data: {
            data: [
              {
                id: 1,
                name: 'Elden Ring',
                background_image: null,
                rating: 4.7,
                genres: [{ id: 1, name: 'RPG', slug: 'rpg' }],
              },
            ],
            total: 1,
            current_page: 1,
            last_page: 1,
            per_page: 12,
          },
        });
      }

      if (url === '/genres') {
        return Promise.resolve({
          data: [{ id: 1, name: 'RPG', slug: 'rpg' }],
        });
      }

      if (url === '/platforms') {
        return Promise.resolve({
          data: [{ id: 1, name: 'PC', slug: 'pc' }],
        });
      }

      return Promise.resolve({ data: {} });
    });
  });

  it('renders loaded games from API', async () => {
    const client = new QueryClient();

    render(
      <QueryClientProvider client={client}>
        <HomePage />
      </QueryClientProvider>
    );

    await waitFor(() => {
      expect(screen.getByText('Elden Ring')).toBeInTheDocument();
    });

    expect(screen.getByText('Loaded 1 games')).toBeInTheDocument();
    expect(screen.getAllByText('RPG').length).toBeGreaterThan(0);
  });
});
