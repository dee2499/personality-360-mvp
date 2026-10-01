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
    public function index(): View
    {
        $surveys = Survey::withCount(['questions', 'participants', 'assessments'])
            ->with(['creator', 'company'])
            ->latest()
            ->paginate(10);

        return view('admin.surveys.index', compact('surveys'));
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
        $survey = Survey::create([
            'company_id' => $request->input('company_id'),
            'title' => $request->string('title'),
            'description' => $request->string('description'),
            'status' => 'draft',
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

        return redirect()->route('admin.surveys.show', $survey)
            ->with('success', "Survey '{$survey->title}' created successfully.");
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
