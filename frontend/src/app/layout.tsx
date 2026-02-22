import type { Metadata } from "next";
import { Fraunces, Space_Grotesk } from "next/font/google";
import "./globals.css";
import Providers from "./providers";
import Link from "next/link";

const display = Fraunces({
  variable: "--font-display",
  subsets: ["latin"],
});

const sans = Space_Grotesk({
  variable: "--font-sans",
  subsets: ["latin"],
});

export const metadata: Metadata = {
  title: "GameHub",
  description: "Discover, review, and favorite games.",
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="en">
      <body className={`${display.variable} ${sans.variable} antialiased`}>
        <Providers>
          <div className="min-h-screen bg-atmosphere">
            <header className="sticky top-0 z-50 border-b border-[var(--line)] bg-white/70 backdrop-blur">
              <div className="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
                <Link href="/" className="flex items-center gap-2 text-lg font-semibold">
                  <span className="inline-flex h-9 w-9 items-center justify-center rounded-full bg-[var(--ink-1)] text-[var(--brand-3)]">
                    GH
                  </span>
                  <span className="font-[var(--font-display)] text-xl">GameHub</span>
                </Link>
                <nav className="flex items-center gap-4 text-sm">
                  <Link href="/" className="rounded-full px-3 py-2 hover:bg-black/5">
                    Games
                  </Link>
                  <Link href="/reviews" className="rounded-full px-3 py-2 hover:bg-black/5">
                    Reviews
                  </Link>
                  <Link href="/dashboard" className="rounded-full px-3 py-2 hover:bg-black/5">
                    Favorites
                  </Link>
                  <Link href="/auth/login" className="rounded-full bg-[var(--ink-1)] px-4 py-2 text-white">
                    Sign In
                  </Link>
                </nav>
              </div>
            </header>
            <main className="mx-auto max-w-6xl px-6 py-10">{children}</main>
          </div>
        </Providers>
      </body>
    </html>
  );
}
