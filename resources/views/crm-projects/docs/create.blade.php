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
                <span>New Document</span>
            </div>
            
            <h1 class="text-4xl font-extrabold text-white tracking-tight">
                Create a Document
            </h1>
        </div>

        <div class="bg-slate-800 rounded-xl border border-slate-700 p-6">
            <form action="{{ route('crm-projects.docs.store', $project->id) }}" method="POST">
                @csrf
                
                <div class="mb-6">
                    <label for="title" class="block text-sm font-medium text-gray-300 mb-2">Document Title</label>
                    <input type="text" id="title" name="title" required class="w-full bg-slate-900 border border-slate-700 text-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g. Brainstorming Notes">
                    @error('title')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="content" class="block text-sm font-medium text-gray-300 mb-2">Content</label>
                    <textarea id="content" name="content" rows="12" required class="w-full bg-slate-900 border border-slate-700 text-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500" placeholder="Type your document content here..."></textarea>
                    @error('content')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('crm-projects.docs', $project->id) }}" class="px-5 py-2.5 text-sm font-medium text-gray-300 hover:text-white bg-slate-700 hover:bg-slate-600 rounded-lg transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-[#238636] hover:bg-[#2ea043] rounded-lg transition-colors">
                        Save Document
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
