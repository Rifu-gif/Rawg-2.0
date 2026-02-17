<x-app-layout>
<div class="py-12 bg-gray-100 dark:bg-gray-900">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Dark Header Section -->
        <div class="bg-gradient-to-r from-gray-900 via-gray-800 to-gray-900 dark:from-gray-950 dark:via-gray-900 dark:to-gray-950 rounded-lg shadow-xl p-8 mb-8">
            <h1 class="text-4xl md:text-5xl font-bold text-white mb-2">Games Database</h1>
            <p class="text-gray-300 text-lg">Explore thousands of games from the RAWG database</p>
        </div>

        <!-- Search and Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-8">
            <form action="{{ route('games.index') }}" method="GET" class="space-y-4">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search Games</label>
                    <input type="text" id="search" name="search" placeholder="Search for games..." 
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                        value="{{ request('search') }}">
                </div>

                <!-- Filters Row -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Ordering -->
                    <div>
                        <label for="ordering" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sort By</label>
                        <select id="ordering" name="ordering" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="-rating" {{ request('ordering') === '-rating' ? 'selected' : '' }}>Top Rated</option>
                            <option value="-released" {{ request('ordering') === '-released' ? 'selected' : '' }}>Newest First</option>
                            <option value="released" {{ request('ordering') === 'released' ? 'selected' : '' }}>Oldest First</option>
                            <option value="name" {{ request('ordering') === 'name' ? 'selected' : '' }}>Alphabetical A-Z</option>
                            <option value="-name" {{ request('ordering') === '-name' ? 'selected' : '' }}>Alphabetical Z-A</option>
                        </select>
                    </div>

                    <!-- Genre Filter -->
                    <div>
                        <label for="genre" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Genre</label>
                        <select id="genre" name="genre" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="">All Genres</option>
                            @foreach($genres as $genre)
                                <option value="{{ $genre['id'] }}" {{ request('genre') == $genre['id'] ? 'selected' : '' }}>
                                    {{ $genre['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Platform Filter -->
                    <div>
                        <label for="platform" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Platform</label>
                        <select id="platform" name="platform" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="">All Platforms</option>
                            @foreach($platforms as $platform)
                                <option value="{{ $platform['id'] }}" {{ request('platform') == $platform['id'] ? 'selected' : '' }}>
                                    {{ $platform['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex gap-2 flex-wrap">
                    <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                        Filter Results
                    </button>
                    <a href="{{ route('games.index') }}" class="px-6 py-2 bg-gray-300 hover:bg-gray-400 dark:bg-gray-600 dark:hover:bg-gray-700 text-gray-900 dark:text-white font-medium rounded-lg transition-colors">
                        Clear Filters
                    </a>
                    @auth
                        <a href="{{ route('games.favorites') }}" class="px-6 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-medium rounded-lg transition-colors">
                            ★ My Favorites
                        </a>
                    @endauth
                </div>
            </form>
        </div>

        <!-- Games Grid -->
        @if(count($games) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                @foreach($games as $game)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow group">
                        <!-- Game Image Container with Favorite Button -->
                        <div class="relative h-48 overflow-hidden bg-gray-200 dark:bg-gray-700">
                            <a href="{{ route('games.show', $game['id']) }}" class="h-full block">
                                @if($game['background_image'])
                                    <img src="{{ $game['background_image'] }}" alt="{{ $game['name'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-gray-300 dark:bg-gray-600">
                                        <span class="text-gray-500">No Image</span>
                                    </div>
                                @endif
                            </a>
                            
                            <!-- Favorite Button -->
                            @auth
                                <button class="absolute top-2 right-2 favorite-btn p-2 bg-white dark:bg-gray-900 rounded-full shadow-md hover:bg-yellow-400 transition-colors" 
                                        data-game-id="{{ $game['id'] }}"
                                        data-is-favorited="{{ in_array($game['id'], $favoriteGameIds) ? 'true' : 'false' }}">
                                    <span class="text-2xl {{ in_array($game['id'], $favoriteGameIds) ? 'text-yellow-400' : 'text-gray-400' }}">★</span>
                                </button>
                            @else
                                <button class="absolute top-2 right-2 p-2 bg-white dark:bg-gray-900 rounded-full shadow-md" onclick="window.location.href='{{ route('login') }}'">
                                    <span class="text-2xl text-gray-400">★</span>
                                </button>
                            @endauth
                        </div>

                        <!-- Game Info -->
                        <div class="p-4">
                            <a href="{{ route('games.show', $game['id']) }}" class="block hover:text-blue-600">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white line-clamp-2 mb-2">{{ $game['name'] }}</h3>
                            </a>
                            
                            <!-- Rating -->
                            @if($game['rating'])
                                <div class="flex items-center mb-3">
                                    <span class="text-yellow-400">★</span>
                                    <span class="ml-1 text-sm text-gray-600 dark:text-gray-400">{{ $game['rating'] }}/5</span>
                                </div>
                            @endif

                            <!-- Release Date -->
                            @if($game['released'])
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                                    {{ \Carbon\Carbon::parse($game['released'])->format('M d, Y') }}
                                </p>
                            @endif

                            <!-- Platforms -->
                            @if(isset($game['platforms']) && count($game['platforms']) > 0)
                                <div class="flex flex-wrap gap-1">
                                    @foreach(array_slice($game['platforms'], 0, 3) as $platform)
                                        <span class="inline-block px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-xs rounded">
                                            {{ $platform['platform']['name'] }}
                                        </span>
                                    @endforeach
                                    @if(count($game['platforms']) > 3)
                                        <span class="inline-block px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-xs rounded">
                                            +{{ count($game['platforms']) - 3 }}
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination Info -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 text-center">
                <p class="text-gray-600 dark:text-gray-400 mb-4">
                    Showing results from a database of {{ number_format($total) }} games
                </p>
                
                <!-- Pagination Links -->
                <div class="flex justify-center gap-2">
                    @if($currentPage > 1)
                        <a href="{{ route('games.index', array_merge(request()->query(), ['page' => $currentPage - 1])) }}" 
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                            ← Previous
                        </a>
                    @endif

                    <span class="px-4 py-2 text-gray-700 dark:text-gray-300">
                        Page {{ $currentPage }}
                    </span>

                    @if($nextPage)
                        <a href="{{ route('games.index', array_merge(request()->query(), ['page' => $currentPage + 1])) }}" 
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                            Next →
                        </a>
                    @endif
                </div>
            </div>
        @else
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-8 text-center">
                <p class="text-gray-600 dark:text-gray-400 text-lg">No games found. Try adjusting your filters or search query.</p>
            </div>
        @endif
    </div>
</div>

<script>
document.querySelectorAll('.favorite-btn').forEach(button => {
    button.addEventListener('click', async (e) => {
        e.preventDefault();
        e.stopPropagation();
        
        const gameId = button.dataset.gameId;
        const isFavorited = button.dataset.isFavorited === 'true';
        
        try {
            const response = await fetch(`/games/${gameId}/toggle-favorite`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                },
            });
            
            const data = await response.json();
            
            if (response.ok) {
                const star = button.querySelector('span');
                if (data.favorited) {
                    button.dataset.isFavorited = 'true';
                    star.classList.remove('text-gray-400');
                    star.classList.add('text-yellow-400');
                    button.classList.add('bg-yellow-100');
                } else {
                    button.dataset.isFavorited = 'false';
                    star.classList.remove('text-yellow-400');
                    star.classList.add('text-gray-400');
                    button.classList.remove('bg-yellow-100');
                }
            }
        } catch (error) {
            console.error('Error toggling favorite:', error);
        }
    });
});
</script>
</x-app-layout>
