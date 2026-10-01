<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Participant\SubmitAssessmentRequest;
use App\Models\Assessment;
use App\Models\AssessmentAnswer;
use App\Models\Survey;
use App\Services\AssessmentCategoryService;
use App\Services\AssessmentGenerationService;
use App\Services\AssessmentScoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AssessmentController extends Controller
{
    public function __construct(
        protected AssessmentScoreService $scoreService,
        protected AssessmentCategoryService $categoryService
    ) {}

    /**
     * Display the participant dashboard with assigned assessments.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $assessments = Assessment::with(['subject', 'survey'])
            ->where('assessor_id', $user->id)
            ->get();

        $totalAssigned = $assessments->count();
        $completedCount = $assessments->where('status', 'completed')->count();
        $pendingCount = $assessments->where('status', 'pending')->count();
        $inProgressCount = $assessments->where('status', 'in_progress')->count();

        $completionRate = $totalAssigned > 0
            ? round(($completedCount / $totalAssigned) * 100, 1)
            : 0;

        $selfAssessment = $assessments->first(fn ($a) => $a->isSelfAssessment());
        $peerAssessments = $assessments->filter(fn ($a) => ! $a->isSelfAssessment());

        $surveyGroups = $assessments->groupBy('survey_id')->map(function ($items) {
            $survey = $items->first()->survey;
            $self = $items->first(fn ($a) => $a->isSelfAssessment());
            $peers = $items->filter(fn ($a) => ! $a->isSelfAssessment());
            $isCompleted = $items->every(fn ($a) => $a->isCompleted());
            $completed = $items->where('status', 'completed')->count();
            $total = $items->count();

            return [
                'survey' => $survey,
                'selfAssessment' => $self,
                'peerAssessments' => $peers,
                'isCompleted' => $isCompleted,
                'completedCount' => $completed,
                'totalCount' => $total,
            ];
        })->values();

        return view('participant.assessments.index', compact(
            'assessments',
            'selfAssessment',
            'peerAssessments',
            'surveyGroups',
            'totalAssigned',
            'completedCount',
            'pendingCount',
            'inProgressCount',
            'completionRate'
        ));
    }

    /**
     * Show the assessment taking interface (or completed view).
     */
    public function show(Request $request, Assessment $assessment): View
    {
        // Enforce authorization: user can only view their own given assessments
        if ($assessment->assessor_id !== $request->user()->id && ! $request->user()->isAdmin()) {
            abort(403, 'You are not authorized to view this assessment.');
        }

        // If pending, mark as in_progress
        if ($assessment->isPending()) {
            $assessment->update([
                'status' => 'in_progress',
                'started_at' => now(),
            ]);
        }

        $assessment->load([
            'subject',
            'survey.questions' => fn ($q) => $q->orderBy('sort_order'),
            'answers',
        ]);

        $answers = $assessment->answers->pluck('score', 'question_id')->toArray();

        $isReadOnly = $assessment->isCompleted();

        $categoryEmoji = $assessment->category ? $this->categoryService->getEmoji($assessment->category) : null;
        $categoryBadge = $assessment->category ? $this->categoryService->getBadgeClass($assessment->category) : null;

        return view('participant.assessments.show', compact(
            'assessment',
            'answers',
            'isReadOnly',
            'categoryEmoji',
            'categoryBadge'
        ));
    }

    /**
     * Submit all answers for the assessment.
     */
    public function submit(SubmitAssessmentRequest $request, Assessment $assessment): RedirectResponse
    {
        if ($assessment->isCompleted()) {
            return redirect()->route('participant.assessments.show', $assessment)
                ->with('info', 'This assessment has already been completed and submitted.');
        }

        $validated = $request->validated();
        $answersData = $validated['answers']; // [question_id => score]

        DB::transaction(function () use ($assessment, $answersData) {
            foreach ($answersData as $questionId => $score) {
                AssessmentAnswer::updateOrCreate(
                    [
                        'assessment_id' => $assessment->id,
                        'question_id' => $questionId,
                    ],
                    [
                        'score' => (int) $score,
                    ]
                );
            }

            $assessment->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // Automatically compute total score, percentage, and category
            $this->scoreService->calculateAssessmentScore($assessment);
        });

        $subjectName = $assessment->isSelfAssessment() ? 'your self-assessment' : "assessment of {$assessment->subject->name}";

        return redirect()->route('participant.assessments.index')
            ->with('success', "Great job! Successfully submitted {$subjectName}.");
    }

    /**
     * Show the question-first multi-subject assessment slider wizard.
     */
    public function takeSurvey(Request $request, Survey $survey): View|RedirectResponse
    {
        $user = $request->user();

        // Check if user is participant or has assessments in this survey
        $isParticipant = $survey->participants()->where('users.id', $user->id)->exists();
        $hasAssessments = Assessment::where('survey_id', $survey->id)->where('assessor_id', $user->id)->exists();

        if (! $isParticipant && ! $hasAssessments && ! $user->isAdmin()) {
            abort(403, 'You are not assigned to this survey cohort.');
        }

        // Ensure assessments are generated for this survey
        $assessments = Assessment::with(['subject', 'answers'])
            ->where('survey_id', $survey->id)
            ->where('assessor_id', $user->id)
            ->get();

        if ($assessments->isEmpty() && $isParticipant) {
            app(AssessmentGenerationService::class)->generateForSurvey($survey);
            $assessments = Assessment::with(['subject', 'answers'])
                ->where('survey_id', $survey->id)
                ->where('assessor_id', $user->id)
                ->get();
        }

        // Questions sorted
        $questions = $survey->questions()->where('is_active', true)->orderBy('sort_order')->get();

        // Subjects list: Put current user (Self) first, followed by other colleagues
        $subjects = $assessments->map(fn ($a) => $a->subject)
            ->filter()
            ->unique('id')
            ->sortBy(fn ($s) => $s->id === $user->id ? 0 : 1)
            ->values();

        // Existing scores map: [subject_id => [question_id => score]]
        $existingScores = [];
        foreach ($assessments as $assessment) {
            foreach ($assessment->answers as $ans) {
                $existingScores[$assessment->subject_id][$ans->question_id] = $ans->score;
            }
        }

        $isAllCompleted = $assessments->isNotEmpty() && $assessments->every(fn ($a) => $a->isCompleted());

        return view('participant.surveys.take', compact(
            'survey',
            'questions',
            'subjects',
            'existingScores',
            'isAllCompleted',
            'user'
        ));
    }

    /**
     * Submit all answers across all questions for the entire cohort.
     */
    public function submitSurveyMatrix(Request $request, Survey $survey): RedirectResponse
    {
        $user = $request->user();

        $answersData = $request->input('answers', []); // [subject_id => [question_id => score]]

        if (empty($answersData)) {
            return back()->with('error', 'Please provide ratings before submitting.');
        }

        DB::transaction(function () use ($survey, $user, $answersData) {
            foreach ($answersData as $subjectId => $questionScores) {
                $assessment = Assessment::firstOrCreate(
                    [
                        'survey_id' => $survey->id,
                        'assessor_id' => $user->id,
                        'subject_id' => $subjectId,
                    ],
                    [
                        'status' => 'in_progress',
                        'started_at' => now(),
                    ]
                );

                foreach ($questionScores as $questionId => $score) {
                    if ($score !== null && $score !== '') {
                        AssessmentAnswer::updateOrCreate(
                            [
                                'assessment_id' => $assessment->id,
                                'question_id' => $questionId,
                            ],
                            [
                                'score' => (int) $score,
                            ]
                        );
                    }
                }

                $assessment->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);

                $this->scoreService->calculateAssessmentScore($assessment);
            }
        });

        return redirect()->route('participant.assessments.index')
            ->with('success', "Great job! All evaluations for '{$survey->title}' have been successfully submitted.");
    }
}
