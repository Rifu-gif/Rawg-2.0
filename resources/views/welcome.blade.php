<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} - Discover & Share Games</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-gray-900 text-white">
    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 bg-gray-900/95 backdrop-blur-sm border-b border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-white" />
                    </a>
                </div>

                <!-- Auth Links -->
                <div class="flex items-center space-x-4">
                    <a href="{{ route('login') }}" 
                       class="px-4 py-2 text-sm font-medium text-gray-300 hover:text-white transition-colors">
                        Sign In
                    </a>
                    <a href="{{ route('register') }}" 
                       class="px-6 py-2 bg-cyan-500 text-white font-semibold rounded-lg hover:bg-cyan-600 shadow-lg hover:shadow-cyan-500/50 transition-all duration-200">
                        Sign Up
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="relative pt-32 pb-20 px-4 overflow-hidden">
        <!-- Animated Background -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute w-full h-full bg-gradient-to-br from-cyan-900/20 via-gray-900 to-purple-900/20"></div>
            <div class="absolute top-0 left-0 w-96 h-96 bg-cyan-500/10 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute bottom-0 right-0 w-96 h-96 bg-purple-500/10 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
        </div>

        <div class="relative max-w-7xl mx-auto text-center">
            <!-- Badge -->
            <div class="inline-flex items-center space-x-2 bg-cyan-500/10 border border-cyan-500/20 px-4 py-2 rounded-full mb-8">
                <svg class="w-4 h-4 text-cyan-400" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                </svg>
                <span class="text-sm font-medium text-cyan-300">The ultimate gaming database</span>
            </div>

            <!-- Headline -->
            <h1 class="text-5xl md:text-7xl font-bold mb-6 leading-tight">
                Discover Amazing
                <br/>
                <span class="bg-gradient-to-r from-cyan-400 via-blue-500 to-purple-600 bg-clip-text text-transparent">
                    Video Games
                </span>
            </h1>

            <!-- Subheadline -->
            <p class="text-xl md:text-2xl text-gray-400 mb-10 max-w-3xl mx-auto">
                Browse thousands of games, read reviews, track your collection, 
                and share your gaming experiences with the community.
            </p>

            <!-- CTA Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('register') }}" 
                   class="w-full sm:w-auto px-8 py-4 bg-gradient-to-r from-cyan-500 to-blue-600 text-white text-lg font-semibold rounded-xl hover:shadow-2xl hover:shadow-cyan-500/50 transition-all duration-300 transform hover:-translate-y-1">
                    Start Exploring
                    <svg class="inline-block w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                    </svg>
                </a>
                <a href="{{ route('dashboard') }}" 
                   class="w-full sm:w-auto px-8 py-4 bg-gray-800 text-white text-lg font-semibold rounded-xl hover:bg-gray-700 border-2 border-gray-700 hover:border-gray-600 transition-all duration-200">
                    Browse Games
                </a>
            </div>

            <!-- Stats -->
            <div class="mt-20 grid grid-cols-3 gap-8 max-w-3xl mx-auto">
                <div class="text-center">
                    <div class="text-4xl md:text-5xl font-bold bg-gradient-to-r from-cyan-400 to-blue-500 bg-clip-text text-transparent">
                        {{ number_format(\App\Models\Post::count()) }}+
                    </div>
                    <div class="text-gray-400 mt-2 text-sm md:text-base">Games Listed</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl md:text-5xl font-bold bg-gradient-to-r from-purple-400 to-pink-500 bg-clip-text text-transparent">
                        {{ number_format(\App\Models\User::count()) }}+
                    </div>
                    <div class="text-gray-400 mt-2 text-sm md:text-base">Gamers</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl md:text-5xl font-bold bg-gradient-to-r from-green-400 to-emerald-500 bg-clip-text text-transparent">
                        {{ number_format(\App\Models\Clap::count()) }}+
                    </div>
                    <div class="text-gray-400 mt-2 text-sm md:text-base">Reviews</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="relative py-20 px-4 bg-gray-800/50">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold mb-4">Everything for Gamers</h2>
                <p class="text-xl text-gray-400">All the tools you need to track and discover games</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-gray-800/80 backdrop-blur-sm border border-gray-700 rounded-2xl p-8 hover:border-cyan-500/50 transition-all duration-300 group">
                    <div class="w-14 h-14 bg-gradient-to-br from-cyan-500 to-blue-600 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold mb-3">Discover Games</h3>
                    <p class="text-gray-400">Browse through thousands of games across all platforms and genres. Find your next favorite game.</p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-gray-800/80 backdrop-blur-sm border border-gray-700 rounded-2xl p-8 hover:border-purple-500/50 transition-all duration-300 group">
                    <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold mb-3">Track Collection</h3>
                    <p class="text-gray-400">Build your personal game library. Keep track of games you own, want, or have played.</p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-gray-800/80 backdrop-blur-sm border border-gray-700 rounded-2xl p-8 hover:border-green-500/50 transition-all duration-300 group">
                    <div class="w-14 h-14 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold mb-3">Write Reviews</h3>
                    <p class="text-gray-400">Share your thoughts and help others discover great games. Read reviews from the community.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Categories Preview -->
    <div class="relative py-20 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold mb-4">Browse by Genre</h2>
                <p class="text-xl text-gray-400">Explore games across different categories</p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                @foreach(\App\Models\Category::all() as $category)
                <a href="{{ route('post.byCategory', $category) }}" 
                   class="group bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-xl p-6 hover:border-cyan-500/50 transition-all duration-300 text-center">
                    <div class="text-3xl mb-3"></div>
                    <h3 class="font-semibold text-white group-hover:text-cyan-400 transition-colors">{{ $category->name }}</h3>
                </a>
                @endforeach
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="relative py-20 px-4 bg-gradient-to-r from-cyan-900/30 to-purple-900/30">
        <div class="max-w-4xl mx-auto text-center">
            <h2 class="text-4xl md:text-5xl font-bold mb-6">
                Ready to Start Gaming?
            </h2>
            <p class="text-xl text-gray-300 mb-10">
                Join our community of gamers. Track, discover, and share your favorite games.
            </p>
            <a href="{{ route('register') }}" 
               class="inline-block px-10 py-4 bg-gradient-to-r from-cyan-500 to-blue-600 text-white text-lg font-bold rounded-xl hover:shadow-2xl hover:shadow-cyan-500/50 transition-all duration-300 transform hover:-translate-y-1">
                Create Free Account
            </a>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-950 text-gray-400 py-12 border-t border-gray-800">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Built for gamers, by gamers.</p>
        </div>
    </footer>
</body>
</html>