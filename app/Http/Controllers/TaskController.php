<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(\Illuminate\Http\Request $request)
    {
        $user = auth()->user();
        $isManagement = $user->isManagement();

        $query = Task::with(['client', 'user'])
            ->when(!$isManagement, function($q) use ($user) {
                return $q->where('user_id', $user->id);
            });

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('date')) {
            $query->whereDate('due_at', $request->date);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            if ($request->status === 'completed') {
                $query->where('is_completed', true);
            } elseif ($request->status === 'pending') {
                $query->where('is_completed', false);
            } else {
                $query->where('priority', $request->status);
            }
        }

        $tasks = $query->orderBy('due_at', 'desc')->paginate(20)->withQueryString();

        return view('tasks.index', compact('tasks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();
        $isManagement = $user->isManagement();

        $clients = \App\Models\Client::query()
            ->when(!$isManagement, function($query) use ($user) {
                return $query->where('agent_id', $user->id);
            })
            ->orderBy('full_name')
            ->get();

        return view('tasks.create', compact('clients'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'client_id' => 'nullable|exists:clients,id',
            'location' => 'nullable|string|max:255',
            'category' => 'required|in:Meeting,Call,Task',
            'due_at' => 'required|date',
            'description' => 'nullable|string',
            'priority' => 'required|in:Low,Medium,High',
        ]);

        $task = Task::create([
            'user_id' => auth()->id(),
            'title' => $validated['title'],
            'client_id' => $validated['client_id'],
            'location' => $validated['location'],
            'category' => $validated['category'],
            'due_at' => $validated['due_at'],
            'description' => $validated['description'],
            'priority' => $validated['priority'],
        ]);

        return redirect()->route('dashboard')->with('success', 'Task created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        if (auth()->user()->isAgent() && $task->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to task.');
        }

        return view('tasks.show', compact('task'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        if (auth()->user()->isAgent() && $task->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to task.');
        }

        $user = auth()->user();
        $isManagement = $user->isManagement();

        $clients = \App\Models\Client::query()
            ->when(!$isManagement, function($query) use ($user) {
                return $query->where('agent_id', $user->id);
            })
            ->orderBy('full_name')
            ->get();

        return view('tasks.edit', compact('task', 'clients'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        if (auth()->user()->isAgent() && $task->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to task.');
        }

        if ($request->has('is_completed')) {
            $task->update(['is_completed' => $request->is_completed]);
            return back()->with('success', 'Task status updated.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'client_id' => 'nullable|exists:clients,id',
            'location' => 'nullable|string|max:255',
            'category' => 'required|in:Meeting,Call,Task',
            'due_at' => 'required|date',
            'description' => 'nullable|string',
            'priority' => 'required|in:Low,Medium,High',
        ]);

        $task->update($validated);

        return redirect()->route('tasks.index')->with('success', 'Task updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        if (auth()->user()->isAgent() && $task->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to task.');
        }

        $task->delete();
        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully.');
    }
}
