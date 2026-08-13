<?php

namespace App\Http\Controllers;

use App\Models\CallLog;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CallLogController extends Controller
{
    /**
     * Display a listing of all call logs (for Admin/Manager).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isManagement = $user->isAdmin() || $user->isManager() || $user->hasRole('project-manager');
        
        $query = CallLog::with(['client', 'staffMember', 'creator']);

        // Filter by date range
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('call_start_time', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('call_start_time', '<=', $request->end_date);
        }

        // Filter by call result
        if ($request->has('call_result') && $request->call_result) {
            $query->where('call_result', $request->call_result);
        }

        // Enforce restriction to own logs for non-management
        if (!$isManagement) {
            $query->where('staff_member_id', $user->id);
        } else {
            // Only allow filtering by staff member if user is management
            if ($request->has('staff_member_id') && $request->staff_member_id) {
                $query->where('staff_member_id', $request->staff_member_id);
            }
        }

        // Filter by client
        if ($request->has('client_id') && $request->client_id) {
            $query->where('client_id', $request->client_id);
        }

        // Sort by most recent
        $callLogs = $query->orderBy('call_start_time', 'desc')->paginate(20);

        return view('call-logs.index', compact('callLogs'));
    }

    /**
     * Display call logs for a specific client.
     */
    public function clientIndex(Client $client)
    {
        $callLogs = CallLog::where('client_id', $client->id)
            ->orderBy('call_start_time', 'desc')
            ->get();

        return view('call-logs.client-index', compact('callLogs', 'client'));
    }

    /**
     * Show the form for creating a new call log.
     */
    public function create(Client $client)
    {
        return view('call-logs.create', compact('client'));
    }

    /**
     * Store a newly created call log.
     */
    public function store(Request $request, Client $client)
    {
        $validated = $request->validate([
            'phone_number' => 'required|string|max:20',
            'dialer_platform' => 'required|string|max:100',
            'call_direction' => 'required|in:Incoming,Outgoing',
            'call_start_time' => 'required|date',
            'call_end_time' => 'required|date|after:call_start_time',
            'call_result' => 'required|string|max:100',
            'notes' => 'nullable|string',
            'next_follow_up_date' => 'nullable|date',
        ]);

        // Calculate call duration
        $startTime = \Carbon\Carbon::parse($validated['call_start_time']);
        $endTime = \Carbon\Carbon::parse($validated['call_end_time']);
        $durationSeconds = $endTime->diffInSeconds($startTime);

        $callLog = CallLog::create([
            'call_record_number' => CallLog::generateCallRecordNumber(),
            'client_id' => $client->id,
            'customer_name' => $client->full_name,
            'phone_number' => $validated['phone_number'],
            'staff_member_id' => Auth::id(),
            'dialer_platform' => $validated['dialer_platform'],
            'call_direction' => $validated['call_direction'],
            'call_start_time' => $validated['call_start_time'],
            'call_end_time' => $validated['call_end_time'],
            'call_duration_seconds' => $durationSeconds,
            'call_result' => $validated['call_result'],
            'notes' => $validated['notes'] ?? null,
            'next_follow_up_date' => $validated['next_follow_up_date'] ?? null,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('clients.call-logs.index', $client)
            ->with('success', 'Call log created successfully.');
    }

    /**
     * Display the specified call log.
     */
    public function show(CallLog $callLog)
    {
        $callLog->load(['client', 'staffMember', 'creator', 'updater']);

        return view('call-logs.show', compact('callLog'));
    }

    /**
     * Show the form for editing the specified call log.
     * Only Admin and Manager can edit.
     */
    public function edit(CallLog $callLog)
    {
        $user = Auth::user();
        
        // Agents cannot edit old records
        if ($user->isAgent()) {
            abort(403, 'Agents cannot edit call records. Please contact your manager.');
        }

        $callLog->load(['client', 'staffMember']);

        return view('call-logs.edit', compact('callLog'));
    }

    /**
     * Update the specified call log.
     * Only Admin and Manager can update, and they must provide a reason.
     */
    public function update(Request $request, CallLog $callLog)
    {
        $user = Auth::user();

        // Agents cannot update records
        if ($user->isAgent()) {
            abort(403, 'Agents cannot edit call records. Please contact your manager.');
        }

        $validated = $request->validate([
            'phone_number' => 'required|string|max:20',
            'dialer_platform' => 'required|string|max:100',
            'call_direction' => 'required|in:Incoming,Outgoing',
            'call_start_time' => 'required|date',
            'call_end_time' => 'required|date|after:call_start_time',
            'call_result' => 'required|string|max:100',
            'notes' => 'nullable|string',
            'next_follow_up_date' => 'nullable|date',
            'admin_edit_reason' => 'required|string|min:10|max:500',
        ]);

        // Calculate call duration
        $startTime = \Carbon\Carbon::parse($validated['call_start_time']);
        $endTime = \Carbon\Carbon::parse($validated['call_end_time']);
        $durationSeconds = $endTime->diffInSeconds($startTime);

        $callLog->update([
            'phone_number' => $validated['phone_number'],
            'dialer_platform' => $validated['dialer_platform'],
            'call_direction' => $validated['call_direction'],
            'call_start_time' => $validated['call_start_time'],
            'call_end_time' => $validated['call_end_time'],
            'call_duration_seconds' => $durationSeconds,
            'call_result' => $validated['call_result'],
            'notes' => $validated['notes'] ?? null,
            'next_follow_up_date' => $validated['next_follow_up_date'] ?? null,
            'updated_by' => Auth::id(),
            'admin_edit_reason' => $validated['admin_edit_reason'],
        ]);

        return redirect()->route('call-logs.show', $callLog)
            ->with('success', 'Call log updated successfully.');
    }

    /**
     * Remove the specified call log from storage.
     * Soft delete is not used - records are permanent.
     * Only Admin can delete.
     */
    public function destroy(CallLog $callLog)
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
            abort(403, 'Only administrators can delete call records.');
        }

        $client = $callLog->client;
        
        $callLog->delete();

        return redirect()->route('clients.call-logs.index', $client)
            ->with('success', 'Call record deleted successfully.');
    }
}
