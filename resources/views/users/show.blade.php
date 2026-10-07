<x-app-layout>
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('users.index') }}" class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">User Details</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">View user information</p>
        </div>
    </div>

    <div class="max-w-2xl">
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-slate-700">
            <div class="flex items-center gap-6 mb-8 pb-8 border-b border-gray-100 dark:border-slate-700">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=6366f1&color=fff&size=128" alt="" class="w-20 h-20 rounded-2xl">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $user->name }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                    @if($user->isAdmin())
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 mt-2">Admin</span>
                    @elseif($user->isManager())
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400 mt-2">Manager</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 mt-2">Agent</span>
                    @endif
                </div>
            </div>

            <div class="space-y-4">
                <div class="flex justify-between py-3 border-b border-gray-100 dark:border-slate-700">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Name</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->name }}</span>
                </div>
                <div class="flex justify-between py-3 border-b border-gray-100 dark:border-slate-700">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Email</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->email }}</span>
                </div>
                <div class="flex justify-between py-3 border-b border-gray-100 dark:border-slate-700">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Role</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->getRoleLabelAttribute() }}</span>
                </div>
                <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-slate-700">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Development Team</span>
                    <span>
                        @if($user->is_development_team)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-violet-100 dark:bg-violet-900/40 text-violet-700 dark:text-violet-300 border border-violet-200 dark:border-violet-700/50">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Yes (Project Dashboard Access)
                            </span>
                        @else
                            <span class="text-sm text-gray-500 dark:text-gray-400 font-medium">No</span>
                        @endif
                    </span>
                </div>
                <div class="flex justify-between py-3 border-b border-gray-100 dark:border-slate-700">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Created</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->created_at->format('M d, Y') }}</span>
                </div>
            </div>

            <div class="flex items-center gap-4 mt-8">
                <a href="{{ route('users.edit', $user) }}" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition-colors">
                    Edit User
                </a>
                <a href="{{ route('users.index') }}" class="px-5 py-2.5 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-gray-300 rounded-xl text-sm font-medium hover:bg-gray-200 dark:hover:bg-slate-600 transition-colors">
                    Back to List
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
