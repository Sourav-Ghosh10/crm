<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <img src="{{ asset('assets/img/logo.png') }}" alt="{{ config('app.name', 'CRM') }}" class="block h-12 w-auto">
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    <x-nav-link :href="route('clients.index')" :active="request()->routeIs('clients.*')">
                        {{ __('Clients') }}
                    </x-nav-link>
                    @auth
                        @if(Auth::user()->isManagement())
                            <x-nav-link :href="route('call-logs.index')" :active="request()->routeIs('call-logs.index')">
                                {{ __('Call Logs') }}
                            </x-nav-link>
                        @endif
                    @endauth
                </div>
            </div>

            <!-- Right Side (Notifications & Settings) -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                
                <!-- Notification Dropdown -->
                <div class="relative mr-3" x-data="notificationDropdown()" x-init="init()">
                    <x-dropdown align="right" width="80">
                        <x-slot name="trigger">
                            <button class="relative inline-flex items-center p-2 border border-transparent text-sm leading-4 font-medium rounded-full text-gray-500 bg-white hover:text-gray-700 hover:bg-gray-100 focus:outline-none transition ease-in-out duration-150">
                                <!-- Bell Icon -->
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                <!-- Unread Badge -->
                                <span x-show="unreadCount > 0" x-text="unreadCount" class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/4 -translate-y-1/4 bg-red-600 rounded-full" style="display: none;"></span>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="w-80">
                                <div class="px-4 py-2 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
                                    <h3 class="text-sm font-semibold text-gray-700">Notifications</h3>
                                    <button @click="markAllAsRead" x-show="unreadCount > 0" class="text-xs text-blue-600 hover:text-blue-800 focus:outline-none">Mark all read</button>
                                </div>
                                <div class="max-h-64 overflow-y-auto">
                                    <template x-if="notifications.length === 0">
                                        <div class="px-4 py-3 text-sm text-gray-500 text-center">
                                            No notifications
                                        </div>
                                    </template>
                                    <template x-for="notification in notifications" :key="notification.id">
                                        <a :href="`/notifications/${notification.id}/read`" class="block px-4 py-3 border-b border-gray-100 hover:bg-gray-50 transition duration-150 ease-in-out" :class="{'bg-blue-50/30': !notification.read_at}">
                                            <div class="flex items-start">
                                                <div class="flex-shrink-0 pt-0.5">
                                                    <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-blue-100">
                                                        <span class="text-xl">👤</span>
                                                    </span>
                                                </div>
                                                <div class="ml-3 w-0 flex-1">
                                                    <p class="text-sm font-medium text-gray-900" x-text="notification.data.title"></p>
                                                    <p class="text-xs text-gray-500 mt-1" x-text="notification.data.message"></p>
                                                    <p class="text-xs text-gray-400 mt-1" x-text="`Assigned by ${notification.data.assigned_by_name}`"></p>
                                                    <p class="text-xs text-gray-400 mt-1" x-text="formatTime(notification.created_at)"></p>
                                                </div>
                                                <div class="ml-2 flex-shrink-0 flex" x-show="!notification.read_at">
                                                    <span class="inline-block h-2 w-2 rounded-full bg-blue-600"></span>
                                                </div>
                                            </div>
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </x-slot>
                    </x-dropdown>
                </div>
                
                <script>
                    function notificationDropdown() {
                        return {
                            unreadCount: 0,
                            notifications: [],
                            pusherBound: false,
                            init() {
                                this.fetchNotifications();
                                this.listenForRealTimeNotifications();
                                this.setupAudioUnlock();
                            },
                            setupAudioUnlock() {
                                const unlockAudio = () => {
                                    const audio = document.getElementById('crm-notification-sound');
                                    if (audio) {
                                        const originalVolume = audio.volume;
                                        audio.volume = 0; // Mute for unlock
                                        const p = audio.play();
                                        if (p !== undefined) {
                                            p.then(() => {
                                                audio.pause();
                                                audio.currentTime = 0;
                                                audio.volume = originalVolume; // Restore
                                            }).catch(() => {}); // Ignore unlock errors
                                        }
                                    }
                                    // Remove listeners after first interaction
                                    document.removeEventListener('click', unlockAudio);
                                    document.removeEventListener('keydown', unlockAudio);
                                };
                                document.addEventListener('click', unlockAudio);
                                document.addEventListener('keydown', unlockAudio);
                            },
                            fetchNotifications() {
                                fetch('/notifications/unread')
                                    .then(response => response.json())
                                    .then(data => {
                                        this.unreadCount = data.unread_count;
                                        this.notifications = data.notifications;
                                    });
                            },
                            listenForRealTimeNotifications() {
                                const userId = {{ auth()->check() ? auth()->id() : 'null' }};
                                
                                const setupPusher = () => {
                                    if (window.Echo && userId && !this.pusherBound) {
                                        this.pusherBound = true;
                                        const channel = window.Echo.private(`App.Models.User.${userId}`);
                                        
                                        // Unbind previous listeners to prevent duplicate triggers on Livewire/Blade re-renders
                                        channel.stopListening('.Illuminate\\Notifications\\Events\\BroadcastNotificationCreated');
                                        
                                        channel.notification((notification) => {
                                                console.log("[Pusher Checkpoint] Notification event fired!", notification);
                                                const newNotif = {
                                                    id: notification.id,
                                                    data: notification.data || notification,
                                                    created_at: new Date().toISOString(),
                                                    read_at: null
                                                };

                                                // Prevent duplicate UI updates if event received multiple times
                                                if (this.notifications.find(n => n.id === newNotif.id)) {
                                                    return;
                                                }

                                                this.unreadCount++;
                                            // Ensure title exists
                                            if (!newNotif.data.title && newNotif.data.project_name) {
                                                 newNotif.data.title = 'You have been assigned to a project';
                                                 newNotif.data.message = 'You have been assigned to the project: ' + newNotif.data.project_name;
                                            }
                                            
                                            this.notifications.unshift(newNotif);
                                            if (this.notifications.length > 5) {
                                                this.notifications.pop();
                                            }
                                            
                                            // Play sound for the incoming notification
                                            this.playSound();

                                            // UI unread badge and list are updated above.
                                            // FCM handles the native desktop notification in firebase-init.blade.php
                                            
                                        });
                                    }
                                };
                                
                                // Because this Alpine component might initialize before Echo is ready,
                                // we must wait until Echo is loaded to subscribe.
                                const checkEcho = () => {
                                    if (window.Echo) {
                                        setupPusher();
                                    } else {
                                        // Fallback for async load
                                        let attempts = 0;
                                        const interval = setInterval(() => {
                                            if (window.Echo) {
                                                setupPusher();
                                                clearInterval(interval);
                                            }
                                            if (attempts++ > 40) clearInterval(interval); // Try for up to 20 seconds
                                        }, 500);
                                    }
                                };
                                checkEcho();
                            },
                            markAllAsRead() {
                                fetch('/notifications/mark-all-read', {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                        'Accept': 'application/json'
                                    }
                                }).then(() => {
                                    this.unreadCount = 0;
                                    this.notifications.forEach(n => n.read_at = new Date().toISOString());
                                });
                            },
                            playSound() {
                                console.log('[Pusher Checkpoint] playSound() reached.');
                                const audio = document.getElementById('crm-notification-sound');
                                
                                if (!audio) {
                                    console.error('[Pusher Error] <audio id="crm-notification-sound"> element NOT FOUND in the DOM!');
                                    return;
                                }

                                console.log('[Pusher Checkpoint] Audio element found:', audio.src, 'preload:', audio.preload);

                                // Reset time to allow repeated fast plays
                                audio.currentTime = 0;
                                
                                const playPromise = audio.play();
                                if (playPromise !== undefined) {
                                    playPromise.then(() => {
                                        console.log('[Pusher Success] Audio played successfully.');
                                    }).catch(error => {
                                        console.error('[Pusher Error] Audio playback blocked or failed:', error);
                                    });
                                }
                            },
                            formatTime(dateString) {
                                const date = new Date(dateString);
                                return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                            }
                        }
                    }
                </script>

                <!-- Embedded Audio Element for reliable preloading -->
                <audio id="crm-notification-sound" src="{{ asset('/notification.wav') }}" preload="auto" style="display:none;"></audio>

                <!-- Settings Dropdown -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('clients.index')" :active="request()->routeIs('clients.*')">
                {{ __('Clients') }}
            </x-responsive-nav-link>
            @auth
                @if(Auth::user()->isManagement())
                    <x-responsive-nav-link :href="route('call-logs.index')" :active="request()->routeIs('call-logs.index')">
                        {{ __('Call Logs') }}
                    </x-responsive-nav-link>
                @endif
            @endauth
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
