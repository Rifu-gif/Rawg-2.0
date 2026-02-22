'use client';

import { useState } from 'react';
import { api } from '@/lib/api';
import { tokenStore } from '@/lib/auth';
import Link from 'next/link';
import { useRouter } from 'next/navigation';

export default function LoginPage() {
  const router = useRouter();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setError('');
    try {
      const { data } = await api.post('/auth/login', { email, password });
      tokenStore.set(data.token);
      router.push('/dashboard');
    } catch (err) {
      setError('Login failed. Check your credentials.');
    }
  }

  return (
    <div className="mx-auto max-w-md rounded-3xl border border-[var(--line)] bg-white p-8 shadow-xl">
      <h1 className="text-2xl font-semibold [font-family:var(--font-display)]">Welcome back</h1>
      <p className="mt-2 text-sm text-[var(--ink-3)]">Sign in to manage favorites and reviews.</p>
      <form onSubmit={handleSubmit} className="mt-6 space-y-4">
        <input
          type="email"
          placeholder="Email"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          className="w-full rounded-xl border border-[var(--line)] px-3 py-2"
        />
        <input
          type="password"
          placeholder="Password"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          className="w-full rounded-xl border border-[var(--line)] px-3 py-2"
        />
        {error && <p className="text-sm text-red-500">{error}</p>}
        <button className="w-full rounded-full bg-[var(--ink-1)] px-4 py-2 text-sm font-semibold text-white">
          Sign in
        </button>
      </form>
      <p className="mt-4 text-sm text-[var(--ink-3)]">
        No account yet?{' '}
        <Link className="text-[var(--brand-1)]" href="/auth/register">
          Register
        </Link>
      </p>
    </div>
  );
}
