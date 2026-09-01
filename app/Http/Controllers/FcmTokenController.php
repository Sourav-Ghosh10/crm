<?php

namespace App\Http\Controllers;

use App\Models\FcmToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FcmTokenController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'browser' => 'nullable|string|max:50',
            'device' => 'nullable|string|max:50',
        ]);

        $user = Auth::user();

        // Clear this token from any other users to prevent cross-user duplicate notifications
        // if they logged into the same browser/device sequentially.
        FcmToken::where('token', $request->token)
            ->where('user_id', '!=', $user->id)
            ->delete();

        \App\Models\User::where('fcm_token', $request->token)
            ->where('id', '!=', $user->id)
            ->update(['fcm_token' => null]);

        FcmToken::updateOrCreate(
            [
                'user_id' => $user->id,
                'token' => $request->token,
            ],
            [
                'browser' => $request->browser,
                'device' => $request->device,
            ]
        );

        // Also update the fcm_token field directly on the users table
        $user->update([
            'fcm_token' => $request->token,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'FCM token saved successfully.',
        ]);
    }
}