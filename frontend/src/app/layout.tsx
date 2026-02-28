import type { Metadata } from 'next';
import { Fraunces, Space_Grotesk } from 'next/font/google';
import './globals.css';
import Providers from './providers';
import SiteHeader from '@/components/site-header';

const display = Fraunces({
  variable: '--font-display',
  subsets: ['latin'],
});

const sans = Space_Grotesk({
  variable: '--font-sans',
  subsets: ['latin'],
});

export const metadata: Metadata = {
  title: 'Game Social',
  description: 'Discover and share games with the community.',
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
          <div className="min-h-screen bg-[#f2f4f8]">
            <SiteHeader />
            <main>{children}</main>
          </div>
        </Providers>
      </body>
    </html>
  );
}
