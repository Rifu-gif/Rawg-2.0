'use client';

import { useEffect, useState } from 'react';
import { isAxiosError } from 'axios';
import { api } from '@/lib/api';
import { tokenStore, useAuthToken } from '@/lib/auth';
import Link from 'next/link';
import { useRouter, useSearchParams } from 'next/navigation';

export default function LoginPage() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const token = useAuthToken();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [info, setInfo] = useState('');
  const [rememberEmail, setRememberEmail] = useState(false);

  useEffect(() => {
    if (token) {
      router.replace('/');
    }
  }, [token, router]);

  useEffect(() => {
    const queryEmail = searchParams.get('email');
    const isRegistered = searchParams.get('registered');
    const isVerified = searchParams.get('verified');
    const verificationError = searchParams.get('verification');
    const isDeleted = searchParams.get('deleted');

    if (queryEmail) {
      setEmail(queryEmail);
    }

    if (isRegistered === '1') {
      setInfo('Account created. Please verify your email from Mailpit before logging in.');
      return;
    }

    if (isVerified === '1') {
      setInfo('Email verified successfully. You can now sign in.');
      return;
    }

    if (isDeleted === '1') {
      setInfo('Your account was deleted successfully.');
      return;
    }

    if (verificationError === 'invalid') {
      setError('This verification link is invalid or expired. Try logging in to get a new one.');
    }
  }, [searchParams]);

  useEffect(() => {
    const savedEmail = localStorage.getItem('remembered_email');
    if (savedEmail && !searchParams.get('email')) {
      setEmail(savedEmail);
      setRememberEmail(true);
    }
  }, [searchParams]);

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setError('');
    setInfo('');
    try {
      const { data } = await api.post('/auth/login', { email, password });
      if (rememberEmail) {
        localStorage.setItem('remembered_email', email);
      } else {
        localStorage.removeItem('remembered_email');
      }
      tokenStore.set(data.token);
      router.push('/');
    } catch (err: unknown) {
      if (isAxiosError(err)) {
        setError(err.response?.data?.message || 'Login failed. Check your credentials.');
        return;
      }
      setError('Login failed. Check your credentials.');
    }
  }

  return (
    <div className="relative min-h-screen overflow-hidden bg-slate-950 px-4 py-10">
      <div className="pointer-events-none absolute -top-20 -left-16 h-72 w-72 rounded-full bg-cyan-500/20 blur-3xl" />
      <div className="pointer-events-none absolute -bottom-28 right-0 h-96 w-96 rounded-full bg-blue-600/20 blur-3xl" />

      <div className="relative mx-auto max-w-md rounded-3xl border border-slate-700 bg-slate-900/85 p-8 shadow-2xl">
        <div className="mb-5 grid grid-cols-2 gap-2 rounded-xl border border-slate-700 bg-slate-950/60 p-1">
          <button
            type="button"
            className="rounded-lg bg-gradient-to-r from-cyan-500 to-blue-600 px-3 py-2 text-sm font-semibold text-white"
          >
            Sign In
          </button>
          <Link
            href="/auth/register"
            className="rounded-lg px-3 py-2 text-center text-sm font-semibold text-slate-300 transition hover:bg-slate-800 hover:text-white"
          >
            Register
          </Link>
        </div>

        <h1 className="text-3xl font-bold text-white">Welcome back</h1>
        <p className="mt-2 text-sm text-slate-300">Sign in to manage favorites and reviews.</p>

        <form onSubmit={handleSubmit} className="mt-6 space-y-4">
          <input
            type="email"
            placeholder="Email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            className="w-full rounded-xl border border-slate-600 bg-slate-950/50 px-3 py-2 text-white placeholder:text-slate-500"
          />
          <input
            type="password"
            placeholder="Password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            className="w-full rounded-xl border border-slate-600 bg-slate-950/50 px-3 py-2 text-white placeholder:text-slate-500"
          />
          <label className="flex items-center gap-2 text-sm text-slate-300">
            <input
              type="checkbox"
              checked={rememberEmail}
              onChange={(e) => setRememberEmail(e.target.checked)}
              className="h-4 w-4 rounded border-slate-500 bg-slate-900 text-cyan-500 focus:ring-cyan-500"
            />
            Remember me (email)
          </label>
          {info && <p className="text-sm text-emerald-400">{info}</p>}
          {error && <p className="text-sm text-red-400">{error}</p>}

          <div>
            <button className="w-full rounded-full bg-gradient-to-r from-cyan-500 to-blue-600 px-4 py-2 text-sm font-semibold text-white">
              Sign In
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
