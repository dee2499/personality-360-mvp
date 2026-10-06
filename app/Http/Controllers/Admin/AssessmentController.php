<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Company;
use App\Models\Survey;
use App\Services\AssessmentCategoryService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssessmentController extends Controller
{
    public function __construct(
        protected AssessmentCategoryService $categoryService
    ) {}

    public function index(Request $request): View
    {
        $status = $request->query('status');
        $surveyId = $request->query('survey_id');
        $companyId = $request->query('company_id');
        $companyFilter = $request->query('company');

        $isManager = $request->user()->isManager();
        $managerCompanyId = $isManager ? $request->user()->company_id : null;

        if ($isManager && ! $managerCompanyId) {
            abort(403, 'Your account is designated as manager but has not been assigned to a company yet.');
        }

        $query = Assessment::has('survey')
            ->with(['assessor', 'subject', 'survey.company'])
            ->latest('updated_at');

        if ($isManager) {
            $query->whereHas('survey', fn ($q) => $q->where('company_id', $managerCompanyId));
        } else {
            if ($companyId) {
                if ($companyId === 'none') {
                    $query->whereHas('survey', fn ($q) => $q->whereNull('company_id'));
                } else {
                    $query->whereHas('survey', fn ($q) => $q->where('company_id', $companyId));
                }
            } elseif ($companyFilter) {
                $compName = trim((string) $companyFilter);
                if (strtolower($compName) === 'none' || strtolower($compName) === 'unassigned') {
                    $query->whereHas('survey', fn ($q) => $q->whereNull('company_id'));
                } else {
                    $query->whereHas('survey.company', function ($q) use ($compName) {
                        $q->where('name', 'like', "%{$compName}%");
                    });
                }
            }
        }

        if ($status && in_array($status, ['pending', 'in_progress', 'completed'])) {
            $query->where('status', $status);
        }

        if ($surveyId) {
            $query->where('survey_id', $surveyId);
        }

        $assessments = $query->paginate(15)->withQueryString();
        $companies = $isManager
            ? Company::where('id', $managerCompanyId)->get()
            : Company::orderBy('name')->get();
        $surveysQuery = Survey::with('company')->orderBy('title');

        if ($isManager) {
            $surveysQuery->where('company_id', $managerCompanyId);
        } elseif ($companyId && $companyId !== 'none') {
            $surveysQuery->where('company_id', $companyId);
        } elseif ($companyFilter) {
            $compName = trim((string) $companyFilter);
            if (strtolower($compName) === 'none' || strtolower($compName) === 'unassigned') {
                $surveysQuery->whereNull('company_id');
            } else {
                $surveysQuery->whereHas('company', fn ($q) => $q->where('name', 'like', "%{$compName}%"));
            }
        }
        $surveys = $surveysQuery->get();

        $selectedCompany = null;
        if ($isManager) {
            $selectedCompany = $companies->first();
        } elseif ($companyId && $companyId !== 'none') {
            $selectedCompany = $companies->firstWhere('id', (int) $companyId);
        } elseif ($companyFilter) {
            $selectedCompany = $companies->first(fn ($c) => strcasecmp($c->name, trim((string) $companyFilter)) === 0)
                ?? (object) ['name' => trim((string) $companyFilter), 'id' => null];
        }

        return view('admin.assessments.index', compact(
            'assessments',
            'surveys',
            'companies',
            'status',
            'surveyId',
            'companyId',
            'companyFilter',
            'selectedCompany'
        ));
    }

    public function show(Assessment $assessment): View
    {
        if (! $assessment->survey) {
            abort(404, 'The survey for this assessment no longer exists.');
        }

        if (request()->user()->isManager() && $assessment->survey?->company_id !== request()->user()->company_id) {
            abort(403, 'You are not authorized to view assessments outside your company.');
        }

        $assessment->load([
            'assessor',
            'subject',
            'survey.questions' => fn ($q) => $q->orderBy('sort_order'),
            'answers.question',
        ]);

        $answersByQuestionId = $assessment->answers->keyBy('question_id');

        $questions = $assessment->survey?->questions ?? collect();
        $questionsWithAnswers = $questions->map(function ($question) use ($answersByQuestionId) {
            $answer = $answersByQuestionId->get($question->id);

            return [
                'question' => $question,
                'score' => $answer?->score,
            ];
        });

        $categoryEmoji = $assessment->category ? $this->categoryService->getEmoji($assessment->category) : null;
        $categoryBadge = $assessment->category ? $this->categoryService->getBadgeClass($assessment->category) : null;

        return view('admin.assessments.show', compact(
            'assessment',
            'questionsWithAnswers',
            'categoryEmoji',
            'categoryBadge'
        ));
    }
}
