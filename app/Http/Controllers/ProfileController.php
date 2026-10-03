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
     * Show the user profile, 3-meter 360 survey scores (Normalised, Self, Peer), and password management screen.
     */
    public function show(Request $request, AssessmentScoreService $scoreService): View
    {
        $user = $request->user()->load('company');

        $surveys = Survey::query()
            ->where(function ($query) use ($user) {
                $query->whereHas('participants', fn ($q) => $q->where('users.id', $user->id))
                    ->orWhereHas('assessments', fn ($q) => $q->where('assessor_id', $user->id)->orWhere('subject_id', $user->id));
                if ($user->company_id) {
                    $query->orWhere('company_id', $user->company_id);
                }
            })
            ->with(['company', 'questions'])
            ->get()
            ->map(function (Survey $survey) use ($user, $scoreService) {
                $cq = $scoreService->calculateChangeQuotientReport($user, $survey);

                return [
                    'survey' => $survey,
                    'normalised' => $cq['cq3'],
                    'self' => $cq['cq1'],
                    'peer' => $cq['cq2'],
                    'comparison' => $cq['comparison'],
                    'sync' => $cq['cq_sync'],
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
