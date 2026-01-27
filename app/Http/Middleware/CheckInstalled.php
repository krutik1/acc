<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckInstalled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the application is installed using ENV or file existence
        $isInstalled = env('APP_INSTALLED', false) || file_exists(storage_path('installed'));

        // If trying to access installer but already installed -> redirect to dashboard
        if ($isInstalled && $request->is('install*')) {
            return redirect('/');
        }

        // If NOT installed, and NOT accessing installer -> redirect to installer
        if (!$isInstalled && !$request->is('install*')) {
            return redirect('/install');
        }

        return $next($request);
    }
}
