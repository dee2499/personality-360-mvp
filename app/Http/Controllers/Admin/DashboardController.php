<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Company;
use App\Models\Survey;
use App\Models\User;
use App\Services\AssessmentScoreService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected AssessmentScoreService $scoreService
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $isManager = $user->isManager();
        $companyId = $isManager ? $user->company_id : null;

        if ($isManager && ! $companyId) {
            abort(403, 'Your account is designated as manager but has not been assigned to a company yet.');
        }

        // For manager, display Team CQ Sync report as the main dashboard
        if ($isManager) {
            $company = $user->company;
            $selectedSurveyId = $request->query('survey_id');
            $survey = $selectedSurveyId
                ? Survey::where('company_id', $companyId)->find($selectedSurveyId)
                : Survey::where('company_id', $companyId)->latest()->first();

            if (! $survey) {
                $survey = Survey::where('company_id', $companyId)->first();
            }

            if (! $survey) {
                abort(404, 'No surveys found for your organization.');
            }

            $insights = $this->scoreService->calculateGroupInsights($survey);
            $availableSurveys = Survey::where('company_id', $companyId)->orderBy('title')->get();

            return view('admin.surveys.team-sync-report', [
                'survey' => $survey,
                'company' => $company,
                'insights' => $insights,
                'availableSurveys' => $availableSurveys,
            ]);
        }

        // For admin, display Team CQ Sync report of the selected company
        $adminCompanyId = session('admin_selected_company_id');
        $adminCompany = $adminCompanyId ? Company::find($adminCompanyId) : Company::first();

        if ($adminCompany) {
            $selectedSurveyId = $request->query('survey_id');
            $survey = $selectedSurveyId
                ? Survey::where('company_id', $adminCompany->id)->find($selectedSurveyId)
                : Survey::where('company_id', $adminCompany->id)->latest()->first();

            if (! $survey) {
                $survey = Survey::where('company_id', $adminCompany->id)->first();
            }

            if ($survey) {
                $insights = $this->scoreService->calculateGroupInsights($survey);
                $availableSurveys = Survey::where('company_id', $adminCompany->id)->orderBy('title')->get();

                return view('admin.surveys.team-sync-report', [
                    'survey' => $survey,
                    'company' => $adminCompany,
                    'insights' => $insights,
                    'availableSurveys' => $availableSurveys,
                ]);
            }
        }

        return $this->renderOverviewDashboard($request);
    }

    public function surveyAssessment(Request $request): View
    {
        return $this->renderOverviewDashboard($request);
    }

    protected function renderOverviewDashboard(Request $request): View
    {
        $user = $request->user();
        $isManager = $user->isManager();
        $companyId = $isManager ? $user->company_id : session('admin_selected_company_id');

        if ($isManager && ! $companyId) {
            abort(403, 'Your account is designated as manager but has not been assigned to a company yet.');
        }

        $totalSurveys = Survey::when($companyId, fn ($q) => $q->where('company_id', $companyId))->count();
        $activeSurveys = Survey::when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->where('status', 'published')
            ->count();
        $totalParticipants = User::when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->where('role', 'participant')
            ->count();

        $assessmentQuery = Assessment::has('survey')
            ->when($companyId, fn ($q) => $q->whereHas('survey', fn ($sq) => $sq->where('company_id', $companyId)));

        $completedAssessments = (clone $assessmentQuery)->where('status', 'completed')->count();
        $pendingAssessments = (clone $assessmentQuery)->where('status', 'pending')->count();
        $inProgressAssessments = (clone $assessmentQuery)->where('status', 'in_progress')->count();
        $totalAssessments = (clone $assessmentQuery)->count();

        $overallCompletionRate = $totalAssessments > 0
            ? round(($completedAssessments / $totalAssessments) * 100, 1)
            : 0;

        $recentAssessments = (clone $assessmentQuery)
            ->with(['assessor', 'subject', 'survey'])
            ->latest('updated_at')
            ->take(10)
            ->get();

        $surveys = Survey::when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->withCount(['participants', 'assessments', 'questions'])
            ->latest()
            ->take(5)
            ->get();

        $company = $isManager ? $user->company : ($companyId ? Company::find($companyId) : null);

        return view('admin.dashboard', compact(
            'totalSurveys',
            'activeSurveys',
            'totalParticipants',
            'completedAssessments',
            'pendingAssessments',
            'inProgressAssessments',
            'totalAssessments',
            'overallCompletionRate',
            'recentAssessments',
            'surveys',
            'company'
        ));
    }
}
