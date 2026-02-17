<x-app-layout>
<div class="py-12">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
        <!-- Back Button -->
        <a href="{{ route('games.index') }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 mb-6 inline-flex items-center">
            ← Back to Games
        </a>

        <!-- Game Header -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden mb-8">
            <div class="relative h-80 overflow-hidden bg-gray-200 dark:bg-gray-700">
                @if($game['background_image'])
                    <img src="{{ $game['background_image'] }}" alt="{{ $game['name'] }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center bg-gray-300 dark:bg-gray-600">
                        <span class="text-gray-500">No Image</span>
                    </div>
                @endif
            </div>

            <div class="p-8">
                <div class="flex justify-between items-start mb-4">
                    <h1 class="text-4xl font-bold text-gray-900 dark:text-white">{{ $game['name'] }}</h1>
                    
                    @auth
                        <button class="favorite-btn p-3 bg-yellow-100 dark:bg-yellow-900 rounded-full shadow-md hover:bg-yellow-200 transition-colors" 
                                data-game-id="{{ $game['id'] }}"
                                data-is-favorited="{{ $isFavorited ? 'true' : 'false' }}"
                                title="{{ $isFavorited ? 'Remove from favorites' : 'Add to favorites' }}">
                            <span class="text-4xl {{ $isFavorited ? 'text-yellow-400' : 'text-gray-400' }}">★</span>
                        </button>
                    @endauth
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <!-- Rating -->
                    @if($game['rating'])
                        <div>
                            <p class="text-gray-600 dark:text-gray-400 text-sm mb-2">Rating</p>
                            <div class="flex items-center">
                                <span class="text-yellow-400 text-2xl">★</span>
                                <span class="ml-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $game['rating'] }}/5</span>
                            </div>
                        </div>
                    @endif

                    <!-- Release Date -->
                    @if($game['released'])
                        <div>
                            <p class="text-gray-600 dark:text-gray-400 text-sm mb-2">Release Date</p>
                            <p class="text-xl font-semibold text-gray-900 dark:text-white">
                                {{ \Carbon\Carbon::parse($game['released'])->format('M d, Y') }}
                            </p>
                        </div>
                    @endif

                    <!-- Metacritic Score -->
                    @if($game['metacritic'])
                        <div>
                            <p class="text-gray-600 dark:text-gray-400 text-sm mb-2">Metacritic</p>
                            <p class="text-xl font-semibold text-gray-900 dark:text-white">{{ $game['metacritic'] }}/100</p>
                        </div>
                    @endif

                    <!-- Playtime -->
                    @if(isset($game['playtime']) && $game['playtime'] > 0)
                        <div>
                            <p class="text-gray-600 dark:text-gray-400 text-sm mb-2">Average Playtime</p>
                            <p class="text-xl font-semibold text-gray-900 dark:text-white">{{ $game['playtime'] }} hours</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <!-- Description -->
                @if($game['description'])
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-8 mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">About</h2>
                        <div class="text-gray-700 dark:text-gray-300 prose dark:prose-invert prose-sm max-w-none">
                            {!! $game['description'] !!}
                        </div>
                    </div>
                @endif

                <!-- Platforms -->
                @if(isset($game['platforms']) && count($game['platforms']) > 0)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-8 mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Platforms</h2>
                        <div class="flex flex-wrap gap-2">
                            @foreach($game['platforms'] as $platform)
                                <span class="inline-block px-4 py-2 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 font-medium rounded-lg">
                                    {{ $platform['platform']['name'] }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Genres -->
                @if(isset($game['genres']) && count($game['genres']) > 0)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-8 mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Genres</h2>
                        <div class="flex flex-wrap gap-2">
                            @foreach($game['genres'] as $genre)
                                <span class="inline-block px-4 py-2 bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 font-medium rounded-lg">
                                    {{ $genre['name'] }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Screenshots -->
                @if(count($screenshots) > 0)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-8">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Screenshots</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($screenshots as $screenshot)
                                <div class="overflow-hidden rounded-lg">
                                    <img src="{{ $screenshot['image'] }}" alt="Screenshot" class="w-full h-48 object-cover hover:scale-105 transition-transform duration-200">
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Developers & Publishers -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
                    @if(isset($game['developers']) && count($game['developers']) > 0)
                        <div class="mb-6">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-3">Developers</h3>
                            <ul class="space-y-2">
                                @foreach($game['developers'] as $developer)
                                    <li class="text-gray-700 dark:text-gray-300">{{ $developer['name'] }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(isset($game['publishers']) && count($game['publishers']) > 0)
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-3">Publishers</h3>
                            <ul class="space-y-2">
                                @foreach($game['publishers'] as $publisher)
                                    <li class="text-gray-700 dark:text-gray-300">{{ $publisher['name'] }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                <!-- Game Details -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Game Details</h3>
                    <div class="space-y-3 text-sm">
                        @if(isset($game['ratings']) && count($game['ratings']) > 0)
                            <div>
                                <p class="text-gray-600 dark:text-gray-400">Age Rating</p>
                                <p class="font-semibold text-gray-900 dark:text-white">
                                    {{ $game['ratings'][0]['title'] ?? 'N/A' }}
                                </p>
                            </div>
                        @endif

                        @if(isset($game['updated']))
                            <div>
                                <p class="text-gray-600 dark:text-gray-400">Last Updated</p>
                                <p class="font-semibold text-gray-900 dark:text-white">
                                    {{ \Carbon\Carbon::parse($game['updated'])->format('M d, Y') }}
                                </p>
                            </div>
                        @endif

                        @if(isset($game['website']) && $game['website'])
                            <div>
                                <a href="{{ $game['website'] }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-semibold">
                                    Official Website →
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
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
                    button.classList.remove('bg-yellow-100');
                    button.classList.add('bg-yellow-100');
                    button.title = 'Remove from favorites';
                } else {
                    button.dataset.isFavorited = 'false';
                    star.classList.remove('text-yellow-400');
                    star.classList.add('text-gray-400');
                    button.classList.remove('bg-yellow-200');
                    button.title = 'Add to favorites';
                }
                
                // Show toast message
                const message = document.createElement('div');
                message.className = 'fixed top-4 right-4 bg-blue-500 text-white px-6 py-3 rounded-lg shadow-lg';
                message.textContent = data.message;
                document.body.appendChild(message);
                
                setTimeout(() => {
                    message.remove();
                }, 2000);
            }
        } catch (error) {
            console.error('Error toggling favorite:', error);
        }
    });
});
</script>
</x-app-layout>
