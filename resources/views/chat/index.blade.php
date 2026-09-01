<x-app-layout>
    <div class="w-full flex flex-col sm:flex-row bg-white dark:bg-[#17181c] text-slate-900 dark:text-slate-200 overflow-hidden"
        style="height: calc(100vh - 80px) !important;" x-data="chatViewComponent()">

        <!-- Left Sidebar: Channels & DMs Selector -->
        <div class="h-full flex flex-col bg-gray-50 dark:bg-[#1f2329] border-r border-gray-200 dark:border-[#2f343d] shrink-0"
            :class="showSidebar ? 'flex w-full sm:w-64' : 'hidden sm:flex sm:w-64'">



            <!-- Channels & DMs List -->
            <div class="flex-1 overflow-y-auto p-2 pt-3 custom-scrollbar space-y-4">

                <!-- Section: Channels -->
                <div>
                    <div
                        class="flex items-center justify-between px-2 py-1.5 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider select-none">
                        <span>Channels</span>
                        <button type="button" @click="groupModalOpen = true"
                            class="p-0.5 rounded hover:bg-gray-200 dark:hover:bg-[#2a2f37] text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors animate-pulse-subtle"
                            title="Create Channel">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="space-y-0.5 mt-1" id="channel-list-container">
                        @foreach($rooms->where('is_group', true) as $r)
                            @php
                                $isSelected = $selectedRoom && $selectedRoom->id === $r->id;
                            @endphp
                            <a href="{{ route('chat.index', ['room_id' => $r->id]) }}" id="room-link-{{ $r->id }}"
                                id="room-link-{{ $r->id }}"
                                class="flex items-center gap-2 px-2.5 py-1.5 rounded text-sm transition-all duration-150 {{ $isSelected ? 'bg-indigo-50 dark:bg-[#3f4550] text-indigo-700 dark:text-white font-semibold' : 'text-slate-500 dark:text-slate-400 hover:bg-gray-200 dark:hover:bg-[#2a2f37] hover:text-slate-900 dark:text-slate-200' }}">
                                <span class="text-sm text-slate-500 font-bold">#</span>
                                <span class="truncate">{{ $r->display_name }}</span>
                                
                                <span id="unread-badge-{{ $r->id }}" style="{{ $r->unread_count > 0 ? '' : 'display:none;' }}" class="bg-indigo-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full ml-auto shadow-sm">
                                    {{ $r->unread_count }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Section: Direct Messages (Active Rooms Only) -->
                <div>
                    <div class="px-2 py-1.5 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider select-none">
                        Direct Messages
                    </div>

                    <div class="space-y-0.5 mt-1" id="dm-list-container">
                        @foreach($rooms->where('is_group', false) as $r)
                            @php
                                $isSelected = $selectedRoom && $selectedRoom->id === $r->id;
                                $colors = ['#3b82f6', '#a855f7', '#ec4899', '#10b981', '#f59e0b', '#f43f5e'];
                                $initials = collect(explode(' ', $r->display_name))->map(fn($n) => substr($n, 0, 1))->take(1)->join('');
                                $avatarColor = $colors[$r->id % count($colors)];
                            @endphp
                            <a href="{{ route('chat.index', ['room_id' => $r->id]) }}" id="room-link-{{ $r->id }}"
                                id="room-link-{{ $r->id }}"
                                class="w-full flex items-center gap-2 px-2.5 py-1.5 rounded text-left text-sm transition-all duration-150 {{ $isSelected ? 'bg-indigo-50 dark:bg-[#3f4550] text-indigo-700 dark:text-white font-semibold' : 'text-slate-500 dark:text-slate-400 hover:bg-gray-200 dark:hover:bg-[#2a2f37] hover:text-slate-900 dark:text-slate-200' }}">

                                <!-- Small Avatar Circle -->
                                <div style="background-color: {{ $avatarColor }};"
                                    class="w-5 h-5 rounded flex items-center justify-center font-extrabold text-[9px] shrink-0 text-white shadow-sm">
                                    {{ strtoupper($initials) }}
                                </div>

                                <!-- Status dot next to name -->
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>

                                <span class="truncate flex-1">{{ $r->display_name }}</span>
                                
                                <span id="unread-badge-{{ $r->id }}" style="{{ $r->unread_count > 0 ? '' : 'display:none;' }}" class="bg-indigo-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full ml-auto shadow-sm">
                                    {{ $r->unread_count }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Hidden Direct Message form trigger -->
            <form x-ref="dmForm" action="{{ route('chat.rooms.store') }}" method="POST" class="hidden">
                @csrf
                <input type="hidden" name="type" value="dm">
                <input type="hidden" name="user_id" x-model="dmTargetUserId">
            </form>
        </div>

        <!-- Right Column: Chat Pane -->
        <div class="flex-1 h-full flex flex-col bg-white dark:bg-[#17181c] relative overflow-hidden"
            :class="!showSidebar ? 'flex' : 'hidden sm:flex'"
            x-data="chatRoomComponent({{ $selectedRoom ? $selectedRoom->id : 'null' }}, {{ auth()->id() }}, {{ json_encode(auth()->user()->isAdmin() || auth()->user()->isManager() || auth()->user()->hasRole('project-manager')) }}, {{ json_encode($rooms->pluck('id')) }}, {{ \App\Models\ChatMessage::max('id') ?? 0 }})"
            x-init="init()"
            @dragenter.prevent="if(roomId) handleDragEnter($event)"
            @dragover.prevent="if(roomId) handleDragOver($event)"
            @dragleave.prevent="if(roomId) handleDragLeave($event)"
            @drop.prevent="if(roomId) handleDrop($event)">

            <!-- Full Screen Dropzone Overlay -->
            <div x-cloak x-show="dragOver"
                 class="absolute inset-0 z-[9999] bg-white dark:bg-[#17181c]/90 border-[6px] border-dashed border-indigo-500 m-4 rounded-3xl flex flex-col items-center justify-center backdrop-blur-md transition-all pointer-events-none">
                 <div class="text-indigo-400 text-6xl mb-6 animate-bounce">📎</div>
                 <div class="text-indigo-100 text-3xl font-bold mb-2">Drop file to attach</div>
                 <div class="text-indigo-300 text-xl">Release anywhere in the chat</div>
            </div>

            <!-- ── Offline Banner ── -->
            <div x-cloak x-show="isOffline" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2"
                class="shrink-0 flex items-center justify-center gap-2 px-4 py-2 bg-amber-500/20 border-b border-amber-500/30 text-xs font-medium text-amber-300 z-40">
                <svg class="w-4 h-4 shrink-0 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M18.364 5.636a9 9 0 010 12.728M15.536 8.464a5 5 0 010 7.072M12 12h.01M8.464 8.464a5 5 0 000 7.072M5.636 5.636a9 9 0 000 12.728" />
                </svg>
                You are offline — messages cannot be sent until your connection is restored.
            </div>

            <!-- ── Reconnected flash ── -->
            <div x-cloak x-show="justReconnected" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-500"
                x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2"
                class="shrink-0 flex items-center justify-center gap-2 px-4 py-2 bg-emerald-500/20 border-b border-emerald-500/30 text-xs font-medium text-emerald-300 z-40">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Back online! You can send messages again.
            </div>

            @if($selectedRoom)
                    <!-- Chat Header -->
                    <div class="relative h-[56px] px-6 flex items-center bg-gray-50 dark:bg-[#1f2329] shrink-0">
                        <div class="flex items-center gap-2 min-w-0">
                            <!-- Mobile Back Button -->
                            <button type="button" @click="showSidebar = true"
                                class="sm:hidden p-2 -ml-2 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white rounded-lg hover:bg-gray-200 dark:hover:bg-[#2a2f37]"
                                aria-label="Back to conversations list">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                                    </path>
                                </svg>
                            </button>

                            <!-- Status dot/prefix -->
                            <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0 select-none"></span>

                            <!-- Name & Member list -->
                            <div class="min-w-0">
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white truncate leading-tight flex items-center gap-1.5">
                                    @if($selectedRoom->is_group)
                                        {{-- Clickable group name --}}
                                        <button type="button" id="group-name-btn"
                                            @click="toggleMembersPanel({{ $selectedRoom->id }})"
                                            class="hover:text-indigo-300 transition-colors duration-150 flex items-center gap-1.5 group"
                                            title="View members">
                                            {{ $selectedRoom->display_name }}
                                            <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400 group-hover:text-indigo-300 transition-colors"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                        </button>
                                    @else
                                        {{ $selectedRoom->display_name }}
                                        <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400 cursor-pointer" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.907c.961 0 1.371 1.24.588 1.81l-3.97 2.883a1 1 0 00-.364 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.971-2.883a1 1 0 00-1.18 0l-3.97 2.883c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.364-1.118L2.98 9.72c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z">
                                            </path>
                                        </svg>
                                    @endif
                                </h3>
                            </div>
                        </div>

                        <!-- Center Search Bar (Centered and compact) -->
                        <div class="hidden sm:block absolute left-1/2 -translate-x-1/2 w-[28rem] max-w-[45%]">
                            <button type="button"
                                @click="searchOverlayOpen = true; $nextTick(() => $refs.overlaySearchInput.focus());"
                                class="w-full flex items-center justify-between px-4 py-1.5 rounded-xl text-xs bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors text-left">
                                <span class="flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                    <span>Search rooms</span>
                                </span>
                                <span
                                    class="text-[10px] bg-slate-800 px-1.5 py-0.5 rounded text-slate-500 dark:text-slate-400 font-mono select-none">(Ctrl+K)</span>
                            </button>
                        </div>

                    </div>

                    <!-- Chat Messages Body -->
                    <div class="flex-1 relative overflow-hidden flex flex-col justify-end"
                        @click="closeMembersPanelOnOutsideClick($event)">

                        <!-- Members Slide-in Panel -->
                        <div id="members-panel" x-cloak x-show="membersPanelOpen"
                            x-transition:enter="transition ease-out duration-250"
                            x-transition:enter-start="opacity-0 translate-x-full"
                            x-transition:enter-end="opacity-100 translate-x-0"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 translate-x-0"
                            x-transition:leave-end="opacity-0 translate-x-full"
                            @keydown.window.escape="membersPanelOpen = false"
                            class="absolute right-0 top-0 h-full w-72 bg-gray-50 dark:bg-[#1f2329] border-l border-gray-200 dark:border-[#2f343d] z-30 flex flex-col shadow-2xl">

                            <!-- Panel Header -->
                            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-[#2f343d] shrink-0">
                                <h4 class="text-sm font-semibold text-white flex items-center gap-2">
                                    <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    Members
                                    <span class="text-xs text-slate-500 dark:text-slate-400 font-normal" x-show="groupMembers.length > 0"
                                        x-text="'(' + groupMembers.length + ')'" x-cloak></span>
                                </h4>
                                <button type="button" @click="membersPanelOpen = false"
                                    class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-gray-200 dark:hover:bg-[#2a2f37] transition-colors"
                                    title="Close">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Loading State -->
                            <div x-show="membersLoading" class="flex-1 flex items-center justify-center" x-cloak>
                                <svg class="animate-spin w-6 h-6 text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                                    </circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                            </div>

                            <!-- Panel Body (visible when not loading) -->
                            <div x-show="!membersLoading" class="flex-1 flex flex-col overflow-hidden" x-cloak>

                                <!-- ── Current Members List ── -->
                                <div class="flex-1 overflow-y-auto p-3 space-y-1 custom-scrollbar">
                                    <p class="px-1 pb-1 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Current
                                        Members</p>

                                    <template x-for="member in groupMembers" :key="member.id">
                                        <div
                                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-gray-200 dark:hover:bg-[#2a2f37] transition-colors duration-150 group">
                                            <!-- Avatar -->
                                            <div :style="'background-color: ' + getMemberColor(member.id)"
                                                class="w-8 h-8 rounded-lg flex items-center justify-center font-extrabold text-xs shrink-0 text-white shadow-sm">
                                                <span x-text="getMemberInitials(member.name)"></span>
                                            </div>
                                            <!-- Name + Email -->
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-medium text-slate-900 dark:text-slate-200 truncate" x-text="member.name"></p>
                                                <p class="text-[10px] text-slate-500 truncate" x-text="member.email"></p>
                                            </div>
                                            <template x-if="member.id === currentUserId">
                                                <span
                                                    class="text-[10px] px-1.5 py-0.5 rounded-md bg-indigo-600/30 text-indigo-300 font-semibold shrink-0">You</span>
                                            </template>
                                            <template x-if="member.id !== currentUserId && currentUserCanRemove">
                                                <button type="button" @click="removeMemberFromGroup(member)"
                                                    :disabled="memberActionLoading === member.id"
                                                    class="shrink-0 flex items-center justify-center w-7 h-7 rounded-lg bg-red-500/15 hover:bg-red-500/30 text-red-400 hover:text-red-300 transition-all duration-150 border border-red-500/20 hover:border-red-400/40"
                                                    title="Remove from group">
                                                    <!-- Trash icon -->
                                                    <svg x-show="memberActionLoading !== member.id" class="w-3.5 h-3.5"
                                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                    <!-- Spinner while loading -->
                                                    <svg x-show="memberActionLoading === member.id"
                                                        class="animate-spin w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg"
                                                        fill="none" viewBox="0 0 24 24" style="display:none;">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                            stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor"
                                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                                    </svg>
                                                </button>
                                            </template>
                                        </div>
                                    </template>

                                    <!-- Empty state -->
                                    <div x-show="groupMembers.length === 0"
                                        class="flex flex-col items-center justify-center h-24 text-slate-500 text-sm" x-cloak>
                                        No members found
                                    </div>
                                </div>

                                <!-- ── Add Members Section ── -->
                                <div class="border-t border-gray-200 dark:border-[#2f343d] shrink-0">
                                    <!-- Toggle button -->
                                    <button type="button" x-show="currentUserCanRemove"
                                        @click="addMemberSectionOpen = !addMemberSectionOpen; if(addMemberSectionOpen) { $nextTick(() => $refs.addMemberSearch?.focus()); }"
                                        class="w-full flex items-center gap-2 px-4 py-3 text-xs font-semibold text-indigo-400 hover:text-indigo-300 hover:bg-gray-200 dark:hover:bg-[#2a2f37] transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4" />
                                        </svg>
                                        Add Members
                                        <svg class="w-3 h-3 ml-auto transition-transform duration-200"
                                            :class="addMemberSectionOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>

                                    <!-- Expandable add section -->
                                    <div x-show="addMemberSectionOpen" x-cloak
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 -translate-y-2"
                                        x-transition:enter-end="opacity-100 translate-y-0" class="px-3 pb-3">

                                        <!-- Search input -->
                                        <div class="relative mb-2">
                                            <input type="text" x-ref="addMemberSearch" x-model="addMemberQuery"
                                                class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-500 placeholder-slate-500"
                                                style="background-color:#2a2f37 !important; color:#cbd5e1 !important; border:none !important;"
                                                placeholder="Search users...">
                                            <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3 h-3 text-slate-500"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                            </svg>
                                        </div>

                                        <!-- Users not in group -->
                                        <div class="space-y-1 overflow-y-auto custom-scrollbar" style="max-height:160px">
                                            <template x-for="u in filteredAddableUsers()" :key="u.id">
                                                <div
                                                    class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-gray-200 dark:hover:bg-[#2a2f37] transition-colors group">
                                                    <div :style="'background-color:' + getMemberColor(u.id)"
                                                        class="w-7 h-7 rounded-lg flex items-center justify-center font-extrabold text-[10px] shrink-0 text-white">
                                                        <span x-text="getMemberInitials(u.name)"></span>
                                                    </div>
                                                    <span class="text-xs text-slate-800 dark:text-slate-300 truncate flex-1" x-text="u.name"></span>
                                                    <button type="button" @click="addMemberToGroup(u)"
                                                        :disabled="memberActionLoading === u.id"
                                                        class="w-6 h-6 flex items-center justify-center rounded-md bg-indigo-600/20 hover:bg-indigo-600 text-indigo-400 hover:text-slate-900 dark:hover:text-white transition-all duration-150 shrink-0"
                                                        title="Add to group">
                                                        <svg x-show="memberActionLoading !== u.id" class="w-3 h-3" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2.5" d="M12 4v16m8-8H4" />
                                                        </svg>
                                                        <svg x-show="memberActionLoading === u.id" class="animate-spin w-3 h-3"
                                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                            style="display:none;">
                                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                                stroke="currentColor" stroke-width="4"></circle>
                                                            <path class="opacity-75" fill="currentColor"
                                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </template>
                                            <!-- No addable users -->
                                            <div x-show="filteredAddableUsers().length === 0"
                                                class="text-center text-[11px] text-slate-600 py-3">
                                                No users to add
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Error toast -->
                                <div x-show="memberActionError" x-cloak
                                    class="mx-3 mb-2 px-3 py-2 bg-red-500/20 border border-red-500/30 rounded-lg text-xs text-red-300"
                                    x-text="memberActionError"></div>
                            </div>
                        </div>

                        <!-- Scrollable Area -->
                        <div id="chat-messages" x-ref="messages" @scroll="updateScrollButton"
                            class="overflow-y-auto p-4 space-y-4 custom-scrollbar flex-1"
                            style="scrollbar-width: thin; scrollbar-color: #3f4550 transparent;">

                            <!-- Render dynamic message list -->
                            <template x-if="messages.length > 0">
                                <div class="space-y-4">
                                    <template x-for="msg in messages" :key="msg.id">
                                        <div class="flex items-end gap-3 px-4 py-1.5"
                                            :class="msg.user_id === currentUserId ? 'justify-end' : 'justify-start'">

                                            <!-- User Initials Avatar -->
                                            <div :style="'background-color: ' + getUserColor(msg.user_id)"
                                                class="w-9 h-9 rounded-full flex items-center justify-center font-extrabold text-xs shrink-0 text-white shadow-sm"
                                                :class="msg.user_id === currentUserId ? 'order-2' : 'order-1'">
                                                <span x-text="getUserInitials(msg.user ? msg.user.name : 'Unknown')"></span>
                                            </div>

                                            <!-- Message Detail -->
                                            <div class="max-w-[78%] min-w-0 px-3 py-2 shadow-sm"
                                                :class="msg.user_id === currentUserId ? 'order-1 bg-indigo-600 text-white rounded-2xl rounded-br-md' : 'order-2 bg-gray-200 dark:bg-[#2a2f37] text-slate-800 dark:text-slate-100 rounded-2xl rounded-bl-md'">
                                                <div class="flex items-baseline gap-2"
                                                    :class="msg.user_id === currentUserId ? 'justify-end' : 'justify-start'">
                                                    <span class="text-xs font-bold"
                                                        :class="msg.user_id === currentUserId ? 'text-indigo-100' : 'text-slate-800 dark:text-slate-100'"
                                                        x-text="msg.user_id === currentUserId ? 'You' : (msg.user ? msg.user.name : 'Unknown User')"></span>
                                                    <span class="text-[10px]"
                                                        :class="msg.user_id === currentUserId ? 'text-indigo-200' : 'text-slate-500 dark:text-slate-400'"
                                                        x-text="formatTime(msg.created_at)"></span>
                                                </div>
                                                <div x-show="msg.message"
                                                    class="text-sm mt-1 leading-relaxed whitespace-pre-wrap"
                                                    x-text="msg.message"></div>


                                                <!-- Attached File Block -->
                                                <template x-if="msg.attachment_name">
                                                    <div class="mt-2">
                                                        <!-- Render Embedded PDF Viewer IF file ends with .pdf -->
                                                        <template x-if="msg.attachment_name.toLowerCase().endsWith('.pdf')">
                                                            <div
                                                                class="w-full min-w-[280px] max-w-md rounded-lg overflow-hidden border border-gray-200 dark:border-[#2f343d] bg-[#1a1d21] shadow-md my-1">
                                                                <iframe :src="attachmentUrl(msg.id, msg.attachment_name)"
                                                                    class="w-full h-64 border-0" type="application/pdf">
                                                                </iframe>
                                                                <div
                                                                    class="p-2 flex items-center justify-between bg-gray-50 dark:bg-[#1f2329] text-xs">
                                                                    <span
                                                                        class="truncate text-slate-800 dark:text-slate-300 font-medium max-w-[180px]"
                                                                        x-text="msg.attachment_name"></span>
                                                                    
                                                                    <div class="flex items-center gap-2 ml-2 shrink-0">
                                                                        <a :href="downloadAttachmentUrl(msg.id)"
                                                                            class="text-indigo-400 hover:text-indigo-300 p-1.5 rounded hover:bg-gray-200 dark:hover:bg-[#2a2f37] transition-colors"
                                                                            title="Download"
                                                                            aria-label="Download attachment">
                                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                                                            </svg>
                                                                        </a>
                                                                        <a :href="attachmentUrl(msg.id, msg.attachment_name)"
                                                                            target="_blank" rel="noopener noreferrer"
                                                                            class="text-indigo-400 hover:text-indigo-300 underline font-semibold">
                                                                            Open
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </template>

                                                        <!-- Standard Link for other attachments -->
                                                        <template x-if="!msg.attachment_name.toLowerCase().endsWith('.pdf')">
                                                            <div class="flex items-center justify-between gap-3">
                                                                <a :href="attachmentUrl(msg.id, msg.attachment_name)"
                                                                    target="_blank" rel="noopener noreferrer"
                                                                    class="inline-flex min-w-0 flex-1 items-center gap-1.5 text-xs transition-colors"
                                                                    :class="msg.user_id === currentUserId ? 'text-indigo-100 hover:text-slate-900 dark:hover:text-white' : 'text-indigo-300 hover:text-indigo-200'"
                                                                    :title="msg.attachment_name">
                                                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor"
                                                                        viewBox="0 0 24 24" aria-hidden="true">
                                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                                            stroke-width="2"
                                                                            d="M15.172 7 8.586 13.586a2 2 0 1 0 2.828 2.828l6.414-6.586a4 4 0 0 0-5.656-5.656l-6.415 6.585a6 6 0 1 0 8.486 8.486L20.5 13">
                                                                        </path>
                                                                    </svg>
                                                                    <span class="truncate" x-text="msg.attachment_name"></span>
                                                                </a>
                                                                
                                                                <a :href="downloadAttachmentUrl(msg.id)"
                                                                    class="shrink-0 p-1 rounded transition-colors"
                                                                    :class="msg.user_id === currentUserId ? 'text-indigo-200 hover:bg-indigo-500 hover:text-slate-900 dark:hover:text-white' : 'text-indigo-400 hover:bg-gray-50 dark:bg-[#1f2329] hover:text-indigo-200'"
                                                                    title="Download"
                                                                    aria-label="Download attachment">
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                                                    </svg>
                                                                </a>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                </div>
                            </template>
                        </div>
                        </template>

                        <!-- Empty Welcome message in Rocket.Chat format -->
                        <template x-if="messages.length === 0">
                            <div class="h-full flex flex-col items-center justify-center p-8 text-center bg-white dark:bg-[#17181c]">
                                @php
                                    $selInitials = collect(explode(' ', $selectedRoom->display_name))->map(fn($n) => substr($n, 0, 1))->take(1)->join('');
                                    $colors = ['#3b82f6', '#a855f7', '#ec4899', '#10b981', '#f59e0b', '#f43f5e'];
                                    $selAvatarColor = $colors[$selectedRoom->id % count($colors)];
                                @endphp
                                <div style="background-color: {{ $selectedRoom->is_group ? '#6366f1' : $selAvatarColor }};"
                                    class="w-16 h-16 rounded-xl flex items-center justify-center font-extrabold text-2xl text-white shadow-lg mb-4">
                                    {{ strtoupper($selInitials) }}
                                </div>

                                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">
                                    {{ $selectedRoom->is_group ? 'This is the start of the #' . $selectedRoom->display_name . ' channel' : 'You have joined a new direct message with' }}
                                </h3>

                                @if(!$selectedRoom->is_group)
                                    <div
                                        class="inline-flex items-center gap-1.5 px-3 py-1 bg-[#2f343d] rounded-lg text-sm text-slate-900 dark:text-slate-200 font-medium">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <span>{{ $selectedRoom->display_name }}</span>
                                    </div>
                                @endif


                            </div>
                        </template>
                    </div>

                    <!-- Scroll to bottom button -->
                    <button type="button" x-cloak x-show="showScrollButton" @click="scrollToBottom(true)"
                        class="absolute right-6 bottom-20 w-9 h-9 rounded-full bg-[#3f4550] hover:bg-[#4f5664] text-white shadow-xl flex items-center justify-center transition-all hover:scale-105 active:scale-95 duration-150 z-20"
                        title="Scroll to latest messages">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 13-7 7-7-7"></path>
                        </svg>
                    </button>
                </div>

                <!-- Chat Input Form -->
                <div class="p-4 bg-white dark:bg-[#17181c] border-t border-gray-200 dark:border-[#2f343d] shrink-0">
                    <form @submit.prevent="sendMessage">
                        <div
                            :class="{'border-indigo-500 bg-indigo-500/10': dragOver, 'border-gray-200 dark:border-[#2f343d] bg-gray-100 dark:bg-[#1d2025]': !dragOver}"
                            class="relative flex items-center border focus-within:border-slate-500 rounded-lg overflow-hidden transition-colors">
                            <input type="file" x-ref="attachmentInput" @change="selectAttachment($event)" class="hidden">
                            <button type="button" @click="$refs.attachmentInput.click()"
                                class="ml-3 shrink-0 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors"
                                :class="{'text-indigo-300': attachment}" title="Attach a file" aria-label="Attach a file">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15.172 7 8.586 13.586a2 2 0 1 0 2.828 2.828l6.414-6.586a4 4 0 0 0-5.656-5.656l-6.415 6.585a6 6 0 1 0 8.486 8.486L20.5 13">
                                    </path>
                                </svg>
                            </button>
                            <!-- Attachment Chip inside message box -->
                            <div x-show="attachment" style="display: none;" class="flex items-center gap-2 pl-3 pr-2 py-1.5 bg-indigo-500/20 text-indigo-300 rounded-md border border-indigo-500/30 shrink-0 max-w-[150px]">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7 8.586 13.586a2 2 0 1 0 2.828 2.828l6.414-6.586a4 4 0 0 0-5.656-5.656l-6.415 6.585a6 6 0 1 0 8.486 8.486L20.5 13"></path>
                                </svg>
                                <span class="truncate text-xs font-medium" x-text="attachment?.name"></span>
                                <button type="button" @click="clearAttachment" class="ml-1 text-indigo-400 hover:text-indigo-200 transition-colors">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>

                            <textarea x-ref="messageInput" x-model="newMessage" rows="1"
                                @keydown.enter="if (!$event.shiftKey) { $event.preventDefault(); sendMessage(); }"
                                @input="$el.style.height = 'auto'; $el.style.height = Math.min($el.scrollHeight, 150) + 'px'"
                                style="resize: none; max-height: 150px; min-height: 44px;"
                                class="w-full min-w-0 bg-transparent border-0 text-slate-900 dark:text-slate-200 placeholder-slate-500 focus:ring-0 text-sm py-3 pl-3 pr-10 focus:outline-none custom-scrollbar"
                                placeholder="Message {{ $selectedRoom->is_group ? '#' . $selectedRoom->display_name : '@' . $selectedRoom->display_name }}"
                                :required="!attachment" :disabled="isSending"></textarea>

                            <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors flex items-center justify-center"
                                :class="{'opacity-50 cursor-not-allowed': isSending}" :disabled="isSending">
                                <svg x-show="!isSending" class="w-5 h-5 rotate-45 text-slate-500 dark:text-slate-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                </svg>
                                <svg x-show="isSending" style="display: none;" class="animate-spin w-5 h-5 text-slate-500 dark:text-slate-400"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                                    </circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                            </button>
                        </div>
                        <!-- Remove the old bottom attachment display -->
                        <!-- Send Error Alert -->
                        <div x-cloak x-show="sendError"
                            class="mt-2 flex items-center gap-2 px-3 py-2 rounded-lg bg-red-500/15 border border-red-500/25 text-xs text-red-300"
                            role="alert">
                            <svg class="w-4 h-4 shrink-0 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M18.364 5.636a9 9 0 010 12.728M15.536 8.464a5 5 0 010 7.072M12 12h.01M8.464 8.464a5 5 0 000 7.072M5.636 5.636a9 9 0 000 12.728" />
                            </svg>
                            <span x-text="sendError"></span>
                            <button type="button" @click="sendError=''"
                                class="ml-auto text-red-400 hover:text-red-200 transition-colors" title="Dismiss">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            @else
            <!-- Header bar for empty state -->
            <div class="h-[56px] px-6 flex items-center justify-between bg-gray-50 dark:bg-[#1f2329] shrink-0">
                <div class="flex items-center gap-2 min-w-0"></div>

                <!-- Center Search Bar -->
                <div class="hidden sm:flex flex-1 justify-center max-w-xs md:max-w-md mx-4">
                    <button type="button"
                        @click="searchOverlayOpen = true; $nextTick(() => $refs.overlaySearchInput.focus());"
                        class="w-full flex items-center justify-between px-4 py-1.5 rounded-xl text-xs bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors text-left">
                        <span class="flex items-center gap-2">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <span>Search rooms</span>
                        </span>
                        <span
                            class="text-[10px] bg-slate-800 px-1.5 py-0.5 rounded text-slate-500 dark:text-slate-400 font-mono select-none">(Ctrl+K)</span>
                    </button>
                </div>

                <div class="flex items-center gap-4 text-slate-500 dark:text-slate-400">
                    <button type="button" @click="groupModalOpen = true;" class="hover:text-slate-900 dark:hover:text-white transition-colors"
                        title="Create Channel/Group">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                            </path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- No Active Chat Selected Empty State -->
            <div class="flex-1 flex flex-col items-center justify-center p-8 text-center bg-white dark:bg-[#17181c]">
                <div
                    class="w-16 h-16 rounded-full bg-gray-50 dark:bg-[#1f2329] flex items-center justify-center text-slate-500 dark:text-slate-400 mb-4 border border-gray-200 dark:border-[#2f343d]">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                        </path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Select a Conversation</h3>
                <p class="text-sm text-slate-500 mt-1 max-w-sm">Select an active conversation, start a direct message,
                    or create a channel/group from the left sidebar list to begin messaging.</p>
            </div>
        @endif
    </div>

    <!-- New Group/Channel Modal -->
    <div x-cloak x-show="groupModalOpen" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black/75 backdrop-blur-sm transition-opacity" @click="groupModalOpen = false">
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal Panel (Matching CRM theme) -->
            <div
                class="inline-block align-middle bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100 dark:border-gray-200 dark:border-slate-700">
                <form action="{{ route('chat.rooms.store') }}" method="POST" class="p-6">
                    @csrf
                    <input type="hidden" name="type" value="group">

                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Create New Channel / Group
                    </h3>

                    <!-- Group Name Input -->
                    <div class="mb-6">
                        <label for="group_name"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Channel Name
                            <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="group_name" required
                            style="background-color: #334155 !important; border-color: transparent !important; color: #d1d5db !important;"
                            class="w-full px-4 py-2.5 border-0 rounded-xl text-sm placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 transition-colors"
                            placeholder="e.g. general-chat">
                    </div>

                    <!-- Members Checklist -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Add
                            Members</label>
                        <div class="overflow-y-auto border border-gray-100 dark:border-gray-200 dark:border-slate-700 rounded-xl p-2.5 space-y-2 bg-gray-50 dark:bg-slate-900/50 custom-scrollbar"
                            style="max-height: 180px !important;">
                            @foreach($users as $u)
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-700 cursor-pointer transition-colors text-gray-700 dark:text-gray-300">
                                    <input type="checkbox" name="user_ids[]" value="{{ $u->id }}"
                                        style="background-color: #334155 !important; border-color: transparent !important; color: #6366f1 !important; border-radius: 6px !important;"
                                        class="rounded focus:ring-2 focus:ring-indigo-500">
                                    <span class="text-sm font-medium">{{ $u->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-200 dark:border-slate-700">
                        <button type="button" @click="groupModalOpen = false"
                            class="px-5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition-colors shadow-lg shadow-indigo-600/25 flex items-center gap-2">
                            Create Channel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Floating Quick Search Modal (Rocket.Chat style) -->
    <div x-cloak x-show="searchOverlayOpen" class="fixed inset-0 z-50 flex items-start justify-center pt-20 px-4"
        @keydown.window.escape="searchOverlayOpen = false; overlaySearchQuery = '';"
        @keydown.window.ctrl.k.prevent="searchOverlayOpen = true; $nextTick(() => $refs.overlaySearchInput.focus());">

        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity"
            @click="searchOverlayOpen = false; overlaySearchQuery = '';"></div>

        <!-- Modal Content Card (Matching CRM theme) -->
        <div class="relative w-full bg-white dark:bg-slate-800 border border-gray-100 dark:border-gray-200 dark:border-slate-700 rounded-2xl shadow-2xl overflow-hidden text-gray-700 dark:text-gray-200 z-10"
            style="max-width: 450px !important; width: 100% !important;">

            <!-- Search input header -->
            <div
                class="p-2.5 border-b border-gray-100 dark:border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-900/50 flex items-center justify-between">
                <div class="relative flex-1">
                    <input type="text" x-model="overlaySearchQuery" x-ref="overlaySearchInput"
                        class="w-full pl-4 pr-10 py-1.5 text-xs rounded-xl bg-slate-200 dark:bg-slate-700 text-slate-900 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:outline-none placeholder-gray-500 dark:placeholder-gray-400 transition-colors"
                        placeholder="Search rooms (Ctrl+K)">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Results List -->
            <div class="overflow-y-auto p-2 custom-scrollbar space-y-4 bg-white dark:bg-slate-800"
                style="max-height: 240px !important;">

                <!-- Section: Channels -->
                <div>
                    <div class="px-3 py-1 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider select-none">
                        Channels
                    </div>
                    <div class="space-y-0.5 mt-1" id="channel-list-container">
                        @foreach($rooms->where('is_group', true) as $r)
                            <a href="{{ route('chat.index', ['room_id' => $r->id]) }}" id="room-link-{{ $r->id }}"
                                x-show="overlaySearchQuery === '' || '{{ strtolower($r->display_name) }}'.includes(overlaySearchQuery.toLowerCase())"
                                @click="searchOverlayOpen = false; overlaySearchQuery = '';"
                                class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 hover:text-gray-900 dark:hover:text-slate-900 dark:hover:text-white transition-all duration-150">
                                <span class="text-sm font-bold text-gray-400">#</span>
                                <span class="truncate font-medium flex-1">{{ $r->display_name }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Section: People / Direct Messages (Displays all usernames by default!) -->
                <div>
                    <div class="px-3 py-1 text-[11px] font-bold text-gray-400 uppercase tracking-wider select-none">
                        People
                    </div>
                    <div class="space-y-0.5 mt-1">
                        @foreach($users as $u)
                            @php
                                $activeRoom = $rooms->first(function ($room) use ($u) {
                                    return !$room->is_group && $room->members->contains('id', $u->id);
                                });
                                $colors = ['#3b82f6', '#a855f7', '#ec4899', '#10b981', '#f59e0b', '#f43f5e'];
                                $initials = collect(explode(' ', $u->name))->map(fn($n) => substr($n, 0, 1))->take(1)->join('');
                                $avatarColor = $colors[$u->id % count($colors)];
                            @endphp
                            <button type="button"
                                @click="openOrCreateDM({{ $u->id }}, {{ $activeRoom ? $activeRoom->id : 'null' }}); searchOverlayOpen = false; overlaySearchQuery = '';"
                                x-show="overlaySearchQuery === '' || '{{ strtolower($u->name) }}'.includes(overlaySearchQuery.toLowerCase())"
                                class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 hover:text-gray-900 dark:hover:text-slate-900 dark:hover:text-white transition-all duration-150">
                                <div style="background-color: {{ $avatarColor }};"
                                    class="w-5 h-5 rounded flex items-center justify-center font-extrabold text-[9px] shrink-0 text-white shadow-sm">
                                    {{ strtoupper($initials) }}
                                </div>
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                <span class="truncate font-medium flex-1">{{ $u->name }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }

        /* Custom scrollbar styles */
        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: #2f343d;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background-color: #3f4550;
        }
    </style>

    <script>
        function chatViewComponent() {
            return {
                searchQuery: '',
                searchOpen: false,
                searchOverlayOpen: false,
                overlaySearchQuery: '',
                showSidebar: {{ request()->has('room_id') ? 'false' : 'true' }},
                groupModalOpen: false,
                dmTargetUserId: null,

                openOrCreateDM(userId, roomId) {
                    if (roomId) {
                        window.location.href = "{{ route('chat.index') }}?room_id=" + roomId;
                    } else {
                        this.dmTargetUserId = userId;
                        this.$nextTick(() => {
                            this.$refs.dmForm.submit();
                        });
                    }
                }
            };
        }

        document.addEventListener('alpine:init', () => {
            Alpine.data('chatRoomComponent', (roomId, currentUserId, canRemoveMembers, allRoomIds, maxMessageIdAtLoad) => ({
                roomId: roomId,
                currentUserId: currentUserId,
                currentUserCanRemove: canRemoveMembers,
                allRoomIds: allRoomIds || [],
                maxMessageIdAtLoad: maxMessageIdAtLoad || 0,
                audioCtx: null,

                // Members Panel
                membersPanelOpen: false,
                groupMembers: [],
                membersLoading: false,
                _membersPanelRoomId: null,

                async toggleMembersPanel(roomId) {
                    // If already open for this room, just close
                    if (this.membersPanelOpen && this._membersPanelRoomId === roomId) {
                        this.membersPanelOpen = false;
                        return;
                    }

                    this.membersPanelOpen = true;
                    this._membersPanelRoomId = roomId;
                    this.groupMembers = [];
                    this.membersLoading = true;

                    try {
                        const res = await axios.get(`${this.membersBaseUrl}/${roomId}/members`);
                        this.groupMembers = res.data.members || [];
                    } catch (err) {
                        console.error('[Members] Failed to load members:', err);
                    } finally {
                        this.membersLoading = false;
                    }
                },

                closeMembersPanelOnOutsideClick(event) {
                    if (!this.membersPanelOpen) return;
                    const panel = document.getElementById('members-panel');
                    const nameBtn = document.getElementById('group-name-btn');
                    if (panel && !panel.contains(event.target) && nameBtn && !nameBtn.contains(event.target)) {
                        this.membersPanelOpen = false;
                    }
                },

                getMemberInitials(name) {
                    if (!name) return 'U';
                    return name.trim().split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
                },

                getMemberColor(userId) {
                    const colors = ['#3b82f6', '#a855f7', '#ec4899', '#10b981', '#f59e0b', '#f43f5e'];
                    return colors[userId % colors.length];
                },

                // Whether the current user can remove members
                currentUserCanRemove: canRemoveMembers,

                filteredAddableUsers() {
                    const memberIds = new Set(this.groupMembers.map(m => m.id));
                    const q = this.addMemberQuery.trim().toLowerCase();
                    return this.allUsers.filter(u =>
                        !memberIds.has(u.id) &&
                        (q === '' || u.name.toLowerCase().includes(q))
                    );
                },

                async addMemberToGroup(user) {
                    this.memberActionError = '';
                    this.memberActionLoading = user.id;
                    try {
                        const res = await axios.post(`${this.membersBaseUrl}/${this._membersPanelRoomId}/members`, {
                            user_id: user.id
                        });
                        this.groupMembers = res.data.members || [];
                        this.addMemberQuery = '';
                    } catch (err) {
                        this.memberActionError = err.response?.data?.message || 'Failed to add member.';
                        setTimeout(() => this.memberActionError = '', 3000);
                    } finally {
                        this.memberActionLoading = null;
                    }
                },

                async removeMemberFromGroup(member) {
                    if (!confirm(`Remove ${member.name} from this group?`)) return;
                    this.memberActionError = '';
                    this.memberActionLoading = member.id;
                    try {
                        const res = await axios.delete(`${this.membersBaseUrl}/${this._membersPanelRoomId}/members/${member.id}`);
                        this.groupMembers = res.data.members || [];
                    } catch (err) {
                        this.memberActionError = err.response?.data?.error || 'Failed to remove member.';
                        setTimeout(() => this.memberActionError = '', 3000);
                    } finally {
                        this.memberActionLoading = null;
                    }
                },
                // Offline detection
                isOffline: !navigator.onLine,
                justReconnected: false,

                roomId: roomId,
                currentUserId: currentUserId,
                messages: @json($selectedRoom ? $messages : []),
                seenMessageIds: new Set(), // track message IDs to prevent duplicates
                newMessage: '',
                attachment: null,
                isSending: false,
                sendError: '',
                showScrollButton: false,
                dragOver: false,
                dragCounter: 0,
                messageEndpoint: @json($selectedRoom ? route('chat.rooms.messages.store', ['room' => $selectedRoom]) : ''),
                attachmentBaseUrl: @json(url('chat/messages')),
                membersBaseUrl: @json(url('chat/rooms')),
                // All users for the add-member list
                allUsers: @json($users->map(fn($u) => ['id' => $u->id, 'name' => $u->name, 'email' => $u->email])),
                // Add member panel state
                addMemberSectionOpen: false,
                addMemberQuery: '',
                memberActionLoading: null,  // userId being actioned
                memberActionError: '',

                init() {
                    

                    // Offline / online detection
                    window.addEventListener('offline', () => {
                        this.isOffline = true;
                        this.justReconnected = false;
                    });
                    window.addEventListener('online', () => {
                        this.isOffline = false;
                        this.justReconnected = true;
                        setTimeout(() => { this.justReconnected = false; }, 3000);
                    });

                    if (!this.roomId) return;

                    // Seed seen IDs from server-rendered messages
                    this.messages.forEach(m => this.seenMessageIds.add(m.id));

                    this.$nextTick(() => {
                        this.scrollToBottom();
                    });

                    // Subscribe to Pusher — retry every 500ms until Echo is available (max 10s)
                    this.subscribeToEcho();

                    // Request notification permission
                    if ('Notification' in window && Notification.permission === 'default') {
                        Notification.requestPermission();
                    }
                },

                subscribeToEcho(attempts = 0) {
                    const MAX_ATTEMPTS = 20; // 20 x 500ms = 10 seconds

                    if (window.Echo) {
                        console.log('[Echo] Subscribing to private channel: chat-room.' + this.roomId);

                        this.allRoomIds.forEach(id => {
                            window.Echo.private(`chat-room.${id}`)
                                .listen('.ChatMessageSent', (e) => {
                                    console.log(`[Echo] Message received from Pusher for room ${id}:`, e);
                                    const msg = e.message;

                                    // Deduplicate globally for both active and background rooms
                                    if (this.seenMessageIds.has(msg.id)) {
                                        console.log('[Echo] Skipping duplicate message id:', msg.id);
                                        return;
                                    }
                                    
                                    // Prevent double-counting messages that were already in the DB on page load
                                    if (msg.id <= this.maxMessageIdAtLoad) {
                                        console.log('[Echo] Skipping message already accounted for on page load:', msg.id);
                                        return;
                                    }
                                    
                                    this.seenMessageIds.add(msg.id);

                                    // Bump room to the top of its section
                                    const link = document.getElementById(`room-link-${id}`);
                                    if (link && link.parentNode) {
                                        link.parentNode.prepend(link);
                                    }

                                    if (id === this.roomId) {
                                        // Active room
                                        const shouldFollow = this.isNearBottom();
                                        this.messages.push(msg);

                                        if (msg.user_id !== this.currentUserId) {
                                            
                                            this.showBrowserNotification(msg);
                                        }

                                        this.$nextTick(() => {
                                            if (shouldFollow) {
                                                this.scrollToBottom();
                                            } else {
                                                this.updateScrollButton();
                                            }
                                        });
                                    } else {
                                        // Different room
                                        if (msg.user_id !== this.currentUserId) {
                                            
                                            this.showBrowserNotification(msg);

                                            const badge = document.getElementById(`unread-badge-${id}`);
                                            if (badge) {
                                                badge.style.display = 'inline-block';
                                                badge.textContent = parseInt(badge.textContent || '0') + 1;
                                            }
                                        }
                                    }
                                });
                        });

                        console.log('[Echo] ✅ Subscribed successfully.');

                    } else if (attempts < MAX_ATTEMPTS) {
                        console.log(`[Echo] Not ready yet, retrying... (attempt ${attempts + 1}/${MAX_ATTEMPTS})`);
                        setTimeout(() => this.subscribeToEcho(attempts + 1), 500);
                    } else {
                        console.error('[Echo] ❌ Failed to initialize after 10 seconds. Real-time disabled.');
                    }
                },

                showBrowserNotification(msg) {
                    // Browser notification logic removed as it is now handled centrally by FCM
                    // in firebase-init.blade.php and the service worker.
                },

                initAudioContext() {
                    try {
                        const AudioContext = window.AudioContext || window.webkitAudioContext;
                        this.audioCtx = new AudioContext();
                        
                        // Unlock audio on first interaction
                        const unlockAudio = () => {
                            if (this.audioCtx.state === 'suspended') {
                                this.audioCtx.resume();
                            }
                            
                            // Play a silent oscillator to permanently unlock the audio context on mobile/safari
                            const osc = this.audioCtx.createOscillator();
                            const gain = this.audioCtx.createGain();
                            gain.gain.value = 0;
                            osc.connect(gain);
                            gain.connect(this.audioCtx.destination);
                            osc.start(0);
                            osc.stop(0.001);
                            
                            document.removeEventListener('click', unlockAudio);
                            document.removeEventListener('touchstart', unlockAudio);
                            document.removeEventListener('keydown', unlockAudio);
                        };
                        
                        document.addEventListener('click', unlockAudio);
                        document.addEventListener('touchstart', unlockAudio);
                        document.addEventListener('keydown', unlockAudio);
                    } catch(e) {
                        console.warn('[Audio] Web Audio API not supported', e);
                    }
                },

                playNotificationSound() {
                    if (!this.audioCtx) return;
                    
                    try {
                        if (this.audioCtx.state === 'suspended') {
                            this.audioCtx.resume();
                        }

                        // First tone (high)
                        const osc1 = this.audioCtx.createOscillator();
                        const gain1 = this.audioCtx.createGain();
                        osc1.connect(gain1);
                        gain1.connect(this.audioCtx.destination);
                        osc1.type = 'sine';
                        osc1.frequency.setValueAtTime(1046, this.audioCtx.currentTime); // C6
                        gain1.gain.setValueAtTime(0.15, this.audioCtx.currentTime);
                        gain1.gain.exponentialRampToValueAtTime(0.001, this.audioCtx.currentTime + 0.25);
                        osc1.start(this.audioCtx.currentTime);
                        osc1.stop(this.audioCtx.currentTime + 0.25);

                        // Second tone (lower, slightly delayed)
                        const osc2 = this.audioCtx.createOscillator();
                        const gain2 = this.audioCtx.createGain();
                        osc2.connect(gain2);
                        gain2.connect(this.audioCtx.destination);
                        osc2.type = 'sine';
                        osc2.frequency.setValueAtTime(784, this.audioCtx.currentTime + 0.15); // G5
                        gain2.gain.setValueAtTime(0.0, this.audioCtx.currentTime);
                        gain2.gain.setValueAtTime(0.12, this.audioCtx.currentTime + 0.15);
                        gain2.gain.exponentialRampToValueAtTime(0.001, this.audioCtx.currentTime + 0.5);
                        osc2.start(this.audioCtx.currentTime + 0.15);
                        osc2.stop(this.audioCtx.currentTime + 0.5);
                    } catch (e) {
                        console.warn('[Audio] Failed to play notification sound', e);
                    }
                },

                async initFirebasePush() {
                    if (!('Notification' in window) || !('serviceWorker' in navigator)) return;
                    if (!window.__FCM_VAPID_KEY__) return; // skip if not configured

                    try {
                        const permission = await Notification.requestPermission();
                        if (permission !== 'granted') return;

                        // Dynamic import Firebase only when key is available
                        const { initializeApp } = await import('https://www.gstatic.com/firebasejs/10.12.0/firebase-app.js');
                        const { getMessaging, getToken, onMessage } = await import('https://www.gstatic.com/firebasejs/10.12.0/firebase-messaging.js');

                        const app = initializeApp(window.__FIREBASE_CONFIG__);
                        const messaging = getMessaging(app);

                        const token = await getToken(messaging, { vapidKey: window.__FCM_VAPID_KEY__ });
                        if (token) {
                            console.log('[FCM] Token:', token);
                            // Send token to server
                            axios.post('/fcm/token', { token }).catch(() => { });
                        }

                        // Foreground messages are handled globally in firebase-init.blade.php
                        // No need for a duplicate listener here.
                    } catch (err) {
                        console.warn('[FCM] Push init failed:', err.message);
                    }
                },

                isNearBottom() {
                    const container = this.$refs.messages;
                    return container && container.scrollHeight - container.scrollTop - container.clientHeight < 100;
                },

                updateScrollButton() {
                    this.showScrollButton = !this.isNearBottom();
                },

                scrollToBottom(smooth = false) {
                    const container = this.$refs.messages;
                    if (!container) return;
                    container.scrollTo({
                        top: container.scrollHeight,
                        behavior: smooth ? 'smooth' : 'auto',
                    });
                    this.showScrollButton = false;
                },

                formatTime(dateString) {
                    const date = new Date(dateString);
                    return date.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
                },

                getUserInitials(name) {
                    if (!name) return 'U';
                    return name.trim().split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
                },

                getUserColor(userId) {
                    const colors = ['#3b82f6', '#a855f7', '#ec4899', '#10b981', '#f59e0b', '#f43f5e'];
                    return colors[userId % colors.length];
                },

                attachmentUrl(messageId, filename) {
                    return `${this.attachmentBaseUrl}/${messageId}/attachment/${encodeURIComponent(filename)}`;
                },

                downloadAttachmentUrl(messageId) {
                    return `${this.attachmentBaseUrl}/${messageId}/download`;
                },

                selectAttachment(event) {
                    this.attachment = event.target.files[0] || null;
                    if (this.attachment) {
                        this.$nextTick(() => {
                            if (this.$refs.messageInput) this.$refs.messageInput.focus();
                        });
                    }
                },

                handleDragEnter(event) {
                    if (event.dataTransfer && event.dataTransfer.types) {
                        if (!Array.from(event.dataTransfer.types).includes('Files')) {
                            return;
                        }
                    }
                    this.dragCounter++;
                    if (this.dragCounter === 1) {
                        this.dragOver = true;
                    }
                },

                handleDragLeave(event) {
                    if (event.dataTransfer && event.dataTransfer.types) {
                        if (!Array.from(event.dataTransfer.types).includes('Files')) {
                            return;
                        }
                    }
                    this.dragCounter--;
                    if (this.dragCounter <= 0) {
                        this.dragCounter = 0;
                        this.dragOver = false;
                    }
                },

                handleDragOver(event) {
                    if (event.dataTransfer && event.dataTransfer.types) {
                        if (!Array.from(event.dataTransfer.types).includes('Files')) {
                            return;
                        }
                    }
                    event.preventDefault();
                },

                handleDrop(event) {
                    event.preventDefault();
                    this.dragCounter = 0;
                    this.dragOver = false;
                    
                    // Safely extract files from dataTransfer
                    let files = null;
                    if (event.dataTransfer && event.dataTransfer.files) {
                        files = event.dataTransfer.files;
                    }

                    if (files && files.length > 0) {
                        this.attachment = files[0];
                        // Also try assigning to the hidden input to be perfectly safe with all form interactions
                        try {
                            if (this.$refs.attachmentInput) {
                                this.$refs.attachmentInput.files = files;
                            }
                        } catch (e) {
                            console.log("Could not assign to input.files, falling back to state attachment only", e);
                        }

                        this.$nextTick(() => {
                            if (this.$refs.messageInput) this.$refs.messageInput.focus();
                        });
                    } else {
                        console.warn("Drop event contained no files!");
                    }
                },

                clearAttachment() {
                    this.attachment = null;
                    if (this.$refs.attachmentInput) this.$refs.attachmentInput.value = '';
                },

                async sendMessage() {
                    const message = this.newMessage.trim();
                    if ((!message && !this.attachment) || this.isSending) return;

                    // Check connectivity before even trying
                    if (!navigator.onLine) {
                        this.sendError = '📶 No internet connection. Please check your network and try again.';
                        return;
                    }

                    this.isSending = true;
                    this.sendError = '';

                    try {
                        const payload = new FormData();
                        payload.append('message', message);
                        if (this.attachment) payload.append('attachment', this.attachment);

                        const response = await axios.post(this.messageEndpoint, payload);
                        const sentMsg = response.data.message;

                        // Track sent message so Echo event doesn't duplicate it
                        this.seenMessageIds.add(sentMsg.id);
                        this.messages.push(sentMsg);

                        // Bump active room to the top of its section
                        const link = document.getElementById(`room-link-${this.roomId}`);
                        if (link && link.parentNode) {
                            link.parentNode.prepend(link);
                        }

                        this.newMessage = '';
                        if (this.$refs.messageInput) {
                            this.$refs.messageInput.style.height = 'auto';
                        }
                        this.clearAttachment();
                        this.$nextTick(() => {
                            this.scrollToBottom(true);
                            this.$refs.messageInput?.focus();
                        });
                    } catch (error) {
                        console.error('[Chat] Send error:', error);
                        // Detect network/offline errors
                        const isNetworkError = !navigator.onLine
                            || error.code === 'ERR_NETWORK'
                            || error.message === 'Network Error'
                            || !error.response;
                        if (isNetworkError) {
                            this.sendError = '📶 No internet connection. Please check your network and try again.';
                        } else {
                            this.sendError = error.response?.data?.message || 'Unable to send. Please try again.';
                        }
                    } finally {
                        this.isSending = false;
                    }
                }
            }));
        });
    </script>
</x-app-layout>