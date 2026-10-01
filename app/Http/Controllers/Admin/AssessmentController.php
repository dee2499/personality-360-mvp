<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
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

        $query = Assessment::has('survey')
            ->with(['assessor', 'subject', 'survey'])
            ->latest('updated_at');

        if ($status && in_array($status, ['pending', 'in_progress', 'completed'])) {
            $query->where('status', $status);
        }

        if ($surveyId) {
            $query->where('survey_id', $surveyId);
        }

        $assessments = $query->paginate(15)->withQueryString();
        $surveys = Survey::all();

        return view('admin.assessments.index', compact('assessments', 'surveys', 'status', 'surveyId'));
    }

    public function show(Assessment $assessment): View
    {
        if (! $assessment->survey) {
            abort(404, 'The survey for this assessment no longer exists.');
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
