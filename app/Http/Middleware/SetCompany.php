<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCompany
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip company selection for company management routes, selection routes, and auth routes
        if ($request->routeIs('companies.*') || $request->routeIs('companies.select*') || $request->routeIs('login') || $request->routeIs('register') || $request->routeIs('logout')) {
            return $next($request);
        }

        // Check if company is selected in session
        $companyId = session('selected_company_id');
        
        if (!$companyId) {
            // No company selected, redirect to company selection
            return redirect()->route('companies.select');
        }

        // Verify company exists
        $company = Company::find($companyId);
        if (!$company) {
            session()->forget('selected_company_id');
            return redirect()->route('companies.select');
        }

        // Share company with all views
        view()->share('currentCompany', $company);

        return $next($request);
    }
}
