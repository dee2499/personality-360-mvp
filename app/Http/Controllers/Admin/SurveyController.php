<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSurveyRequest;
use App\Models\Company;
use App\Models\Question;
use App\Models\Survey;
use App\Models\User;
use App\Services\AssessmentGenerationService;
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

        $defaultQuestions = [
            'How effectively does this person communicate with others?',
            'How well does this person work in a team?',
            'How confidently does this person make decisions?',
            'How adaptable is this person to change?',
            'How well does this person handle responsibility?',
            'How effectively does this person solve problems?',
            'How well does this person listen to others?',
            'How effectively does this person manage conflict?',
            'How consistently does this person demonstrate empathy?',
            'How effectively does this person take initiative?',
            'How reliable is this person when working toward goals?',
        ];

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
        $order = 1;
        foreach ($questions as $questionText) {
            if (! empty(trim((string) $questionText))) {
                Question::create([
                    'survey_id' => $survey->id,
                    'question_text' => trim((string) $questionText),
                    'sort_order' => $order++,
                    'is_active' => true,
                ]);
            }
        }

        // If survey belongs to a company, automatically enroll all company employees into the cohort
        if ($companyId) {
            $employeeIds = User::where('company_id', $companyId)->pluck('id');
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
}
