<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-blue-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">
        <!-- Header Section -->
        <div class="bg-gradient-to-r from-gray-900 via-gray-800 to-gray-900 dark:from-gray-950 dark:via-gray-900 dark:to-gray-950 text-white py-5 border-b border-gray-700">
            <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold mb-1">Discover & Share</h1>
                    <p class="text-gray-400 text-sm">Explore amazing articles from our community</p>
                </div>
            </div>
        </div>

        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-12">
            
            <!-- Category Filter -->
            <div class="mb-12">
                <div class="bg-gradient-to-r from-gray-800 to-gray-700 dark:from-gray-800 dark:to-gray-700 rounded-2xl shadow-xl p-8 border border-gray-700">
                    <h3 class="text-white font-bold mb-6 text-lg">
                        Filter by Category
                    </h3>
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('dashboard') }}" 
                           class="px-6 py-3 rounded-lg font-semibold transition-all duration-200 inline-flex items-center gap-2
                                  {{ !isset($selectedCategory) ? 'bg-cyan-500 text-white shadow-lg scale-105' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                            All Posts
                        </a>
                        @foreach($categories as $category)
                            <a href="{{ route('post.byCategory', $category) }}" 
                               class="px-6 py-3 rounded-lg font-semibold transition-all duration-200 inline-flex items-center gap-2
                                      {{ isset($selectedCategory) && $selectedCategory->id === $category->id ? 'bg-cyan-500 text-white shadow-lg scale-105' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                                {{ $category->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Posts Grid -->
            @if($posts->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
                    @foreach($posts as $post)
                        <article class="group bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 flex flex-col border border-gray-200 dark:border-gray-700">
                            
                            <!-- Post Image -->
                            <div class="relative h-64 overflow-hidden bg-gray-200 dark:bg-gray-700">
                                @if($post->imageUrl())
                                    <img src="{{ $post->imageUrl('large') }}" 
                                         alt="{{ $post->title }}"
                                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                @else
                                    <div class="h-full bg-gradient-to-br from-cyan-400 to-blue-500 flex items-center justify-center">
                                        <svg class="w-24 h-24 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                @endif
                                
                                <!-- Category Badge Overlay -->
                                @if($post->category)
                                    <div class="absolute top-4 left-4">
                                        <a href="{{ route('post.byCategory', $post->category) }}" 
                                           class="inline-block">
                                            <span class="px-4 py-2 text-sm font-bold text-white bg-gradient-to-r from-cyan-500 to-blue-600 rounded-full shadow-lg hover:shadow-xl transition-all">
                                                {{ $post->category->name }}
                                            </span>
                                        </a>
                                    </div>
                                @endif
                            </div>

                            <!-- Post Content -->
                            <div class="p-7 flex flex-col flex-grow">

                                <!-- Title -->
                                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 line-clamp-2 group-hover:text-cyan-600 dark:group-hover:text-cyan-400 transition-colors">
                                    <a href="{{ route('post.show', ['username' => $post->user->username, 'post' => $post->slug]) }}">
                                        {{ $post->title }}
                                    </a>
                                </h2>

                                <!-- Excerpt -->
                                <p class="text-gray-600 dark:text-gray-400 text-base mb-6 line-clamp-3 flex-grow leading-relaxed">
                                    {{ Str::limit(strip_tags($post->content), 120) }}
                                </p>

                                <!-- Author & Meta -->
                                <div class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-700 mt-auto">
                                    <a href="{{ route('profile.show', $post->user) }}" class="flex items-center space-x-3 flex-1 group/author">
                                        <!-- Avatar -->
                                        <x-user-avatar :user="$post->user" size="w-12 h-12 shadow-md group-hover/author:shadow-lg transition-shadow" />
                                        
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-gray-900 dark:text-white group-hover/author:text-cyan-600 dark:group-hover/author:text-cyan-400 transition-colors">
                                                {{ $post->user->name }}
                                            </span>
                                            <div class="flex items-center space-x-2 text-xs text-gray-500 dark:text-gray-400">
                                                <time>{{ $post->published_at ? $post->published_at->format('M d') : 'Not published' }}</time>
                                                <span>•</span>
                                                <span>{{ $post->readTime() }}m</span>
                                            </div>
                                        </div>
                                    </a>

                                    <!-- Claps -->
                                    <div class="flex items-center space-x-2 bg-gray-100 dark:bg-gray-700 rounded-full px-4 py-2">
                                        <button class="clap-btn group/clap transition-transform hover:scale-110" data-post="{{ $post->id }}">
                                            <svg class="w-5 h-5 text-gray-500 group-hover/clap:text-cyan-500 transition-colors" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"></path>
                                            </svg>
                                        </button>
                                        <span class="clap-count text-sm font-bold text-gray-900 dark:text-white">{{ $post->claps_count }}</span>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($posts->hasPages())
                    <div class="mt-16 flex justify-center">
                        <nav class="inline-flex rounded-xl shadow-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 overflow-hidden">
                            {{-- Previous Page Link --}}
                            @if ($posts->onFirstPage())
                                <span class="px-5 py-4 text-sm font-semibold text-gray-400 bg-gray-50 dark:bg-gray-700 cursor-not-allowed">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </span>
                            @else
                                <a href="{{ $posts->previousPageUrl() }}" class="px-5 py-4 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-cyan-50 dark:hover:bg-gray-700 hover:text-cyan-600 dark:hover:text-cyan-400 transition-colors">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </a>
                            @endif

                            {{-- Page Numbers --}}
                            @foreach(range(1, $posts->lastPage()) as $page)
                                @if ($page == $posts->currentPage())
                                    <span class="px-5 py-4 text-sm font-bold text-white bg-cyan-500 border-x border-cyan-600">
                                        {{ $page }}
                                    </span>
                                @else
                                    <a href="{{ $posts->url($page) }}" class="px-5 py-4 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-cyan-50 dark:hover:bg-gray-700 hover:text-cyan-600 dark:hover:text-cyan-400 border-x border-gray-200 dark:border-gray-700 transition-colors">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach

                            {{-- Next Page Link --}}
                            @if ($posts->hasMorePages())
                                <a href="{{ $posts->nextPageUrl() }}" class="px-5 py-4 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-cyan-50 dark:hover:bg-gray-700 hover:text-cyan-600 dark:hover:text-cyan-400 transition-colors">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </a>
                            @else
                                <span class="px-5 py-4 text-sm font-semibold text-gray-400 bg-gray-50 dark:bg-gray-700 cursor-not-allowed">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </span>
                            @endif
                        </nav>
                    </div>
                @endif

            @else
                <!-- No Posts State -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl p-20 text-center border border-gray-200 dark:border-gray-700">
                    <div class="w-32 h-32 mx-auto mb-8 text-gray-300">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                        </svg>
                    </div>
                    <h3 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">No posts yet</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-lg mb-10">Be the first to share your thoughts with the community.</p>
                    <a href="{{ route('post.create') }}" 
                       class="inline-flex items-center gap-3 px-10 py-4 bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-bold rounded-xl hover:shadow-2xl shadow-lg transition-all duration-200 text-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Create Your First Post
                    </a>
                </div>
            @endif

        </div>
    </div>

    <script>
        document.querySelectorAll('.clap-btn').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                const postId = btn.dataset.post;
                const clapCount = btn.closest('.flex').querySelector('.clap-count');
                
                try {
                    const response = await fetch(`/clap/${postId}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                    });
                    
                    if (response.ok) {
                        const data = await response.json();
                        clapCount.textContent = data.claps_count;
                        btn.classList.add('animate-bounce');
                        setTimeout(() => btn.classList.remove('animate-bounce'), 600);
                    }
                } catch (error) {
                    console.error('Clap error:', error);
                }
            });
        });
    </script>
</x-app-layout>
