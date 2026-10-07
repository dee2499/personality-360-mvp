<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Survey;
use App\Models\User;
use App\Services\AssessmentScoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PeopleController extends Controller
{
    public function __construct(
        protected AssessmentScoreService $scoreService
    ) {}

    /**
     * Display people directory with assessment completion stats.
     */
    public function index(Request $request): View
    {
        $selectedSurveyId = $request->query('survey_id');
        $selectedSurvey = $selectedSurveyId ? Survey::find($selectedSurveyId) : null;

        $isManager = $request->user()->isManager();
        $managerCompanyId = $isManager ? $request->user()->company_id : session('admin_selected_company_id');

        if ($isManager && ! $managerCompanyId) {
            abort(403, 'Your account is designated as manager but has not been assigned to a company yet.');
        }

        $surveys = $managerCompanyId
            ? Survey::where('company_id', $managerCompanyId)->get()
            : Survey::all();

        $participants = User::query()
            ->where('role', 'participant')
            ->when($managerCompanyId, fn ($q) => $q->where('company_id', $managerCompanyId))
            ->when($selectedSurvey, function ($query, $survey) {
                $query->whereHas('surveys', fn ($q) => $q->where('surveys.id', $survey->id));
            })
            ->with(['assessmentsReceived'])
            ->get()
            ->map(function (User $person) use ($selectedSurvey) {
                $metrics = $this->scoreService->calculateSubjectCombinedScore($person, $selectedSurvey);
                $person->metrics = $metrics;

                return $person;
            });

        return view('admin.people.index', compact('participants', 'surveys', 'selectedSurvey'));
    }

    /**
     * Display individual person profile with combined score, meter, and subject assessments.
     */
    public function show(User $person, Request $request): View
    {
        if ($request->user()->isManager() && $person->company_id !== $request->user()->company_id) {
            abort(403, 'You are not authorized to view employees outside your company.');
        }

        $selectedSurveyId = $request->query('survey_id');
        $selectedSurvey = $selectedSurveyId ? Survey::find($selectedSurveyId) : null;

        $surveys = $request->user()->isManager()
            ? Survey::where('company_id', $request->user()->company_id)->get()
            : Survey::all();

        // Get all surveys associated with this person (as participant or in assessments)
        $userSurveys = Survey::query()
            ->where(function ($query) use ($person) {
                $query->whereHas('participants', fn ($q) => $q->where('users.id', $person->id))
                    ->orWhereHas('assessments', fn ($q) => $q->where('assessor_id', $person->id)->orWhere('subject_id', $person->id));
            })
            ->with(['questions'])
            ->get()
            ->map(function (Survey $survey) use ($person) {
                $givenAssessments = Assessment::where('survey_id', $survey->id)
                    ->where('assessor_id', $person->id)
                    ->get();
                $givenTotal = $givenAssessments->count();
                $givenCompleted = $givenAssessments->where('status', 'completed')->count();
                $givenPending = $givenTotal - $givenCompleted;

                $receivedAssessments = Assessment::where('survey_id', $survey->id)
                    ->where('subject_id', $person->id)
                    ->get();
                $receivedTotal = $receivedAssessments->count();
                $receivedCompleted = $receivedAssessments->where('status', 'completed')->count();
                $receivedPending = $receivedTotal - $receivedCompleted;

                $surveyMetrics = $this->scoreService->calculateSubjectCombinedScore($person, $survey);

                $isCompleted = ($givenTotal > 0 ? $givenPending === 0 : true)
                    && ($receivedTotal > 0 ? $receivedPending === 0 : true)
                    && ($givenTotal > 0 || $receivedTotal > 0);

                $isPending = ($givenPending > 0 || $receivedPending > 0);

                return [
                    'survey' => $survey,
                    'is_completed' => $isCompleted,
                    'is_pending' => $isPending,
                    'given_total' => $givenTotal,
                    'given_completed' => $givenCompleted,
                    'given_pending' => $givenPending,
                    'received_total' => $receivedTotal,
                    'received_completed' => $receivedCompleted,
                    'received_pending' => $receivedPending,
                    'metrics' => $surveyMetrics,
                ];
            });

        $pendingSurveys = $userSurveys->filter(fn ($s) => $s['is_pending']);
        $completedSurveys = $userSurveys->filter(fn ($s) => $s['is_completed']);

        // Calculate combined score (for selected survey, or overall across all surveys)
        $metrics = $this->scoreService->calculateSubjectCombinedScore($person, $selectedSurvey);

        return view('admin.people.show', compact(
            'person',
            'metrics',
            'surveys',
            'selectedSurvey',
            'userSurveys',
            'pendingSurveys',
            'completedSurveys'
        ));
    }

    /**
     * Delete an employee/participant and all related evaluation records.
     */
    public function destroy(Request $request, User $person): RedirectResponse
    {
        if ($request->user()->isManager() && $person->company_id !== $request->user()->company_id) {
            abort(403, 'You are not authorized to delete employees outside your company.');
        }

        if ($person->id === $request->user()->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $name = $person->name;
        $company = $person->company;
        $person->delete();

        if ($company) {
            return redirect()->route('admin.companies.show', $company)
                ->with('success', "Employee '{$name}' has been deleted successfully.");
        }

        return redirect()->route('admin.people.index')
            ->with('success', "Person '{$name}' has been deleted successfully.");
    }
}
