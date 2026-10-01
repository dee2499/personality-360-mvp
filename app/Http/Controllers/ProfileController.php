<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use App\Services\AssessmentScoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Show the user profile, 360 dual-meter survey scores, and password management screen.
     */
    public function show(Request $request, AssessmentScoreService $scoreService): View
    {
        $user = $request->user()->load('company');

        $surveys = Survey::query()
            ->where(function ($query) use ($user) {
                $query->whereHas('participants', fn ($q) => $q->where('users.id', $user->id))
                    ->orWhereHas('assessments', fn ($q) => $q->where('assessor_id', $user->id)->orWhere('subject_id', $user->id));
            })
            ->with(['company', 'questions'])
            ->get()
            ->map(function (Survey $survey) use ($user, $scoreService) {
                $metrics = $scoreService->calculateSelfAndPeerScores($user, $survey);
                $combined = $scoreService->calculateSubjectCombinedScore($user, $survey);

                return [
                    'survey' => $survey,
                    'self' => $metrics['self'],
                    'peer' => $metrics['peer'],
                    'comparison' => $metrics['comparison'],
                    'combined' => $combined,
                ];
            });

        return view('profile.show', compact('user', 'surveys'));
    }

    /**
     * Update the user password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Your password has been updated successfully.');
    }
}
