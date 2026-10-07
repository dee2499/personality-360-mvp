<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdminOrManager
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || (! $user->isAdmin() && ! $user->isManager())) {
            abort(Response::HTTP_FORBIDDEN, 'Access restricted to administrators and company managers.');
        }

        if ($user->isAdmin()) {
            $allCompanies = Company::orderBy('name')->get();

            if ($request->has('switch_company_id')) {
                $switchId = (int) $request->query('switch_company_id');
                if ($allCompanies->contains('id', $switchId)) {
                    session(['admin_selected_company_id' => $switchId]);
                }
            }

            $selectedCompanyId = session('admin_selected_company_id');
            $activeCompany = $allCompanies->firstWhere('id', $selectedCompanyId);

            if (! $activeCompany && $allCompanies->isNotEmpty()) {
                $activeCompany = $allCompanies->first();
                session(['admin_selected_company_id' => $activeCompany->id]);
            }

            view()->share('allCompanies', $allCompanies);
            view()->share('activeCompany', $activeCompany);
        } elseif ($user->isManager()) {
            $activeCompany = $user->company;
            view()->share('allCompanies', collect($activeCompany ? [$activeCompany] : []));
            view()->share('activeCompany', $activeCompany);
        }

        return $next($request);
    }
}
