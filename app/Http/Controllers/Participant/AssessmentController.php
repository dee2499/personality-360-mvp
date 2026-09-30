<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Participant\SubmitAssessmentRequest;
use App\Models\Assessment;
use App\Models\AssessmentAnswer;
use App\Services\AssessmentCategoryService;
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

        return view('participant.assessments.index', compact(
            'assessments',
            'selfAssessment',
            'peerAssessments',
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
}
