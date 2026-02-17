<nav x-data="{ open: false, searchOpen: false }" class="bg-white border-b border-gray-100 shadow-md sticky top-0 z-50">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20">
            <div class="flex items-center">
                <!-- Logo -->
                <a href="{{ route('dashboard') }}" class="flex items-center">
                    <x-application-logo class="block h-14 w-auto fill-current text-gray-800" />
                </a>
            </div>

            <div class="flex items-center space-x-6">
                <!-- User Search -->
                <div class="hidden md:block relative">
                    <div class="relative" @click.outside="searchOpen = false">
                        <input x-on:focus="searchOpen = true" 
                               x-on:input="searchOpen = true"
                               type="text" 
                               id="userSearch"
                               placeholder="Search users..." 
                               class="w-48 px-4 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all">
                        <svg class="absolute right-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <!-- Search Results -->
                        <div x-show="searchOpen" class="absolute top-full mt-2 w-64 bg-white border border-gray-200 rounded-lg shadow-xl z-50 max-h-96 overflow-y-auto" id="searchResults" style="display: none;">
                        </div>
                    </div>
                </div>

                <!-- Games Link -->
                <a href="{{ route('games.index') }}" class="hidden sm:flex items-center px-5 py-3 rounded-lg text-gray-700 font-semibold hover:bg-cyan-50 hover:text-cyan-600 transition-all duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    <span class="text-base">Games</span>
                </a>

                <!-- Add Game Button -->
                <a href="{{ route('post.create') }}" class="hidden sm:flex items-center px-8 py-3.5 bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-bold rounded-lg hover:shadow-lg hover:scale-105 transition-all duration-300 text-lg">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    New Post
                </a>

                @auth
                    <!-- Settings Dropdown -->
                    <div class="hidden sm:flex sm:items-center">
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="flex items-center space-x-2 px-3 py-2 rounded-lg hover:bg-gray-50 transition-all duration-200 group">
                                    <x-user-avatar :user="Auth::user()" size="w-9 h-9 text-sm group-hover:shadow-md transition-all" />
                                    <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900">{{ Auth::user()->name }}</span>
                                    <svg class="fill-current h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <div class="px-4 py-3 border-b border-gray-100">
                                    <p class="text-sm font-semibold text-gray-900">{{ Auth::user()->name }}</p>
                                    <p class="text-xs text-gray-500">{{ '@' . Auth::user()->username }}</p>
                                </div>
                                
                                <x-dropdown-link :href="route('profile.show', Auth::user()->username)" class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    {{ __('View Profile') }}
                                </x-dropdown-link>

                                <x-dropdown-link :href="route('myPosts')" class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"></path>
                                    </svg>
                                    {{ __('My Games') }}
                                </x-dropdown-link>

                                <x-dropdown-link :href="route('games.favorites')" class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2l-2.81 6.63L2 9.24l5.46 4.73L5.82 21z"></path>
                                    </svg>
                                    {{ __('Favorite Games') }}
                                </x-dropdown-link>

                                <x-dropdown-link :href="route('profile.edit')" class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    {{ __('Settings') }}
                                </x-dropdown-link>

                                <div class="border-t border-gray-100"></div>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                            class="block w-full px-4 py-2 text-start text-sm leading-5 text-red-600 hover:bg-red-50 focus:outline-none focus:bg-red-50 transition duration-150 ease-in-out flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                        </svg>
                                        {{ __('Log Out') }}
                                    </button>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>
                @endauth

                @guest
                    <a href="{{ route('login') }}" class="hidden sm:flex items-center px-4 py-2 text-sm font-medium text-gray-700 hover:text-cyan-600 transition-colors">
                        Sign In
                    </a>
                    <a href="{{ route('register') }}" class="hidden sm:flex items-center px-5 py-2.5 bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-semibold rounded-lg hover:shadow-lg hover:scale-105 transition-all duration-300">
                        Get Started
                    </a>
                @endguest

                <!-- Hamburger -->
                <div class="flex items-center sm:hidden">
                    <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-lg text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none transition-all duration-150">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-gray-100">
        <div class="pt-2 pb-3 space-y-1">
            <a href="{{ route('games.index') }}" class="flex items-center px-4 py-3 text-base font-semibold text-gray-700 hover:bg-cyan-50 hover:text-cyan-600 transition-colors">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                Games
            </a>

            <a href="{{ route('post.create') }}" class="flex items-center px-4 py-3 text-base font-semibold text-cyan-600 hover:bg-cyan-50 transition-colors">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New Post
            </a>
        </div>

        @auth
            <div class="pt-4 pb-1 border-t border-gray-200">
                <div class="px-4 pb-3">
                    <div class="flex items-center space-x-3">
                        <x-user-avatar :user="Auth::user()" size="w-10 h-10" />
                        <div>
                            <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                            <div class="font-medium text-sm text-gray-500">@{{ Auth::user()->username }}</div>
                        </div>
                    </div>
                </div>

                <div class="space-y-1">
                    <x-responsive-nav-link :href="route('profile.show', Auth::user()->username)">
                        {{ __('View Profile') }}
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('myPosts')">
                        {{ __('My Games') }}
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('games.favorites')">
                        {{ __('Favorite Games') }}
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('profile.edit')">
                        {{ __('Settings') }}
                    </x-responsive-nav-link>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-red-600 hover:text-red-700 hover:bg-red-50 hover:border-red-300 focus:outline-none focus:text-red-700 focus:bg-red-50 focus:border-red-300 transition duration-150 ease-in-out">
                            {{ __('Log Out') }}
                        </button>
                    </form>
                </div>
            </div>
        @endauth

        @guest
            <div class="pt-2 pb-3 space-y-1">
                <a href="{{ route('login') }}" class="block px-4 py-3 text-base font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                    Sign In
                </a>
                <a href="{{ route('register') }}" class="block px-4 py-3 text-base font-medium text-cyan-600 hover:bg-cyan-50 transition-colors">
                    Get Started
                </a>
            </div>
        @endguest
    </div>
</nav>

<script>
    document.getElementById('userSearch')?.addEventListener('input', async (e) => {
        const query = e.target.value.trim();
        const resultsContainer = document.getElementById('searchResults');
        
        if (query.length < 1) {
            resultsContainer.innerHTML = '';
            return;
        }
        
        try {
            const response = await fetch(`/api/users/search?q=${encodeURIComponent(query)}`);
            const users = await response.json();
            
            if (users.length === 0) {
                resultsContainer.innerHTML = '<div class="px-4 py-6 text-center text-gray-500">No users found</div>';
            } else {
                resultsContainer.innerHTML = users.map(user => `
                    <a href="/@${encodeURIComponent(user.username)}" class="block px-4 py-3 hover:bg-gray-50 border-b border-gray-100 last:border-b-0 transition-colors">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-cyan-400 to-blue-500 rounded-full flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                                ${user.name.substring(0, 2).toUpperCase()}
                            </div>
                            <div>
                                <div class="font-semibold text-gray-900 text-sm">${user.name}</div>
                                <div class="text-xs text-gray-500">@${user.username}</div>
                            </div>
                        </div>
                    </a>
                `).join('');
            }
        } catch (error) {
            console.error('Search error:', error);
            resultsContainer.innerHTML = '<div class="px-4 py-3 text-center text-red-500 text-sm">Search error</div>';
        }
    });
</script>
