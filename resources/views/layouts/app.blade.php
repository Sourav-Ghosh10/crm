<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'CRM') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        // Initialize dark mode on page load
        if (localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>

<body class="font-sans antialiased text-gray-900 dark:text-white bg-gray-50 dark:bg-slate-900">
    <!-- Sidebar - Always visible on screen sizes > 640px -->
    <aside class="crm-sidebar w-64 bg-white dark:bg-slate-800 border-r border-gray-200 dark:border-slate-700 fixed left-0 top-0 h-screen z-70 hidden sm:flex flex-col">
        <div class="flex flex-col h-full">
            <div class="p-6 border-b border-gray-100 dark:border-slate-700">
                <div class="flex items-center gap-3">
                    <!-- Light Mode Logo -->
                    <img src="{{ asset('assets/img/logo.png') }}" alt="{{ config('app.name', 'CRM') }}" class="w-50 h-30 rounded-lg object-contain bg-white dark:hidden">
                    <!-- Dark Mode Logo -->
                    <img src="{{ asset('assets/img/logo_white.png') }}" alt="{{ config('app.name', 'CRM') }}" class="w-50 h-30 rounded-lg object-contain hidden dark:block">
                    <!-- <div class="hidden dark:block">
                        <span class="text-lg font-bold text-gray-900 dark:text-white block leading-none">
                            {{ config('app.name', 'CRM') }}
                        </span>
                        <span class="text-[10px] font-medium text-indigo-600 dark:text-indigo-400 uppercase tracking-wider mt-1 block">Management</span>
                    </div> -->
                </div>
            </div>

            <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
                <div class="text-[10px] font-semibold text-gray-400 dark:text-slate-500 uppercase tracking-wider mb-3 px-3">Main</div>
                
                <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('dashboard') ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400' : 'text-gray-600 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-slate-700/50 hover:text-gray-900 dark:hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                    </svg>
                    <span class="font-medium">Dashboard</span>
                </x-nav-link>

                <x-nav-link :href="route('clients.index')" :active="request()->routeIs('clients.*')"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('clients.*') ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400' : 'text-gray-600 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-slate-700/50 hover:text-gray-900 dark:hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span class="font-medium">Clients</span>
                </x-nav-link>

                @auth
                    @if(Auth::user()->canAccessProjects())
                    <div x-data="{ open: {{ request()->routeIs('projects.*') ? 'true' : 'false' }} }">
                        <button @click="open = !open" 
                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('projects.*') ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400' : 'text-gray-600 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-slate-700/50 hover:text-gray-900 dark:hover:text-white' }}">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"></path>
                                </svg>
                                <span class="font-medium">Projects</span>
                            </div>
                            <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" class="mt-1 ml-4 space-y-1">
                            <x-nav-link :href="route('projects.index')" :active="request()->routeIs('projects.index')"
                                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('projects.index') ? 'text-indigo-600 font-semibold' : 'text-gray-500 hover:text-gray-900 dark:text-slate-400 dark:hover:text-white' }}">
                                <span>• List Projects</span>
                            </x-nav-link>
                            <x-nav-link :href="route('projects.settings')" :active="request()->routeIs('projects.settings')"
                                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('projects.settings') ? 'text-indigo-600 font-semibold' : 'text-gray-500 hover:text-gray-900 dark:text-slate-400 dark:hover:text-white' }}">
                                <span>• Settings</span>
                            </x-nav-link>
                            <x-nav-link :href="route('projects.invoices')" :active="request()->routeIs('projects.invoices')"
                                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('projects.invoices') ? 'text-indigo-600 font-semibold' : 'text-gray-500 hover:text-gray-900 dark:text-slate-400 dark:hover:text-white' }}">
                                <span>• Invoices</span>
                            </x-nav-link>
                        </div>
                    </div>
                    @endif
                @endauth

                @auth
                    <div class="text-[10px] font-semibold text-gray-400 dark:text-slate-500 uppercase tracking-wider mb-3 px-3 mt-6">Management</div>

                    <x-nav-link :href="route('call-logs.index')" :active="request()->routeIs('call-logs.*')"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('call-logs.*') ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400' : 'text-gray-600 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-slate-700/50 hover:text-gray-900 dark:hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        <span class="font-medium">Call Logs</span>
                    </x-nav-link>

                    <x-nav-link :href="route('tasks.index')" :active="request()->routeIs('tasks.*')"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('tasks.*') ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400' : 'text-gray-600 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-slate-700/50 hover:text-gray-900 dark:hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                        <span class="font-medium">Tasks & Meetings</span>
                    </x-nav-link>

                    @if(Auth::user()->canManageUsers())
                    <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('users.*') ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400' : 'text-gray-600 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-slate-700/50 hover:text-gray-900 dark:hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <span class="font-medium">Users</span>
                    </x-nav-link>
                    @endif
                @endauth
            </nav>

            <div class="p-4 border-t border-gray-100 dark:border-slate-700 mt-auto">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-600 dark:text-slate-400 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400 transition-all duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        <span class="font-medium">Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Wrapper -->
    <div x-data="{ mobileMenuOpen: false }" class="min-h-screen">
        <!-- Mobile Sidebar Overlay -->
        <div id="mobile-sidebar" 
             class="fixed inset-0 z-50 sm:hidden" 
             x-show="mobileMenuOpen" 
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="transition ease-in duration-300" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0" 
             style="display: none;">
            
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="mobileMenuOpen = false"></div>
            
            <!-- Sidebar Panel -->
            <div class="fixed inset-y-0 left-0 w-72 bg-white dark:bg-slate-800 shadow-2xl flex flex-col transition-transform duration-300"
                 x-transition:enter="translate-x-0"
                 x-transition:enter-start="-translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="translate-x-0"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="-translate-x-full">
                
                <div class="p-6 border-b border-gray-100 dark:border-slate-700 flex justify-between items-center bg-white dark:bg-slate-800">
                    <div class="flex items-center gap-3">
                        <!-- Light Mode Logo (Mobile) -->
                        <img src="{{ asset('assets/img/logo.png') }}" alt="{{ config('app.name', 'CRM') }}" class="w-10 h-10 rounded-lg object-contain bg-white dark:hidden">
                        <!-- Dark Mode Logo (Mobile) -->
                        <img src="{{ asset('assets/img/logo_white.png') }}" alt="{{ config('app.name', 'CRM') }}" class="w-10 h-10 rounded-lg object-contain hidden dark:block">
                        <span class="text-lg font-bold text-gray-900 dark:text-white">{{ config('app.name', 'CRM') }}</span>
                    </div>
                    <button @click="mobileMenuOpen = false" class="p-2 text-gray-500 hover:text-gray-700 dark:text-slate-400 dark:hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <nav class="flex-1 p-4 space-y-1 overflow-y-auto bg-white dark:bg-slate-800">
                    <div class="text-[10px] font-semibold text-gray-400 dark:text-slate-500 uppercase tracking-wider mb-3 px-3">Main</div>
                    
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('dashboard') ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600' : 'text-gray-600 dark:text-slate-400' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                        </svg>
                        <span class="font-medium">Dashboard</span>
                    </x-nav-link>

                    <x-nav-link :href="route('clients.index')" :active="request()->routeIs('clients.*')"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('clients.*') ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600' : 'text-gray-600 dark:text-slate-400' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span class="font-medium">Clients</span>
                    </x-nav-link>

                    @auth
                        @if(Auth::user()->canAccessProjects())
                        <div x-data="{ open: {{ request()->routeIs('projects.*') ? 'true' : 'false' }} }">
                            <button @click="open = !open" 
                                class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl {{ request()->routeIs('projects.*') ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600' : 'text-gray-600 dark:text-slate-400' }}">
                                <div class="flex items-center gap-3">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"></path>
                                    </svg>
                                    <span class="font-medium">Projects</span>
                                </div>
                                <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <div x-show="open" class="mt-1 ml-4 space-y-1">
                                <x-nav-link :href="route('projects.index')" :active="request()->routeIs('projects.index')"
                                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('projects.index') ? 'text-indigo-600 font-semibold' : 'text-gray-500 dark:text-slate-400' }}">
                                    <span>• List Projects</span>
                                </x-nav-link>
                                <x-nav-link :href="route('projects.settings')" :active="request()->routeIs('projects.settings')"
                                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('projects.settings') ? 'text-indigo-600 font-semibold' : 'text-gray-500 dark:text-slate-400' }}">
                                    <span>• Settings</span>
                                </x-nav-link>
                                <x-nav-link :href="route('projects.invoices')" :active="request()->routeIs('projects.invoices')"
                                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('projects.invoices') ? 'text-indigo-600 font-semibold' : 'text-gray-500 dark:text-slate-400' }}">
                                    <span>• Invoices</span>
                                </x-nav-link>
                            </div>
                        </div>
                        @endif
                    @endauth

                    @auth
                        <div class="text-[10px] font-semibold text-gray-400 dark:text-slate-500 uppercase tracking-wider mb-3 px-3 mt-6">Management</div>

                        <x-nav-link :href="route('call-logs.index')" :active="request()->routeIs('call-logs.*')"
                            class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('call-logs.*') ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600' : 'text-gray-600 dark:text-slate-400' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <span class="font-medium">Call Logs</span>
                        </x-nav-link>

                        <x-nav-link :href="route('tasks.index')" :active="request()->routeIs('tasks.*')"
                            class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('tasks.*') ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600' : 'text-gray-600 dark:text-slate-400' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                            <span class="font-medium">Tasks & Meetings</span>
                        </x-nav-link>

                        @if(Auth::user()->canManageUsers())
                        <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')"
                            class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('users.*') ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600' : 'text-gray-600 dark:text-slate-400' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            <span class="font-medium">Users</span>
                        </x-nav-link>
                        @endif
                    @endauth
                </nav>

                <div class="p-4 border-t border-gray-100 dark:border-slate-700 bg-white dark:bg-slate-800">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/10">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            <span class="font-medium">Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="sm:ml-64 min-h-screen flex flex-col relative">
            <!-- Mobile Header with Hamburger -->
            <header class="bg-white dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700 fixed top-0 left-0 right-0 z-50 sm:hidden h-16">
                <div class="flex items-center px-4 py-3">
                    <button @click="mobileMenuOpen = true" class="p-2 text-gray-500 dark:text-slate-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <span class="ml-3 text-lg font-bold text-gray-900 dark:text-white">{{ config('app.name', 'CRM') }}</span>
                </div>
            </header>

            <!-- Desktop Header - Hidden on mobile -->
            <header class="bg-white dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700 fixed top-0 left-0 w-full z-40 sm:block h-16">
            <div class="flex items-center justify-between px-4 sm:px-6 lg:px-8 sm:pl-72 py-3">
                <div></div>
                
                <div class="flex items-center gap-2 sm:gap-4">
                    <div class="relative hidden sm:block">
                        <input type="text" placeholder="Search..." class="w-48 lg:w-64 pl-10 pr-4 py-2 bg-gray-50 dark:bg-slate-700 border-0 rounded-xl text-sm text-gray-700 dark:text-gray-300 placeholder-gray-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-indigo-500">
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>

                    <button class="relative p-2 text-gray-500 dark:text-slate-400 hover:text-gray-700 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                    </button>

                    <button id="theme-toggle" class="p-2 text-gray-500 dark:text-slate-400 hover:text-gray-700 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg">
                        <svg id="sun-icon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <svg id="moon-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                        </svg>
                    </button>

                    @auth
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-2 focus:outline-none">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=6366f1&color=fff&size=40" alt="" class="w-8 h-8 sm:w-10 sm:h-10 rounded-full">
                        </button>
                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-gray-100 dark:border-slate-700 py-1 z-50" style="display: none;">
                            <div class="px-4 py-2 border-b border-gray-100 dark:border-slate-700">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ Auth::user()->role }}</p>
                            </div>
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700">
                                Profile
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                    @endauth
                </div>
            </div>
        </header>

        <div class="flex-1 p-4 sm:p-6 lg:p-15 pt-20 sm:pt-20">
            <div class="mx-auto">
                @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
                     class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-2xl flex items-center justify-between transition-all">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-emerald-100 dark:bg-emerald-900/30 rounded-full flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-emerald-800 dark:text-emerald-300">{{ session('success') }}</p>
                    </div>
                    <button @click="show = false" class="text-emerald-400 hover:text-emerald-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                @endif
                {{ $slot }}
            </div>
        </div>
    </div>

    <script>
        const themeToggleBtn = document.getElementById('theme-toggle');
        const sunIcon = document.getElementById('sun-icon');
        const moonIcon = document.getElementById('moon-icon');

        function updateThemeIcons() {
            if (document.documentElement.classList.contains('dark')) {
                sunIcon.classList.remove('hidden');
                moonIcon.classList.add('hidden');
            } else {
                sunIcon.classList.add('hidden');
                moonIcon.classList.remove('hidden');
            }
        }

        updateThemeIcons();

        themeToggleBtn.addEventListener('click', function() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
            updateThemeIcons();
        });
    </script>
</body>

</html>
