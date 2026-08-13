<x-app-layout>
    <div class="max-w-4xl mx-auto py-8">
        
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                <a href="{{ route('crm-projects.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Projects</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                <a href="{{ route('crm-projects.show', $project->id) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">{{ $project->project_name }}</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                <a href="{{ route('crm-projects.docs', $project->id) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Docs & Files</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                <span class="truncate max-w-xs">{{ $document->title }}</span>
            </div>
            
            <div class="flex items-center justify-between">
                <h1 class="text-4xl font-extrabold text-white tracking-tight">
                    {{ $document->title }}
                </h1>
                
                <a href="{{ route('crm-projects.docs', $project->id) }}" class="px-4 py-2 text-sm font-medium text-gray-300 hover:text-white bg-slate-800 hover:bg-slate-700 rounded-lg transition-colors border border-slate-700">
                    Back to Docs
                </a>
            </div>
            <div class="text-sm text-gray-400 mt-3">
                Posted by <span class="font-bold text-gray-300">{{ $document->user->name ?? 'Unknown' }}</span> on {{ $document->created_at->format('M j, Y') }}
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-8 min-h-[500px]">
            <div class="prose dark:prose-invert max-w-none">
                {!! nl2br(e($document->content)) !!}
            </div>
        </div>
    </div>
</x-app-layout>
