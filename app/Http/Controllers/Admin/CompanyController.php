<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use App\Notifications\EmployeeInvitationNotification;
use App\Services\AssessmentGenerationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CompanyController extends Controller
{
    /**
     * Display a listing of all companies.
     */
    public function index(): View
    {
        $companies = Company::withCount(['users', 'surveys'])
            ->orderBy('name')
            ->get();

        return view('admin.companies.index', compact('companies'));
    }

    /**
     * Show the form for creating a new company.
     */
    public function create(): View
    {
        return view('admin.companies.create');
    }

    /**
     * Store a newly created company in storage.
     */
    public function store(Request $request): RedirectResponse
    {
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
     * Display the specified company with its employees and surveys.
     */
    public function show(Company $company): View
    {
        $company->load([
            'users' => fn ($q) => $q->orderBy('name'),
            'surveys' => fn ($q) => $q->withCount(['participants', 'assessments'])->orderByDesc('created_at'),
        ]);

        return view('admin.companies.show', compact('company'));
    }

    /**
     * Show the form for editing the specified company.
     */
    public function edit(Company $company): View
    {
        return view('admin.companies.edit', compact('company'));
    }

    /**
     * Update the specified company in storage.
     */
    public function update(Request $request, Company $company): RedirectResponse
    {
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
    public function destroy(Company $company): RedirectResponse
    {
        $companyName = $company->name;
        $company->delete();

        return redirect()->route('admin.companies.index')
            ->with('success', "Company '{$companyName}' deleted successfully.");
    }

    /**
     * Invite an employee to join the company.
     */
    public function inviteEmployee(Request $request, Company $company): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['nullable', 'in:participant,admin'],
        ]);

        $token = Str::random(40);

        $user = User::create([
            'company_id' => $company->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'] ?? 'participant',
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

        if ($emailSent) {
            return $redirect->with('success', "Employee '{$user->name}' added to {$company->name}! Invitation email sent to {$user->email}.");
        }

        return $redirect->with('warning', "Employee '{$user->name}' added, but email delivery failed: {$emailError}. You can use the activation link below to activate their account.");
    }

    /**
     * Remove / delete an employee from a company and the system.
     */
    public function destroyEmployee(Request $request, Company $company, User $employee): RedirectResponse
    {
        if ($employee->id === $request->user()->id) {
            return back()->with('error', 'You cannot delete your own account.');
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
