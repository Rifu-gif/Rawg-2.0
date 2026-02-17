'use client';

import { useEffect, useState } from 'react';
import { isAxiosError } from 'axios';
import { api } from '@/lib/api';
import { useAuthToken } from '@/lib/auth';
import Link from 'next/link';
import { useRouter } from 'next/navigation';

export default function RegisterPage() {
  const router = useRouter();
  const token = useAuthToken();
  const [name, setName] = useState('');
  const [username, setUsername] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);

  useEffect(() => {
    if (token) {
      router.replace('/');
    }
  }, [token, router]);

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setError('');
    setSuccess('');
    setIsSubmitting(true);
    try {
      await api.post('/auth/register', {
        name,
        username,
        email,
        password,
        password_confirmation: passwordConfirmation,
      });
      setSuccess('Account created. Check Mailpit for the verification email. Redirecting to sign in...');
      setTimeout(() => {
        router.push(`/auth/login?email=${encodeURIComponent(email)}&registered=1`);
      }, 1500);
    } catch (err: unknown) {
      setIsSubmitting(false);
      if (isAxiosError(err)) {
        const validationErrors = err.response?.data?.errors as Record<string, string[]> | undefined;
        const firstValidationMessage = validationErrors ? Object.values(validationErrors).flat()[0] : null;
        setError(firstValidationMessage || err.response?.data?.message || 'Registration failed. Check your inputs.');
        return;
      }
      setError('Registration failed. Check your inputs.');
    }
  }

  return (
    <div className="relative min-h-screen overflow-hidden bg-slate-950 px-4 py-10">
      <div className="pointer-events-none absolute -top-20 -left-16 h-72 w-72 rounded-full bg-cyan-500/20 blur-3xl" />
      <div className="pointer-events-none absolute -bottom-28 right-0 h-96 w-96 rounded-full bg-blue-600/20 blur-3xl" />

      <div className="relative mx-auto max-w-md rounded-3xl border border-slate-700 bg-slate-900/85 p-8 shadow-2xl">
        <div className="mb-5 grid grid-cols-2 gap-2 rounded-xl border border-slate-700 bg-slate-950/60 p-1">
          <Link
            href="/auth/login"
            className="rounded-lg px-3 py-2 text-center text-sm font-semibold text-slate-300 transition hover:bg-slate-800 hover:text-white"
          >
            Sign In
          </Link>
          <button
            type="button"
            className="rounded-lg bg-gradient-to-r from-cyan-500 to-blue-600 px-3 py-2 text-sm font-semibold text-white"
          >
            Register
          </button>
        </div>

        <h1 className="text-3xl font-bold text-white">Create an account</h1>
        <p className="mt-2 text-sm text-slate-300">Save favorites and review games.</p>

        <form onSubmit={handleSubmit} className="mt-6 space-y-4">
          <input
            placeholder="Name"
            value={name}
            onChange={(e) => setName(e.target.value)}
            required
            disabled={isSubmitting}
            className="w-full rounded-xl border border-slate-600 bg-slate-950/50 px-3 py-2 text-white placeholder:text-slate-500"
          />
          <input
            placeholder="Username"
            value={username}
            onChange={(e) => setUsername(e.target.value)}
            required
            disabled={isSubmitting}
            className="w-full rounded-xl border border-slate-600 bg-slate-950/50 px-3 py-2 text-white placeholder:text-slate-500"
          />
          <input
            type="email"
            placeholder="Email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            required
            disabled={isSubmitting}
            className="w-full rounded-xl border border-slate-600 bg-slate-950/50 px-3 py-2 text-white placeholder:text-slate-500"
          />
          <input
            type="password"
            placeholder="Password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            minLength={8}
            required
            disabled={isSubmitting}
            className="w-full rounded-xl border border-slate-600 bg-slate-950/50 px-3 py-2 text-white placeholder:text-slate-500"
          />
          <input
            type="password"
            placeholder="Confirm password"
            value={passwordConfirmation}
            onChange={(e) => setPasswordConfirmation(e.target.value)}
            minLength={8}
            required
            disabled={isSubmitting}
            className="w-full rounded-xl border border-slate-600 bg-slate-950/50 px-3 py-2 text-white placeholder:text-slate-500"
          />
          {success && <p className="text-sm text-emerald-400">{success}</p>}
          {error && <p className="text-sm text-red-400">{error}</p>}

          <div>
            <button
              disabled={isSubmitting}
              className="w-full rounded-full bg-gradient-to-r from-cyan-500 to-blue-600 px-4 py-2 text-sm font-semibold text-white disabled:opacity-70"
            >
              {isSubmitting ? 'Registering...' : 'Register'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
