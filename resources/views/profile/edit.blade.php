<x-app-layout>
    <div class="py-12 bg-gray-100 dark:bg-gray-900">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="bg-gradient-to-r from-gray-900 via-gray-800 to-gray-900 dark:from-gray-950 dark:via-gray-900 dark:to-gray-950 rounded-lg shadow-xl p-8 mb-8">
                <h1 class="text-4xl md:text-5xl font-bold text-white mb-2">Settings</h1>
                <p class="text-gray-300 text-lg">Manage your account and preferences</p>
            </div>

            <div class="space-y-6">
                <!-- Profile Information Card -->
                <div class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
                    <div class="px-4 sm:p-8 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-600 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                                    <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ __('Profile Information') }}
                                </h2>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    {{ __("Update your account's profile information and email address.") }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 sm:p-8">
                        <div class="max-w-2xl">
                            @include('profile.partials.update-profile-information-form')
                        </div>
                    </div>
                </div>

                <!-- Update Password Card -->
                <div class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
                    <div class="px-4 sm:p-8 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-gray-700 dark:to-gray-600 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                                    <svg class="w-6 h-6 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                    {{ __('Update Password') }}
                                </h2>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    {{ __('Use a long, random password to keep your account secure.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 sm:p-8">
                        <div class="max-w-2xl">
                            @include('profile.partials.update-password-form')
                        </div>
                    </div>
                </div>

                <!-- Delete Account Card -->
                <div class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg overflow-hidden hover:shadow-lg transition-shadow border-l-4 border-red-500">
                    <div class="px-4 sm:p-8 bg-gradient-to-r from-red-50 to-pink-50 dark:from-gray-700 dark:to-gray-600 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                                    <svg class="w-6 h-6 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    {{ __('Delete Account') }}
                                </h2>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 sm:p-8">
                        <div class="max-w-2xl">
                            @include('profile.partials.delete-user-form')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
