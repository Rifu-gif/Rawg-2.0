<x-app-layout>
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-2">
                Search Results
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                @if($query)
                    Results for "<strong>{{ $query }}</strong>" ({{ number_format($total) }} games found)
                @else
                    No search query provided
                @endif
            </p>
        </div>

        <!-- Back to Games Link -->
        <a href="{{ route('games.index') }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 mb-6 inline-flex items-center">
            ← Back to Games
        </a>

        <!-- Games Grid -->
        @if(count($games) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                @foreach($games as $game)
                    <a href="{{ route('games.show', $game['id']) }}" class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow transform hover:scale-105 duration-200">
                        <!-- Game Image -->
                        <div class="relative h-48 overflow-hidden bg-gray-200 dark:bg-gray-700">
                            @if($game['background_image'])
                                <img src="{{ $game['background_image'] }}" alt="{{ $game['name'] }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gray-300 dark:bg-gray-600">
                                    <span class="text-gray-500">No Image</span>
                                </div>
                            @endif
                        </div>

                        <!-- Game Info -->
                        <div class="p-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white line-clamp-2 mb-2">{{ $game['name'] }}</h3>
                            
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
                    </a>
                @endforeach
            </div>

            <!-- Pagination Info -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 text-center">
                <!-- Pagination Links -->
                <div class="flex justify-center gap-2">
                    @if($currentPage > 1)
                        <a href="{{ route('games.search', array_merge(request()->query(), ['page' => $currentPage - 1])) }}" 
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                            ← Previous
                        </a>
                    @endif

                    <span class="px-4 py-2 text-gray-700 dark:text-gray-300">
                        Page {{ $currentPage }}
                    </span>

                    <a href="{{ route('games.search', array_merge(request()->query(), ['page' => $currentPage + 1])) }}" 
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                        Next →
                    </a>
                </div>
            </div>
        @else
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-8 text-center">
                @if($query)
                    <p class="text-gray-600 dark:text-gray-400 text-lg mb-4">
                        No games found for "<strong>{{ $query }}</strong>". Try a different search query.
                    </p>
                @else
                    <p class="text-gray-600 dark:text-gray-400 text-lg">Please provide a search query.</p>
                @endif
                <a href="{{ route('games.index') }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-semibold">
                    Browse all games →
                </a>
            </div>
        @endif
    </div>
</div>
</x-app-layout>
