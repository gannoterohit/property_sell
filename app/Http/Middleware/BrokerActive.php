<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class BrokerActive
{
    public function handle($request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();

        if ($user->role !== 'broker') {
            return $next($request);
        }

        if (!$user->is_broker_active) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'success' => false,
                    'message' => 'Your broker account is pending approval.',
                ], 403);
            }
            return redirect()->route('agent.dashboard')
                ->with('error', 'Your broker account is pending admin approval. You cannot create or modify properties yet.');
        }

        return $next($request);
    }
}
