<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Survey;
use App\Models\User;
use App\Notifications\EmployeeInvitationNotification;
use App\Services\AssessmentGenerationService;
use App\Services\AssessmentScoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function __construct(
        protected AssessmentScoreService $scoreService
    ) {}

    /**
     * Display a listing of all companies.
     */
    public function index(Request $request): View|RedirectResponse
    {
        if ($request->user()->isManager()) {
            if (! $request->user()->company_id) {
                abort(403, 'You are not assigned to any company.');
            }

            return redirect()->route('admin.companies.show', $request->user()->company_id);
        }

        $companies = Company::withCount(['employees', 'surveys'])
            ->orderBy('name')
            ->get();

        return view('admin.companies.index', compact('companies'));
    }

    /**
     * Show the form for creating a new company.
     */
    public function create(Request $request): View
    {
        if (! $request->user()->isAdmin()) {
            abort(403, 'Access restricted to administrators.');
        }

        return view('admin.companies.create');
    }

    /**
     * Store a newly created company in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        if (! $request->user()->isAdmin()) {
            abort(403, 'Access restricted to administrators.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $company = Company::create($validated);

        return redirect()->route('admin.companies.show', $company)
            ->with('success', "Company '{$company->name}' created successfully! You can now add employees.");
    }

    /**
     * Display the specified company with its employees, surveys, and overall score meter.
     */
    public function show(Company $company, Request $request): View
    {
        if ($request->user()->isManager() && $request->user()->company_id !== $company->id) {
            abort(403, "You are not authorized to view another company's data.");
        }

        $selectedSurveyId = $request->query('survey_id');
        $selectedSurvey = $selectedSurveyId ? Survey::find($selectedSurveyId) : null;

        $company->load([
            'employees' => fn ($q) => $q->orderBy('name'),
            'surveys' => fn ($q) => $q->withCount(['participants', 'assessments'])->orderByDesc('created_at'),
        ]);

        $surveys = $company->surveys;

        $metrics = $this->scoreService->calculateCompanyMetrics($company, $selectedSurvey);
        $targetSurvey = $selectedSurvey ?: $surveys->first();
        $groupInsights = $targetSurvey ? $this->scoreService->calculateGroupInsights($targetSurvey) : null;

        return view('admin.companies.show', compact('company', 'metrics', 'surveys', 'selectedSurvey', 'targetSurvey', 'groupInsights'));
    }

    /**
     * Display the dedicated full Team CQ Sync executive report for a company.
     */
    public function teamSyncReport(Company $company, Request $request): View
    {
        if ($request->user()->isManager() && $request->user()->company_id !== $company->id) {
            abort(403, "You are not authorized to view another company's reports.");
        }

        $selectedSurveyId = $request->query('survey_id');
        $survey = $selectedSurveyId ? Survey::find($selectedSurveyId) : $company->surveys()->first();

        if (! $survey) {
            abort(404, 'No surveys found for this organization.');
        }

        $insights = $this->scoreService->calculateGroupInsights($survey);

        return view('admin.surveys.team-sync-report', [
            'survey' => $survey,
            'company' => $company,
            'insights' => $insights,
        ]);
    }

    /**
     * Show the form for editing the specified company.
     */
    public function edit(Company $company, Request $request): View
    {
        if (! $request->user()->isAdmin()) {
            abort(403, 'Access restricted to administrators.');
        }

        return view('admin.companies.edit', compact('company'));
    }

    /**
     * Update the specified company in storage.
     */
    public function update(Request $request, Company $company): RedirectResponse
    {
        if (! $request->user()->isAdmin()) {
            abort(403, 'Access restricted to administrators.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $company->update($validated);

        return redirect()->route('admin.companies.show', $company)
            ->with('success', 'Company details updated successfully.');
    }

    /**
     * Remove the specified company from storage.
     */
    public function destroy(Company $company, Request $request): RedirectResponse
    {
        if (! $request->user()->isAdmin()) {
            abort(403, 'Access restricted to administrators.');
        }

        $companyName = $company->name;

        DB::transaction(function () use ($company) {
            // Delete all surveys belonging to this company (questions, assessments, responses cascade)
            foreach ($company->surveys as $survey) {
                $survey->forceDelete();
            }

            // Delete all non-admin employees associated with this company
            $company->employees()->where('role', '!=', 'admin')->each(function (User $employee) {
                $employee->delete();
            });

            // Disassociate any admin users who might have company_id pointing to this company
            User::where('company_id', $company->id)->where('role', 'admin')->update(['company_id' => null]);

            // Delete the company record
            $company->delete();
        });

        return redirect()->route('admin.companies.index')
            ->with('success', "Company '{$companyName}' and its associated records were deleted successfully.");
    }

    /**
     * Invite an employee to join the company.
     */
    public function inviteEmployee(Request $request, Company $company): RedirectResponse
    {
        if ($request->user()->isManager() && $request->user()->company_id !== $company->id) {
            abort(403, 'You are not authorized to invite employees to another company.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['nullable', 'string'],
        ]);

        $requestedRole = $validated['role'] ?? 'participant';
        $role = in_array($requestedRole, ['participant', 'manager'], true) ? $requestedRole : 'participant';
        $token = Str::random(40);

        $user = User::create([
            'company_id' => $company->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $role,
            'password' => Hash::make(Str::random(32)),
            'invitation_token' => $token,
            'invitation_sent_at' => now(),
        ]);

        // Auto-enroll employee into existing surveys belonging to this company
        foreach ($company->surveys as $survey) {
            $survey->participants()->syncWithoutDetaching([$user->id]);
            app(AssessmentGenerationService::class)->generateForSurvey($survey);
        }

        $emailSent = false;
        $emailError = null;

        try {
            $user->notify(new EmployeeInvitationNotification($token, $company->name));
            $emailSent = true;
        } catch (\Throwable $e) {
            $emailError = $e->getMessage();
            Log::error('Failed sending invitation email to '.$user->email.': '.$e->getMessage());
        }

        $activationUrl = route('invitation.show', ['token' => $token]);

        $redirect = redirect()->route('admin.companies.show', $company)
            ->with('invitation_link', $activationUrl)
            ->with('invited_employee', $user->name);

        $roleLabel = $role === 'manager' ? 'Company Manager' : 'Employee';

        if ($emailSent) {
            return $redirect->with('success', "{$roleLabel} '{$user->name}' added to {$company->name}! Invitation email sent to {$user->email}.");
        }

        return $redirect->with('warning', "{$roleLabel} '{$user->name}' added, but email delivery failed: {$emailError}. You can use the activation link below to activate their account.");
    }

    /**
     * Update an employee's role (promote to manager or demote to participant).
     */
    public function updateEmployeeRole(Request $request, Company $company, User $employee): RedirectResponse
    {
        if (! $request->user()->isAdmin()) {
            abort(403, 'Only administrators can assign or modify manager roles.');
        }

        if ($employee->company_id !== $company->id) {
            abort(404, 'Employee does not belong to this company.');
        }

        if ($employee->isAdmin()) {
            return back()->with('error', 'Administrators cannot be modified as company employees.');
        }

        $validated = $request->validate([
            'role' => ['required', 'string', 'in:participant,manager'],
        ]);

        $employee->update(['role' => $validated['role']]);

        $roleTitle = $validated['role'] === 'manager' ? 'Company Manager' : 'Participant';

        return redirect()->route('admin.companies.show', $company)
            ->with('success', "Role for '{$employee->name}' updated to {$roleTitle} successfully.");
    }

    /**
     * Remove / delete an employee from a company and the system.
     */
    public function destroyEmployee(Request $request, Company $company, User $employee): RedirectResponse
    {
        if ($request->user()->isManager() && $request->user()->company_id !== $company->id) {
            abort(403, 'You are not authorized to manage employees of another company.');
        }

        if ($employee->id === $request->user()->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($employee->isAdmin()) {
            return back()->with('error', 'Administrators cannot be managed or deleted as company employees.');
        }

        if ($employee->company_id !== $company->id) {
            abort(404);
        }

        $name = $employee->name;
        $employee->delete();

        return redirect()->route('admin.companies.show', $company)
            ->with('success', "Employee '{$name}' has been deleted successfully.");
    }
}
