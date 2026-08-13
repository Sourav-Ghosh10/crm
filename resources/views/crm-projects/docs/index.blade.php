<x-app-layout>
    <div class="max-w-7xl mx-auto py-8">
        
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                <a href="{{ route('crm-projects.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Projects</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                <a href="{{ route('crm-projects.show', $project->id) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">{{ $project->project_name }}</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                <span>Docs & Files</span>
            </div>
            
            <div class="flex items-center justify-between mb-4">
                <h1 class="text-4xl font-extrabold text-[#F78166] tracking-tight">
                    Docs & Files
                </h1>
                
                <a href="{{ route('crm-projects.docs.create', $project->id) }}" class="flex items-center gap-2 px-6 py-2.5 bg-[#238636] hover:bg-[#2ea043] text-white font-semibold rounded-full shadow-sm transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    New
                </a>
            </div>
        </div>

        <div class="bg-slate-800 rounded-xl border border-slate-700 overflow-hidden min-h-[400px]">
            @if($documents->count() > 0)
                <div class="divide-y divide-slate-700/50">
                    @foreach($documents as $doc)
                        <a href="{{ route('crm-projects.docs.show', [$project->id, $doc->id]) }}" class="flex items-start gap-4 p-4 hover:bg-slate-700/30 transition-colors group">
                            <div class="flex-shrink-0">
                                <svg class="w-10 h-10 text-gray-400 group-hover:text-indigo-400 transition-colors" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                                </svg>
                            </div>
                            <div class="flex flex-col">
                                <h3 class="text-lg font-bold text-white group-hover:text-indigo-300 transition-colors">{{ $doc->title }}</h3>
                                <div class="text-sm text-gray-400 mt-1">
                                    {{ $doc->user->name ?? 'Unknown' }} &bull; {{ $doc->created_at->format('M j') }} &bull;
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center h-64 text-gray-400">
                    <svg class="w-12 h-12 mb-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <p class="text-lg font-medium">No documents yet</p>
                    <p class="text-sm">Click "New" to create one.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
