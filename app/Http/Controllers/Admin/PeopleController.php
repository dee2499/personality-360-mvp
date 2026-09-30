<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Models\User;
use App\Services\AssessmentScoreService;
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

        $surveys = Survey::all();

        $participants = User::query()
            ->where('role', 'participant')
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
        $selectedSurveyId = $request->query('survey_id');
        $selectedSurvey = $selectedSurveyId ? Survey::find($selectedSurveyId) : null;

        $surveys = Survey::all();

        // Calculate combined score strictly for this subject
        $metrics = $this->scoreService->calculateSubjectCombinedScore($person, $selectedSurvey);

        return view('admin.people.show', compact('person', 'metrics', 'surveys', 'selectedSurvey'));
    }
}
