<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Perform dynamic permission check
        if (!$user->hasPermissionTo($permission)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access. You do not possess the required permission: ' . $permission
                ], 403);
            }
            abort(403, 'Unauthorized access. You do not have permission to perform this action.');
        }

        return $next($request);
    }
}
