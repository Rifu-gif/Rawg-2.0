<article class="group bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100">
    <div class="flex flex-col md:flex-row">
        <!-- Content Section -->
        <div class="flex-1 p-6 md:p-8">
            <!-- Category Badge -->
            <div class="mb-3">
                <a href="{{ route('post.byCategory', $post->category) }}" class="inline-block">
                    <span class="px-3 py-1 text-xs font-semibold text-cyan-700 bg-cyan-50 rounded-full hover:bg-cyan-100 transition-colors">
                        {{ $post->category->name }}
                    </span>
                </a>
            </div>
            
            <!-- Title -->
            <a href="{{ route('post.show', ['username' => $post->user->username, 'post' => $post->slug]) }}" class="block">
                <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-3 group-hover:text-cyan-600 transition-colors line-clamp-2">
                    {{ $post->title }}
                </h2>
            </a>
            
            <!-- Excerpt -->
            <p class="text-gray-600 mb-4 line-clamp-3 leading-relaxed">
                {{ Str::words(strip_tags($post->content), 30) }}
            </p>
            
            <!-- Meta Information -->
            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-gray-500">
                <!-- Author -->
                <a href="{{ route('profile.show', $post->user->username) }}" class="flex items-center gap-2 hover:text-cyan-600 transition-colors font-medium">
                    <div class="w-8 h-8 bg-gradient-to-br from-cyan-400 to-blue-500 rounded-full flex items-center justify-center text-white font-bold text-xs">
                        {{ strtoupper(substr($post->user->username, 0, 2)) }}
                    </div>
                    <span>{{ $post->user->username }}</span>
                </a>
                
                <span class="text-gray-300">•</span>
                
                <!-- Date -->
                <time datetime="{{ $post->created_at->toISOString() }}" class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    {{ $post->created_at->format('M d, Y') }}
                </time>
                
                <span class="text-gray-300">•</span>
                
                <!-- Reading Time -->
                <span class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ $post->readTime() }} min read
                </span>
                
                <span class="text-gray-300">•</span>
                
                <!-- Claps -->
                <span class="flex items-center gap-1.5 text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5" />
                    </svg>
                    <span class="font-semibold">{{ $post->claps_count }}</span>
                </span>
            </div>
        </div>
        
        <!-- Image Section -->
        @if($post->imageUrl())
            <a href="{{ route('post.show', ['username' => $post->user->username, 'post' => $post->slug]) }}" class="md:w-72 flex-shrink-0">
                <div class="relative h-48 md:h-full overflow-hidden">
                    <img class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" 
                         src="{{ $post->imageUrl() }}" 
                         alt="{{ $post->title }}"
                         loading="lazy" />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                </div>
            </a>
        @endif
    </div>
</article>