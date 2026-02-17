<x-app-layout>
<div class="py-12 bg-gray-100 dark:bg-gray-900">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Dark Header Section -->
        <div class="bg-gradient-to-r from-gray-900 via-gray-800 to-gray-900 dark:from-gray-950 dark:via-gray-900 dark:to-gray-950 rounded-lg shadow-xl p-8 mb-8">
            <h1 class="text-4xl md:text-5xl font-bold text-white mb-2">★ My Favorite Games</h1>
            <p class="text-gray-300 text-lg">Your collection of favorite games</p>
        </div>

        <!-- Favorites Grid -->
        @if(count($favorites) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                @foreach($favorites as $favorite)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow group">
                        <!-- Game Image Container with Remove Button -->
                        <div class="relative h-48 overflow-hidden bg-gray-200 dark:bg-gray-700">
                            <a href="{{ route('games.show', $favorite->game_id) }}" class="h-full block">
                                @if($favorite->game_image)
                                    <img src="{{ $favorite->game_image }}" alt="{{ $favorite->game_name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-gray-300 dark:bg-gray-600">
                                        <span class="text-gray-500">No Image</span>
                                    </div>
                                @endif
                            </a>
                            
                            <!-- Remove from Favorites Button -->
                            <button class="absolute top-2 right-2 favorite-btn remove-favorite p-2 bg-white dark:bg-gray-900 rounded-full shadow-md hover:bg-red-400 transition-colors" 
                                    data-game-id="{{ $favorite->game_id }}"
                                    title="Remove from favorites">
                                <span class="text-2xl text-yellow-400">★</span>
                            </button>
                        </div>

                        <!-- Game Info -->
                        <div class="p-4">
                            <a href="{{ route('games.show', $favorite->game_id) }}" class="block hover:text-blue-600">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white line-clamp-2 mb-2">{{ $favorite->game_name }}</h3>
                            </a>
                            
                            <!-- Rating -->
                            @if($favorite->game_rating)
                                <div class="flex items-center mb-3">
                                    <span class="text-yellow-400">★</span>
                                    <span class="ml-1 text-sm text-gray-600 dark:text-gray-400">{{ $favorite->game_rating }}/5</span>
                                </div>
                            @endif

                            <!-- Added Date -->
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Added {{ $favorite->created_at->format('M d, Y') }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <div class="flex justify-center gap-2">
                    @if($favorites->onFirstPage())
                        <span class="px-4 py-2 text-gray-500">← Previous</span>
                    @else
                        <a href="{{ $favorites->previousPageUrl() }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                            ← Previous
                        </a>
                    @endif

                    <span class="px-4 py-2 text-gray-700 dark:text-gray-300">
                        Page {{ $favorites->currentPage() }}
                    </span>

                    @if($favorites->hasMorePages())
                        <a href="{{ $favorites->nextPageUrl() }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                            Next →
                        </a>
                    @else
                        <span class="px-4 py-2 text-gray-500">Next →</span>
                    @endif
                </div>
            </div>
        @else
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-12 text-center">
                <div class="text-6xl mb-4">★</div>
                <p class="text-gray-600 dark:text-gray-400 text-lg mb-6">You haven't added any favorite games yet.</p>
                <a href="{{ route('games.index') }}" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors inline-block">
                    Browse Games
                </a>
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
        
        try {
            const response = await fetch(`/games/${gameId}/toggle-favorite`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                },
            });
            
            const data = await response.json();
            
            if (response.ok && !data.favorited) {
                // Remove the card from the page
                button.closest('[class*="grid"]').parentElement.remove();
                
                // Show message
                const message = document.createElement('div');
                message.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg';
                message.textContent = 'Removed from favorites';
                document.body.appendChild(message);
                
                setTimeout(() => {
                    message.remove();
                    // Reload to show updated list
                    location.reload();
                }, 1000);
            }
        } catch (error) {
            console.error('Error toggling favorite:', error);
        }
    });
});
</script>
</x-app-layout>
