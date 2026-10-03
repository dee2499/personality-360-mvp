<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSurveyRequest;
use App\Models\Company;
use App\Models\Question;
use App\Models\Survey;
use App\Models\User;
use App\Services\AssessmentGenerationService;
use App\Services\AssessmentScoreService;
use App\Services\ChangeQuotientQuestionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SurveyController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $companyFilter = $request->query('company');
        $companyId = $request->query('company_id');

        $surveysQuery = Survey::withCount(['questions', 'participants', 'assessments'])
            ->with(['creator', 'company'])
            ->latest();

        if ($request->filled('search')) {
            $searchTerm = trim((string) $search);
            $surveysQuery->where(function ($query) use ($searchTerm) {
                $query->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        if ($request->filled('company')) {
            $companyName = trim((string) $companyFilter);
            if (strtolower($companyName) === 'none' || strtolower($companyName) === 'unassigned') {
                $surveysQuery->whereNull('company_id');
            } else {
                $surveysQuery->whereHas('company', function ($query) use ($companyName) {
                    $query->where('name', 'like', "%{$companyName}%");
                });
            }
        } elseif ($request->filled('company_id')) {
            if ($companyId === 'none') {
                $surveysQuery->whereNull('company_id');
            } else {
                $surveysQuery->where('company_id', $companyId);
            }
        }

        $surveys = $surveysQuery->paginate(10)->withQueryString();
        $companies = Company::withCount('surveys')->orderBy('name')->get();
        $totalSurveysCount = Survey::count();
        $unassignedSurveysCount = Survey::whereNull('company_id')->count();

        $selectedCompany = null;
        if ($request->filled('company_id') && $companyId !== 'none') {
            $selectedCompany = $companies->firstWhere('id', (int) $companyId);
        } elseif ($request->filled('company')) {
            $selectedCompany = $companies->first(fn ($c) => strcasecmp($c->name, trim((string) $companyFilter)) === 0)
                ?? (object) ['name' => trim((string) $companyFilter), 'id' => null];
        }

        return view('admin.surveys.index', compact(
            'surveys',
            'companies',
            'search',
            'companyFilter',
            'companyId',
            'selectedCompany',
            'totalSurveysCount',
            'unassignedSurveysCount'
        ));
    }

    public function create(Request $request): View
    {
        $companies = Company::orderBy('name')->get();
        $selectedCompanyId = $request->query('company_id');
        $defaultQuestions = ChangeQuotientQuestionService::getDefaultQuestions();

        return view('admin.surveys.create', compact('defaultQuestions', 'companies', 'selectedCompanyId'));
    }

    public function store(StoreSurveyRequest $request): RedirectResponse
    {
        $companyId = $request->input('company_id');

        $survey = Survey::create([
            'company_id' => $companyId,
            'title' => $request->string('title'),
            'description' => $request->string('description'),
            'status' => 'published',
            'published_at' => now(),
            'created_by' => $request->user()->id,
        ]);

        $questions = $request->input('questions', []);
        $defaults = ChangeQuotientQuestionService::getDefaultQuestions();

        if (empty($questions)) {
            app(ChangeQuotientQuestionService::class)->seedForSurvey($survey);
        } else {
            $order = 1;
            foreach ($questions as $index => $qInput) {
                $text = is_array($qInput) ? ($qInput['question_text'] ?? '') : (string) $qInput;
                if (! empty(trim($text))) {
                    $default = $defaults[$index] ?? null;
                    Question::create([
                        'survey_id' => $survey->id,
                        'question_text' => trim($text),
                        'peer_question_text' => is_array($qInput) ? ($qInput['peer_question_text'] ?? $default['peer_question_text'] ?? null) : ($default['peer_question_text'] ?? null),
                        'dimension' => is_array($qInput) ? ($qInput['dimension'] ?? $default['dimension'] ?? null) : ($default['dimension'] ?? null),
                        'type' => is_array($qInput) ? ($qInput['type'] ?? $default['type'] ?? 'individual') : ($default['type'] ?? ($order > 11 ? 'group_sync' : 'individual')),
                        'min_score_description' => is_array($qInput) ? ($qInput['min_score_description'] ?? $default['min_score_description'] ?? '1') : ($default['min_score_description'] ?? '1'),
                        'max_score_description' => is_array($qInput) ? ($qInput['max_score_description'] ?? $default['max_score_description'] ?? '10') : ($default['max_score_description'] ?? '10'),
                        'sort_order' => $order++,
                        'is_active' => true,
                    ]);
                }
            }
        }

        // If survey belongs to a company, automatically enroll all company employees into the cohort
        if ($companyId) {
            $employeeIds = User::where('company_id', $companyId)->where('role', '!=', 'admin')->pluck('id');
            if ($employeeIds->isNotEmpty()) {
                $survey->participants()->sync($employeeIds);
                app(AssessmentGenerationService::class)->generateForSurvey($survey);
            }
        }

        return redirect()->route('admin.surveys.show', $survey)
            ->with('success', "Survey '{$survey->title}' created and published successfully.");
    }

    public function show(Survey $survey): View
    {
        $survey->load([
            'creator',
            'questions' => fn ($q) => $q->orderBy('sort_order'),
            'participants',
            'assessments.assessor',
            'assessments.subject',
        ]);

        $allParticipants = User::where('role', 'participant')->orderBy('name')->get();

        $completedAssessmentsCount = $survey->assessments->where('status', 'completed')->count();
        $totalAssessmentsCount = $survey->assessments->count();

        return view('admin.surveys.show', compact(
            'survey',
            'allParticipants',
            'completedAssessmentsCount',
            'totalAssessmentsCount'
        ));
    }

    public function edit(Survey $survey): View
    {
        return view('admin.surveys.edit', compact('survey'));
    }

    public function update(Request $request, Survey $survey): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $survey->update($validated);

        return redirect()->route('admin.surveys.show', $survey)
            ->with('success', 'Survey details updated successfully.');
    }

    public function destroy(Survey $survey): RedirectResponse
    {
        $title = $survey->title;
        $survey->delete();

        return redirect()->route('admin.surveys.index')
            ->with('success', "Survey '{$title}' deleted successfully.");
    }

    public function publish(Survey $survey, AssessmentGenerationService $generationService): RedirectResponse
    {
        if ($survey->questions()->count() === 0) {
            return back()->with('error', 'Cannot publish a survey with no questions. Please add questions first.');
        }

        if ($survey->participants()->count() < 1) {
            return back()->with('error', 'Please assign at least one participant before publishing.');
        }

        $survey->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        $generated = $generationService->generateForSurvey($survey);

        return back()->with(
            'success',
            "Survey published! Automatically generated {$generated} assessment assignment(s)."
        );
    }

    public function unpublish(Survey $survey): RedirectResponse
    {
        $survey->update([
            'status' => 'draft',
        ]);

        return back()->with('success', 'Survey unpublished and set to draft.');
    }

    /**
     * Display group analytics, intent-level recommendations, and leadership sign-off panel.
     * Note: Zero individual names are exposed in this report.
     */
    public function groupInsights(Survey $survey, AssessmentScoreService $scoreService): View
    {
        $insights = $scoreService->calculateGroupInsights($survey);

        return view('admin.surveys.group-insights', compact('survey', 'insights'));
    }

    /**
     * Record leadership sign-off for the survey group action plan.
     */
    public function signOff(Request $request, Survey $survey): RedirectResponse
    {
        $validated = $request->validate([
            'sign_off_lead' => ['required', 'string', 'max:255'],
            'sign_off_status' => ['required', 'in:approved,pending,needs_review'],
            'sign_off_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $survey->update([
            'sign_off_lead' => $validated['sign_off_lead'],
            'sign_off_status' => $validated['sign_off_status'],
            'sign_off_notes' => $validated['sign_off_notes'] ?? null,
            'signed_off_at' => now(),
        ]);

        return redirect()->route('admin.surveys.group-insights', $survey)
            ->with('success', 'Leadership sign-off has been successfully recorded for this survey.');
    }
}
