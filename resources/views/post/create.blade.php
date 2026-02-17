<x-app-layout>
    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Header -->
            <div class="mb-6">
                <h2 class="text-3xl font-bold text-gray-900">Create New Post</h2>
                <p class="mt-2 text-sm text-gray-600">Share your thoughts with the community</p>
            </div>

            <div class="bg-white overflow-hidden shadow-lg sm:rounded-xl border border-gray-200">  
                <form action="{{ route('post.store') }}" method="POST" enctype="multipart/form-data" class="p-8 space-y-6">

                @csrf

                    <!-- Image Upload Section -->
                    <div>
                        <x-input-label for="image" :value="__('Image')" class="text-base font-semibold" />
                            <p class="text-sm text-gray-500 mb-3">Upload an image for your post</p>
                            
                            <div class="mt-2 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-cyan-400 transition-colors duration-200" id="imageDropZone">
                                <div class="space-y-2 text-center" id="uploadPlaceholder">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-gray-600">
                                        <label for="image" class="relative cursor-pointer bg-white rounded-md font-medium text-cyan-600 hover:text-cyan-500 focus-within:outline-none">
                                            <span class="p-11"> Upload a file </span>
                                            <x-text-input id="image" type="file" name="image" class="sr-only" accept="image/*" />
                                        </label>
                                    </div>
                                </div>
                                <!-- Preview Section -->
                                <div class="space-y-3 text-center hidden" id="uploadPreview">
                                    <img id="previewImage" src="" alt="Preview" class="w-48 h-48 object-cover rounded-lg mx-auto" />
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900" id="fileName"></p>
                                        <button type="button" class="text-sm text-cyan-600 hover:text-cyan-700 font-medium mt-2" onclick="document.getElementById('image').value = ''; document.getElementById('uploadPreview').classList.add('hidden'); document.getElementById('uploadPlaceholder').classList.remove('hidden');">
                                            Change image
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <x-input-error :messages="$errors->get('image')" class="mt-2" />
                    </div>  

 

                    <!-- Title -->
                    <div>
                        <x-input-label for="title" :value="__('Post Title')" class="text-base font-semibold" />
                            <p class="text-sm text-gray-500 mb-3">Give your post an engaging title</p>
                            <x-text-input 
                                id="title" 
                                class="block mt-1 w-full text-lg py-3 px-4 border-gray-300 focus:border-cyan-500 focus:ring-cyan-500 rounded-lg shadow-sm" 
                                type="text" 
                                name="title" 
                                :value="old('title')" 
                                required 
                                autofocus 
                                placeholder="Enter your post title..." 
                            />
                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                    </div>

                    <!-- Content -->
                    <div>
                        <x-input-label for="content" :value="__('Post Content')" class="text-base font-semibold" />
                            <p class="text-sm text-gray-500 mb-3">Write the main content of your post</p>
                            <x-input-textarea 
                                id="content" 
                                class="block mt-1 w-full min-h-[200px] py-3 px-4 border-gray-300 focus:border-cyan-500 focus:ring-cyan-500 rounded-lg shadow-sm" 
                                name="content"
                                placeholder="Share your story, ideas, or thoughts..."
                            >{{ old('content') }}</x-input-textarea>
                        <x-input-error :messages="$errors->get('content')" class="mt-2" />
                    </div>

                    <!-- Category -->
                    <div class="mt-4">
                        <x-input-label for="category_id" :value="__('Category')" />
                        <select id="category_id" name="category_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                            <option value="">Select a Category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                        @selected(old('category_id') == $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200">
                        <a href="/test" class="px-6 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500 transition-colors duration-200">
                            Cancel
                        </a>

                        <button 
                            type="submit" 
                            class="px-8 py-3 text-sm font-medium text-white bg-cyan-500 border border-transparent rounded-lg hover:bg-cyan-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500 shadow-md hover:shadow-lg transition-all duration-200 transform hover:-translate-y-0.5"
                        >
                            <span class="flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Submit
                            </span>
                        </button>
                    </div>


                </form>
            </div>

        </div>
    </div>

    <script>
        const imageInput = document.getElementById('image');
        const uploadPlaceholder = document.getElementById('uploadPlaceholder');
        const uploadPreview = document.getElementById('uploadPreview');
        const previewImage = document.getElementById('previewImage');
        const fileName = document.getElementById('fileName');

        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            
            if (file) {
                // Show filename
                fileName.textContent = file.name;
                
                // Create preview
                const reader = new FileReader();
                reader.onload = function(event) {
                    previewImage.src = event.target.result;
                    uploadPlaceholder.classList.add('hidden');
                    uploadPreview.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</x-app-layout>