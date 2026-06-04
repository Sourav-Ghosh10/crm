<x-app-layout>
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('tasks.index') }}" class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Edit Task</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Update schedule or details</p>
        </div>
    </div>

    <div class="max-w-3xl">
        <form method="POST" action="{{ route('tasks.update', $task) }}" class="space-y-6">
            @csrf
            @method('PATCH')

            <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-slate-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Task Details</h3>
                
                <!-- Title -->
                <div class="mb-6">
                    <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" id="title" value="{{ old('title', $task->title) }}" required
                        placeholder="e.g. Client Strategy Session"
                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-700 border-0 rounded-xl text-sm text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-indigo-500">
                    @error('title')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Category -->
                    <div class="mb-6">
                        <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Category <span class="text-red-500">*</span></label>
                        <select name="category" id="category" required
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-700 border-0 rounded-xl text-sm text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-indigo-500">
                            <option value="Task" {{ old('category', $task->category) == 'Task' ? 'selected' : '' }}>Task</option>
                            <option value="Meeting" {{ old('category', $task->category) == 'Meeting' ? 'selected' : '' }}>Meeting</option>
                            <option value="Call" {{ old('category', $task->category) == 'Call' ? 'selected' : '' }}>Call</option>
                        </select>
                        @error('category')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Priority -->
                    <div class="mb-6">
                        <label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Priority <span class="text-red-500">*</span></label>
                        <select name="priority" id="priority" required
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-700 border-0 rounded-xl text-sm text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-indigo-500">
                            <option value="Low" {{ old('priority', $task->priority) == 'Low' ? 'selected' : '' }}>Low</option>
                            <option value="Medium" {{ old('priority', $task->priority) == 'Medium' ? 'selected' : '' }}>Medium</option>
                            <option value="High" {{ old('priority', $task->priority) == 'High' ? 'selected' : '' }}>High</option>
                        </select>
                        @error('priority')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Due At -->
                    <div class="mb-6">
                        <label for="due_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date & Time <span class="text-red-500">*</span></label>
                        <input type="datetime-local" name="due_at" id="due_at" value="{{ old('due_at', $task->due_at ? $task->due_at->format('Y-m-d\TH:i') : '') }}" required
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-700 border-0 rounded-xl text-sm text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-indigo-500">
                        @error('due_at')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Location -->
                    <div class="mb-6">
                        <label for="location" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Location / Link</label>
                        <input type="text" name="location" id="location" value="{{ old('location', $task->location) }}"
                            placeholder="e.g. Zoom, Microsoft Teams, Office"
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-700 border-0 rounded-xl text-sm text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-indigo-500">
                        @error('location')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Related Client -->
                <div class="mb-6">
                    <label for="client_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Related Client (Optional)</label>
                    <select name="client_id" id="client_id"
                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-700 border-0 rounded-xl text-sm text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-indigo-500">
                        <option value="">No specific client</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ old('client_id', $task->client_id) == $client->id ? 'selected' : '' }}>{{ $client->full_name }}</option>
                        @endforeach
                    </select>
                    @error('client_id')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description / Notes</label>
                    <textarea name="description" id="description" rows="4"
                        placeholder="Add some details about this task..."
                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-700 border-0 rounded-xl text-sm text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-indigo-500">{{ old('description', $task->description) }}</textarea>
                    @error('description')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center gap-4">
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition-colors shadow-lg shadow-indigo-600/25">
                    Update Task
                </button>
                <a href="{{ route('tasks.index') }}" class="px-6 py-2.5 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-gray-300 rounded-xl text-sm font-medium hover:bg-gray-200 dark:hover:bg-slate-600 transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
