<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckDriverBlocked
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->is_blocked) {
            // IF AJAX, return generic error, else redirect back with error
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Your account is blocked. You cannot perform this action.'], 403);
            }
            
            return redirect()->route('driver.dashboard')->with('error', 'Your account is blocked. You cannot perform this action.');
        }

        return $next($request);
    }
}
