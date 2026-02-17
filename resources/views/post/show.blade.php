<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-gray-100 via-white to-blue-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700 px-4 py-3">
                    {{ session('success') }}
                </div>
            @endif

            <article class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-6 sm:p-8 border-b border-gray-200 dark:border-gray-700">
                    <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white leading-tight">{{ $post->title }}</h1>

                    <div class="mt-6 flex items-center justify-between gap-4 flex-wrap">
                        <div class="flex items-center gap-4">
                            <x-user-avatar :user="$post->user" size="w-12 h-12" />

                            <div>
                                <x-follow-ctr :user="$post->user" class="flex items-center gap-2">
                                    <a href="{{ route('profile.show', $post->user) }}" class="font-semibold text-gray-900 dark:text-white hover:text-cyan-600 dark:hover:text-cyan-400">
                                        {{ $post->user->name }}
                                    </a>

                                    @auth
                                        @if (auth()->id() !== $post->user_id)
                                            <span class="text-gray-400">&middot;</span>
                                            <button x-text="following ? 'Unfollow' : 'Follow'"
                                                :class="following ? 'text-red-600' : 'text-emerald-600'" @click="follow()"
                                                class="font-medium">
                                            </button>
                                        @endif
                                    @endauth
                                </x-follow-ctr>

                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ '@' . $post->user->username }} &middot; {{ $post->readTime() }} min read &middot;
                                    {{ $post->created_at->format('M d, Y') }}
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('post.byCategory', $post->category) }}"
                            class="inline-flex items-center px-4 py-2 rounded-full bg-cyan-100 text-cyan-800 dark:bg-cyan-900/60 dark:text-cyan-100 text-sm font-semibold">
                            {{ $post->category->name }}
                        </a>
                    </div>

                    @if ($post->user_id === Auth::id())
                        <div class="mt-6 flex items-center gap-3">
                            <a href="{{ route('post.edit', $post->slug) }}"
                                class="inline-flex items-center px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-lg transition-colors">
                                Edit Post
                            </a>

                            <form action="{{ route('post.destroy', $post) }}" method="POST"
                                onsubmit="return confirm('Delete this post?');">
                                @csrf
                                @method('delete')
                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition-colors">
                                    Delete Post
                                </button>
                            </form>
                        </div>
                    @endif
                </div>

                @if ($post->imageUrl('large'))
                    <div class="bg-gray-100 dark:bg-gray-900">
                        <img src="{{ $post->imageUrl('large') }}" alt="{{ $post->title }}" class="w-full max-h-[520px] object-cover">
                    </div>
                @endif

                <div class="p-6 sm:p-8">
                    <div class="prose prose-lg max-w-none dark:prose-invert !text-white prose-p:!text-white prose-headings:!text-white">
                        {!! nl2br(e($post->content)) !!}
                    </div>

                    <x-clap-button :post="$post" />
                </div>
            </article>

            <section id="comments" class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700">
                <div class="p-6 sm:p-8 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Comments ({{ $post->comments->count() }})
                    </h2>

                    @auth
                        <form action="{{ route('post.comments.store', ['post' => $post->slug]) }}" method="POST" class="mt-5 space-y-3">
                            @csrf
                            <label for="content" class="sr-only">Write a comment</label>
                            <textarea id="content" name="content" rows="4" maxlength="1000"
                                class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 px-4 py-3"
                                placeholder="Write a comment...">{{ old('content') }}</textarea>

                            @error('content')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror

                            <div class="flex justify-end">
                                <button type="submit"
                                    class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-semibold rounded-lg hover:shadow-lg transition-all">
                                    Post Comment
                                </button>
                            </div>
                        </form>
                    @else
                        <p class="mt-4 text-gray-600 dark:text-gray-400">
                            <a href="{{ route('login') }}" class="text-cyan-600 dark:text-cyan-400 font-semibold hover:underline">Sign in</a>
                            to join the discussion.
                        </p>
                    @endauth
                </div>

                <div class="p-6 sm:p-8 space-y-6">
                    @forelse($post->comments as $comment)
                        <article class="flex gap-4">
                            <x-user-avatar :user="$comment->user" size="w-10 h-10" />

                            <div class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40 p-4">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <a href="{{ route('profile.show', $comment->user) }}"
                                        class="font-semibold text-gray-900 dark:text-white hover:text-cyan-600 dark:hover:text-cyan-400">
                                        {{ $comment->user->name }}
                                    </a>
                                    <span class="text-sm text-gray-500">{{ '@' . $comment->user->username }}</span>
                                    <span class="text-sm text-gray-400">&middot;</span>
                                    <time class="text-sm text-gray-500">{{ $comment->created_at->format('M d, Y \a\t h:i A') }}</time>
                                </div>

                                <p class="mt-2 text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $comment->content }}</p>
                            </div>
                        </article>
                    @empty
                        <p class="text-gray-500 dark:text-gray-400">No comments yet. Be the first to comment.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
