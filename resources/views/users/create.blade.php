<x-app-layout>
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('users.index') }}" class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Add User</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Create a new team member</p>
        </div>
    </div>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('users.store') }}" class="space-y-6">
            @csrf

            <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-slate-700">
                <!-- Name -->
                <div class="mb-6">
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Full Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-700 border-0 rounded-xl text-sm text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-indigo-500">
                    @error('name')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div class="mb-6">
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email Address</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-700 border-0 rounded-xl text-sm text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-indigo-500">
                    @error('email')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Role -->
                <div class="mb-6">
                    <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Role</label>
                    <select name="role" id="role" required
                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-700 border-0 rounded-xl text-sm text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-indigo-500">
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>{{ $role->display_name }}</option>
                        @endforeach
                    </select>
                    @error('role')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Development Team Option -->
                <div class="mb-6">
                    <label class="flex items-start gap-3 p-4 bg-gray-50/80 dark:bg-slate-700/50 rounded-xl border border-gray-200/80 dark:border-slate-600 cursor-pointer hover:bg-gray-100/80 dark:hover:bg-slate-700 transition group">
                        <div class="flex items-center h-5 mt-0.5">
                            <input type="checkbox" name="is_development_team" id="is_development_team" value="1" {{ old('is_development_team') ? 'checked' : '' }}
                                class="w-5 h-5 text-indigo-600 bg-white dark:bg-slate-800 border-gray-300 dark:border-slate-600 rounded focus:ring-indigo-500 focus:ring-2 cursor-pointer transition">
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">Development Team</span>
                                <span class="px-2 py-0.5 text-[11px] font-medium bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 rounded-full">Project Dashboard Access</span>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Check if this user is in the development team. If checked, he/she will be able to see and access the Project Management Dashboard.</p>
                        </div>
                    </label>
                    @error('is_development_team')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Password</label>
                    <input type="password" name="password" id="password" required
                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-700 border-0 rounded-xl text-sm text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-indigo-500">
                    @error('password')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div class="mb-6">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Confirm Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required
                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-700 border-0 rounded-xl text-sm text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="flex items-center gap-4">
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition-colors shadow-lg shadow-indigo-600/25">
                    Create User
                </button>
                <a href="{{ route('users.index') }}" class="px-6 py-2.5 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-gray-300 rounded-xl text-sm font-medium hover:bg-gray-200 dark:hover:bg-slate-600 transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
