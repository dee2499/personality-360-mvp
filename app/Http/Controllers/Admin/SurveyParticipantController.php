<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Survey;
use App\Models\User;
use App\Services\AssessmentGenerationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SurveyParticipantController extends Controller
{
    public function store(
        Request $request,
        Survey $survey,
        AssessmentGenerationService $generationService
    ): RedirectResponse {
        if ($request->user()->isManager() && $survey->company_id !== $request->user()->company_id) {
            abort(403, "You are not authorized to enroll participants into another company's survey.");
        }

        $validated = $request->validate([
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['exists:users,id'],
            'new_name' => ['nullable', 'string', 'max:255'],
            'new_username' => ['nullable', 'string', 'max:255', 'unique:users,username'],
            'new_email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'new_designation' => ['nullable', 'string', 'max:255'],
            'new_department' => ['nullable', 'string', 'max:255'],
            'new_division' => ['nullable', 'string', 'max:255'],
            'new_age' => ['nullable', 'integer', 'min:15', 'max:100'],
        ]);

        if (! empty($validated['new_name']) && (! empty($validated['new_email']) || ! empty($validated['new_username']))) {
            $company = $survey->company;
            $plainPassword = $company ? $company->defaultUserPassword() : 'changequo123';

            $newUser = User::create([
                'company_id' => $survey->company_id,
                'name' => trim($validated['new_name']),
                'username' => ! empty($validated['new_username']) ? trim($validated['new_username']) : null,
                'email' => ! empty($validated['new_email']) ? trim($validated['new_email']) : null,
                'password' => Hash::make($plainPassword),
                'role' => 'participant',
                'designation' => ! empty($validated['new_designation']) ? trim($validated['new_designation']) : null,
                'department' => ! empty($validated['new_department']) ? trim($validated['new_department']) : null,
                'division' => ! empty($validated['new_division']) ? trim($validated['new_division']) : null,
                'age' => ! empty($validated['new_age']) ? (int) $validated['new_age'] : null,
                'email_verified_at' => now(),
                'invitation_accepted_at' => now(),
            ]);

            $survey->participants()->syncWithoutDetaching([$newUser->id]);
        }

        if (! empty($validated['user_ids'])) {
            $query = User::whereIn('id', $validated['user_ids'])
                ->where('role', 'participant');

            if ($request->user()->isManager()) {
                $query->where('company_id', $request->user()->company_id);
            }

            $nonAdminIds = $query->pluck('id')->all();

            if (! empty($nonAdminIds)) {
                $survey->participants()->syncWithoutDetaching($nonAdminIds);
            }
        }

        // Always generate/synchronize assessments for the entire group
        $generated = $generationService->generateForSurvey($survey);

        return back()->with('success', "Participants enrolled. Updated {$generated} assessment pairings in cohort.");
    }

    public function destroy(Request $request, Survey $survey, User $participant): RedirectResponse
    {
        if ($request->user()->isManager() && $survey->company_id !== $request->user()->company_id) {
            abort(403, "You are not authorized to remove participants from another company's survey.");
        }

        $survey->participants()->detach($participant->id);

        Assessment::where('survey_id', $survey->id)
            ->where(function ($q) use ($participant) {
                $q->where('assessor_id', $participant->id)
                    ->orWhere('subject_id', $participant->id);
            })->delete();

        app(AssessmentGenerationService::class)->generateForSurvey($survey);

        return back()->with('success', "Participant '{$participant->name}' removed from survey.");
    }
}
