<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-blue-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">
        <!-- Profile Header with Cover -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
            <div class="relative bg-gradient-to-br from-blue-950 via-blue-900 to-blue-800 rounded-2xl shadow-lg overflow-hidden" style="background-color: #0b1f4d;">
                <!-- Animated Background Gradient -->
                <div class="absolute inset-0 opacity-30">
                    <div class="absolute top-0 -left-4 w-72 h-72 bg-cyan-500 rounded-full mix-blend-multiply filter blur-3xl animate-blob"></div>
                    <div class="absolute top-0 -right-4 w-72 h-72 bg-blue-500 rounded-full mix-blend-multiply filter blur-3xl animate-blob animation-delay-2000"></div>
                </div>
                
                <div class="relative px-6 sm:px-8 lg:px-10 py-6">
                <x-follow-ctr :user="$user">
                    <div class="flex flex-col md:flex-row items-start md:items-center gap-6 lg:gap-8">
                        <!-- Avatar -->
                        <div class="relative group flex-shrink-0">
                            @if($user->imageUrl())
                                <img src="{{ $user->imageUrl() }}"
                                     alt="{{ $user->name }}"
                                     class="relative w-32 h-32 md:w-40 md:h-40 rounded-2xl object-cover shadow-2xl ring-4 ring-white/20 group-hover:shadow-cyan-500/50 group-hover:scale-105 transition-all duration-300">
                            @else
                                <div class="relative w-32 h-32 md:w-40 md:h-40 bg-gradient-to-br from-cyan-400 to-blue-600 rounded-2xl flex items-center justify-center shadow-2xl ring-4 ring-white/20 group-hover:shadow-cyan-500/50 group-hover:scale-105 transition-all duration-300">
                                    <span class="text-6xl font-bold text-white">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        <!-- User Info & Actions -->
                        <div class="flex-1">
                            <h1 class="text-3xl md:text-4xl font-black text-white mb-1 tracking-tight">
                                {{ $user->name }}
                            </h1>
                            <p class="text-cyan-300 text-lg mb-4 font-semibold">{{ '@' . $user->username }}</p>
                            
                            <!-- Bio -->
                            @if($user->bio)
                                <p class="text-cyan-100 text-base mb-5 leading-relaxed max-w-2xl">{{ $user->bio }}</p>
                            @endif
                            
                            <!-- Stats Row with Better Design -->
                            <div class="flex flex-wrap gap-4 mb-5">
                                <div class="bg-blue-900/50 backdrop-blur-md rounded-xl px-4 py-2 border border-blue-700/50">
                                    <div class="text-lg font-black text-white">{{ $posts->total() }}</div>
                                    <div class="text-cyan-200 text-xs font-bold uppercase tracking-wider">Games</div>
                                </div>
                                <div class="bg-blue-900/50 backdrop-blur-md rounded-xl px-4 py-2 border border-blue-700/50">
                                    <div class="text-lg font-black text-white" x-text="followersCount"></div>
                                    <div class="text-cyan-200 text-xs font-bold uppercase tracking-wider">Followers</div>
                                </div>
                                <div class="bg-blue-900/50 backdrop-blur-md rounded-xl px-4 py-2 border border-blue-700/50">
                                    <div class="text-lg font-black text-white">{{ $user->following->count() }}</div>
                                    <div class="text-cyan-200 text-xs font-bold uppercase tracking-wider">Following</div>
                                </div>
                            </div>

                            <!-- Follow Button -->
                            @auth
                                @if(auth()->user()->id !== $user->id)
                                    <div class="flex gap-3">
                                        <button 
                                            @click="follow()" 
                                            x-text="following ? 'Following' : '+ Follow'"
                                            :class="following ? 'bg-white/15 text-white hover:bg-white/25 border-white/40' : 'bg-gradient-to-r from-cyan-500 to-blue-600 text-white hover:from-cyan-600 hover:to-blue-700 border-transparent shadow-lg hover:shadow-cyan-500/50'"
                                            class="px-6 py-2 font-bold text-sm rounded-lg transition-all duration-300 border-2 hover:scale-105 flex items-center gap-1">
                                        </button>
                                    </div>
                                @else
                                    <p class="text-cyan-300 text-sm font-semibold">This is your profile</p>
                                @endif
                            @endauth
                        </div>
                    </div>
                </x-follow-ctr>
                </div>
            </div>
        </div>

        <!-- Games Grid Section -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Section Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-4xl font-black text-gray-900 dark:text-white">
                        Games Collection
                        <span class="text-gray-400 dark:text-gray-500 text-2xl ml-3">({{ $posts->total() }})</span>
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-1 text-base">Explore {{ $user->name }}'s favorite games</p>
                </div>
            </div>

            @if($posts->count() > 0)
                <!-- Games Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($posts as $post)
                        <article class="group bg-white dark:bg-gray-800 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden border border-gray-200 dark:border-gray-700 hover:-translate-y-2">
                            <!-- Game Image -->
                            <a href="{{ route('post.show', ['username' => $post->user->username, 'post' => $post->slug]) }}" class="block relative overflow-hidden bg-gray-900 h-56">
                                @if($post->imageUrl())
                                    <img src="{{ $post->imageUrl() }}" 
                                         alt="{{ $post->title }}" 
                                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-gray-700 to-gray-900 flex items-center justify-center">
                                        <svg class="w-20 h-20 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"></path>
                                        </svg>
                                    </div>
                                @endif
                                
                                <!-- Category Badge Overlay -->
                                <div class="absolute top-4 left-4">
                                    <span class="px-3 py-1 text-xs font-bold text-white bg-gradient-to-r from-cyan-500 to-blue-600 rounded-lg shadow-lg">
                                        {{ $post->category->name }}
                                    </span>
                                </div>
                            </a>

                            <!-- Game Info -->
                            <div class="p-5">
                                <a href="{{ route('post.show', ['username' => $post->user->username, 'post' => $post->slug]) }}" class="block">
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white group-hover:text-cyan-600 dark:group-hover:text-cyan-400 transition-colors mb-2 line-clamp-2">
                                        {{ $post->title }}
                                    </h3>
                                </a>

                                <p class="text-gray-600 dark:text-gray-400 text-xs mb-4 line-clamp-2 leading-relaxed">
                                    {{ Str::words(strip_tags($post->content), 15) }}
                                </p>

                                <!-- Meta Info -->
                                <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 pt-3 border-t border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center gap-3">
                                        <span class="flex items-center gap-1 font-semibold">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            {{ $post->readTime() }}m
                                        </span>
                                        <span class="flex items-center gap-1 font-semibold">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"></path>
                                            </svg>
                                            {{ $post->claps_count }}
                                        </span>
                                    </div>
                                    <span class="text-xs font-medium">{{ $post->created_at->format('M d') }}</span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($posts->hasPages())
                    <div class="mt-8">
                        {{ $posts->links() }}
                    </div>
                @endif
            @else
                <!-- Empty State -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-12 text-center border border-gray-200 dark:border-gray-700">
                    <div class="w-32 h-32 mx-auto bg-gradient-to-br from-cyan-100 to-blue-100 dark:from-cyan-900/30 dark:to-blue-900/30 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-16 h-16 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">No games yet</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6 text-base">{{ $user->name }} hasn't added any games to their collection.</p>
                    
                    @auth
                        @if(auth()->user()->id === $user->id)
                            <a href="{{ route('post.create') }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-bold rounded-lg hover:shadow-lg hover:scale-105 transition-all text-base">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Add Your First Game
                            </a>
                        @endif
                    @endauth
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

