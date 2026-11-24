<x-app-layout>
    <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-4">
            @if(auth()->user()->isManagement())
                <a href="{{ route('call-logs.index') }}"
                    class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
            @else
                <a href="{{ route('clients.call-logs.index', $callLog->client) }}"
                    class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
            @endif
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Call Record Details</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $callLog->call_record_number }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            @if(auth()->user()->isManagement())
                <a href="{{ route('call-logs.edit', $callLog) }}"
                    class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-medium transition-colors flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                        </path>
                    </svg>
                    Edit Record
                </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Call Info Card -->
            <div
                class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-slate-700">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Call Information</h2>
                    <div class="flex items-center gap-3">
                        <span
                            class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium {{ $callLog->direction_color }}">
                            {{ $callLog->call_direction }}
                        </span>
                        <span
                            class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium {{ $callLog->result_color }}">
                            {{ $callLog->call_result }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Phone Number</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $callLog->phone_number }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Dialer Platform</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $callLog->dialer_platform }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Call Start Time</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $callLog->call_start_time->format('M d, Y h:i A') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Call End Time</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $callLog->call_end_time->format('h:i A') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Duration</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $callLog->formatted_duration }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Notes Card -->
            @if($callLog->notes)
                <div
                    class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-slate-700">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Notes</h2>
                    <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $callLog->notes }}</p>
                </div>
            @endif

            <!-- Follow-up Card -->
            @if($callLog->next_follow_up_date)
                <div
                    class="bg-indigo-50 dark:bg-indigo-900/20 rounded-2xl p-6 border border-indigo-100 dark:border-indigo-800">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-indigo-600 dark:text-indigo-400">Next Follow-up Date</p>
                            <p class="text-xl font-bold text-indigo-900 dark:text-indigo-300">
                                {{ $callLog->next_follow_up_date->format('M d, Y') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Admin Edit Reason -->
            @if($callLog->admin_edit_reason)
                <div class="bg-amber-50 dark:bg-amber-900/20 rounded-2xl p-6 border border-amber-200 dark:border-amber-800">
                    <div class="flex items-start gap-3">
                        <div
                            class="w-10 h-10 bg-amber-100 dark:bg-amber-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">Edit Reason (Admin/Manager)
                            </p>
                            <p class="text-amber-700 dark:text-amber-400 mt-1">{{ $callLog->admin_edit_reason }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Client Info -->
            <div
                class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-slate-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Client Information</h3>
                @if($callLog->client)
                    <a href="{{ route('clients.show', $callLog->client) }}"
                        class="flex items-center gap-4 mb-4 hover:bg-gray-50 dark:hover:bg-slate-700/30 -m-2 p-2 rounded-xl transition-colors">
                        <div
                            class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-lg font-bold">
                            {{ strtoupper(substr($callLog->customer_name, 0, 2)) }}
                        </div>
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $callLog->customer_name }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $callLog->client->customer_number }}
                            </p>
                        </div>
                    </a>
                @endif
            </div>

            <!-- Staff Info -->
            <div
                class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-slate-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Staff Member</h3>
                <div class="flex items-center gap-4">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($callLog->staffMember->name) }}&background=6366f1&color=fff"
                        alt="" class="w-12 h-12 rounded-xl">
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $callLog->staffMember->name }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $callLog->staffMember->getRoleLabelAttribute() }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Record Info -->
            <div
                class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-slate-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Record Info</h3>
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Created By</span>
                        <span
                            class="text-sm font-medium text-gray-900 dark:text-white">{{ $callLog->creator->name ?? 'System' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Created Date</span>
                        <span
                            class="text-sm font-medium text-gray-900 dark:text-white">{{ $callLog->created_at->format('M d, Y h:i A') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Last Updated By</span>
                        <span
                            class="text-sm font-medium text-gray-900 dark:text-white">{{ $callLog->updater->name ?? 'System' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Last Updated</span>
                        <span
                            class="text-sm font-medium text-gray-900 dark:text-white">{{ $callLog->updated_at->format('M d, Y h:i A') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>