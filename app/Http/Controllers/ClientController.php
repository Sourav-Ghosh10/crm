<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    /**
     * Display a listing of clients.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isManagement = $user->isManagement();
        $search = $request->input('search', '');
        $status = $request->input('status', '');
        $filter = $request->input('filter', '');
        
        $clients = Client::with(['agent', 'creator'])
            ->when($filter === 'deleted' && $isManagement, function ($query) {
                return $query->onlyTrashed();
            })
            ->when($filter === 'active', function ($query) {
                return $query->whereIn('status', ['New', 'Follow-up', 'In Progress']);
            })
            ->when(!$isManagement, function ($query) use ($user) {
                return $query->where('agent_id', $user->id);
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('customer_number', 'like', "%{$search}%")
                      ->orWhere('company_name', 'like', "%{$search}%");
                });
            })
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(10);

        $statuses = Client::getStatuses();
        
        return view('clients.index', compact('clients', 'search', 'status', 'statuses', 'filter'));
    }

    /**
     * Show the form for creating a new client.
     */
    public function create()
    {
        $user = Auth::user();
        $statuses = Client::getStatuses();
        
        // Get agents list based on user role
        if ($user->isAgent()) {
            // Agents can only assign to themselves
            $agents = collect([$user]);
        } else {
            // Admin, Manager and Team Lead can assign to any agent
            $agents = User::whereIn('role', [
                User::ROLE_ADMIN, 
                User::ROLE_MANAGER, 
                User::ROLE_TEAM_LEAD, 
                User::ROLE_AGENT, 
                User::ROLE_BEADER
            ])->get();
        }
        
        return view('clients.create', compact('statuses', 'agents', 'user'));
    }

    /**
     * Store a newly created client.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:' . implode(',', array_keys(Client::getStatuses()))],
        ]);

        // Check for duplicate phone or email
        $existingClient = Client::where('phone', $request->phone)
            ->orWhere(function($q) use ($request) {
                $q->whereNotNull('email')->where('email', $request->email);
            })
            ->first();

        if ($existingClient) {
            return back()->with('error', 'A client with this phone or email already exists.')->withInput();
        }

        // Generate unique customer number
        $customerNumber = 'CLT-' . strtoupper(uniqid());

        // Determine agent_id - always default to logged in user
        $agentId = $request->agent_id;
        if (!$agentId || empty($agentId)) {
            // Default to current logged in user
            $agentId = $user->id;
        }

        Client::create([
            'customer_number' => $customerNumber,
            'full_name' => $request->full_name,
            'phone' => $request->phone,
            'alternate_phone' => $request->alternate_phone,
            'email' => $request->email,
            'company_name' => $request->company_name,
            'tags' => $request->tags,
            'status' => $request->status,
            'agent_id' => $agentId,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('clients.index')->with('success', 'Client created successfully.');
    }

    /**
     * Display the specified client.
     */
    public function show(Client $client)
    {
        if (Auth::user()->isAgent() && $client->agent_id !== Auth::id()) {
            abort(403, 'Unauthorized access to client.');
        }

        $client->load(['agent', 'creator', 'updater']);
        
        return view('clients.show', compact('client'));
    }

    /**
     * Show the form for editing the specified client.
     */
    public function edit(Client $client)
    {
        if (Auth::user()->isAgent() && $client->agent_id !== Auth::id()) {
            abort(403, 'Unauthorized access to client.');
        }

        $user = Auth::user();
        $statuses = Client::getStatuses();
        
        // Get agents list based on user role
        if ($user->isAgent()) {
            // Agents can only assign to themselves
            $agents = collect([$user]);
        } else {
            // Admin, Manager and Team Lead can assign to any agent
            $agents = User::whereIn('role', [
                User::ROLE_ADMIN, 
                User::ROLE_MANAGER, 
                User::ROLE_TEAM_LEAD, 
                User::ROLE_AGENT, 
                User::ROLE_BEADER
            ])->get();
        }
        
        return view('clients.edit', compact('client', 'statuses', 'agents'));
    }

    /**
     * Update the specified client.
     */
    public function update(Request $request, Client $client)
    {
        if (Auth::user()->isAgent() && $client->agent_id !== Auth::id()) {
            abort(403, 'Unauthorized access to client.');
        }

        $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:' . implode(',', array_keys(Client::getStatuses()))],
        ]);

        // Check for duplicate phone or email (excluding current client)
        $existingClient = Client::where('phone', $request->phone)
            ->where('id', '!=', $client->id)
            ->first();

        if ($existingClient) {
            return back()->with('error', 'A client with this phone already exists.')->withInput();
        }

        // Determine agent_id - always default to current user if not provided
        $agentId = $request->agent_id;
        if (!$agentId || empty($agentId)) {
            $agentId = Auth::id();
        }

        $client->update([
            'full_name' => $request->full_name,
            'phone' => $request->phone,
            'alternate_phone' => $request->alternate_phone,
            'email' => $request->email,
            'company_name' => $request->company_name,
            'tags' => $request->tags,
            'status' => $request->status,
            'agent_id' => $agentId,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('clients.index')->with('success', 'Client updated successfully.');
    }

    /**
     * Remove the specified client.
     */
    public function destroy(Client $client)
    {
        if (Auth::user()->isAgent() && $client->agent_id !== Auth::id()) {
            abort(403, 'Unauthorized access to client.');
        }

        $client->delete();

        return redirect()->route('clients.index')->with('success', 'Client deleted successfully.');
    }

    /**
     * AJAX Search for clients
     */
    public function search(Request $request)
    {
        $user = Auth::user();
        $isManagement = $user->isManagement();
        $search = $request->get('search', '');
        $status = $request->get('status', '');
        $filter = $request->get('filter', '');
        
        $clients = Client::with(['agent', 'creator'])
            ->when($filter === 'deleted' && $isManagement, function ($query) {
                return $query->onlyTrashed();
            })
            ->when($filter === 'active', function ($query) {
                return $query->whereIn('status', ['New', 'Follow-up', 'In Progress']);
            })
            ->when(!$isManagement, function ($query) use ($user) {
                return $query->where('agent_id', $user->id);
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('customer_number', 'like', "%{$search}%")
                      ->orWhere('company_name', 'like', "%{$search}%");
                });
            })
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->latest()
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'clients' => $clients,
            'count' => $clients->count()
        ]);
    }

    /**
     * Restore the specified soft-deleted client.
     */
    public function restore(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->isManagement()) {
            abort(403, 'Unauthorized access.');
        }

        $client = Client::onlyTrashed()->findOrFail($id);
        $client->restore();

        return redirect()->route('clients.index')->with('success', 'Client restored successfully.');
    }

    /**
     * Permanently delete the specified soft-deleted client.
     */
    public function forceDelete(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->isManagement()) {
            abort(403, 'Unauthorized access.');
        }

        $client = Client::onlyTrashed()->findOrFail($id);
        $client->forceDelete();

        return redirect()->route('clients.index')->with('success', 'Client permanently deleted successfully.');
    }
}
