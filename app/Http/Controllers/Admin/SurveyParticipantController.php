<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
            'new_email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
        ]);

        if (! empty($validated['new_name']) && ! empty($validated['new_email'])) {
            $newUser = User::create([
                'company_id' => $survey->company_id,
                'name' => $validated['new_name'],
                'email' => $validated['new_email'],
                'password' => Hash::make('password'),
                'role' => 'participant',
            ]);

            $survey->participants()->syncWithoutDetaching([$newUser->id]);
        }

        if (! empty($validated['user_ids'])) {
            $query = User::whereIn('id', $validated['user_ids'])
                ->where('role', '!=', 'admin');

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

        return back()->with('success', "Participant '{$participant->name}' removed from survey.");
    }
}
