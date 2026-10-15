<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Models\Survey;
use App\Models\User;
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

                $route = $request->route();
                if ($route) {
                    $routeName = $route->getName();

                    // If on /admin/companies/{company}, redirect to newly selected company
                    if ($routeName === 'admin.companies.show' || $routeName === 'admin.companies.team-sync') {
                        return redirect()->route('admin.companies.show', $switchId);
                    }

                    // If on /admin/people/{person}, redirect to home if person does not belong to new company
                    if ($routeName === 'admin.people.show') {
                        $person = $route->parameter('person');
                        if ($person instanceof User && (int) $person->company_id !== $switchId) {
                            return redirect()->route('admin.dashboard')
                                ->with('info', 'Participant does not belong to the selected company. Redirected to home.');
                        }
                    }

                    // If on /admin/surveys/{survey}, redirect to home if survey doesn't belong to new company
                    if ($routeName === 'admin.surveys.show' || $routeName === 'admin.surveys.team-sync' || $routeName === 'admin.surveys.group-insights') {
                        $survey = $route->parameter('survey');
                        if ($survey instanceof Survey && $survey->company_id && (int) $survey->company_id !== $switchId) {
                            return redirect()->route('admin.dashboard')
                                ->with('info', 'Survey does not belong to the selected company. Redirected to home.');
                        }
                    }
                }

                // Cleanly redirect without lingering switch_company_id / survey_id parameters
                $cleanUrl = $request->fullUrlWithoutQuery(['switch_company_id', 'survey_id']);

                return redirect($cleanUrl);
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
