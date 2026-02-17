<div class="bg-gradient-to-br from-gray-900 to-gray-800 overflow-hidden shadow-xl sm:rounded-xl">
    <div class="p-6">
        <ul class="flex flex-wrap justify-center gap-3 text-sm font-semibold">
            <li>
                <a href="{{ route('dashboard') }}" 
                   class="inline-block px-6 py-2.5 {{ !request()->is('category/*') ? 'text-gray-900 bg-cyan-400 shadow-lg hover:bg-cyan-300 hover:shadow-cyan-400/50' : 'text-gray-300 bg-gray-800 border border-gray-700 hover:bg-gray-700 hover:text-cyan-400 hover:border-cyan-400/50 hover:shadow-lg hover:shadow-cyan-400/20' }} rounded-lg transition-all duration-300 hover:scale-105">
                    All
                </a>
            </li>

            @foreach($categories as $category)
                <li>
                    <a href="{{ route('post.byCategory', $category) }}" 
                       class="inline-block px-6 py-2.5 {{ request()->is('category/' . $category->id) ? 'text-gray-900 bg-cyan-400 shadow-lg hover:bg-cyan-300 hover:shadow-cyan-400/50' : 'text-gray-300 bg-gray-800 border border-gray-700 hover:bg-gray-700 hover:text-cyan-400 hover:border-cyan-400/50 hover:shadow-lg hover:shadow-cyan-400/20' }} rounded-lg transition-all duration-300 hover:scale-105"> 
                        {{ $category->name }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</div>