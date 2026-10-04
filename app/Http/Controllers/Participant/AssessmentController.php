<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Participant\SubmitAssessmentRequest;
use App\Models\Assessment;
use App\Models\AssessmentAnswer;
use App\Models\GroupSyncAnswer;
use App\Models\Survey;
use App\Services\AssessmentCategoryService;
use App\Services\AssessmentGenerationService;
use App\Services\AssessmentScoreService;
use App\Services\ChangeQuotientQuestionService;
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

        // 1. Identify all surveys assigned to the user or belonging to their company
        $surveys = Survey::query()
            ->where(function ($q) use ($user) {
                $q->whereHas('participants', fn ($sq) => $sq->where('users.id', $user->id))
                    ->orWhereHas('assessments', fn ($sq) => $sq->where('assessor_id', $user->id));
                if ($user->company_id) {
                    $q->orWhere('company_id', $user->company_id);
                }
            })
            ->with(['questions', 'participants', 'company'])
            ->get();

        // 2. Auto-enroll user into their company surveys and sync assessments
        foreach ($surveys as $s) {
            if (! $s->participants()->where('users.id', $user->id)->exists()) {
                $s->participants()->syncWithoutDetaching([$user->id]);
            }
            app(AssessmentGenerationService::class)->generateForSurvey($s);
        }

        // 3. Load all given assessments
        $assessments = Assessment::has('survey')
            ->with(['subject', 'survey'])
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

        // Group surveys for the dashboard view
        $surveyGroups = $surveys->map(function ($survey) use ($user) {
            $surveyAssessments = Assessment::where('survey_id', $survey->id)
                ->where('assessor_id', $user->id)
                ->with('subject')
                ->get();

            $self = $surveyAssessments->first(fn ($a) => $a->isSelfAssessment());
            $peers = $surveyAssessments->filter(fn ($a) => ! $a->isSelfAssessment());
            $isCompleted = $surveyAssessments->isNotEmpty() && $surveyAssessments->every(fn ($a) => $a->isCompleted());
            $completed = $surveyAssessments->where('status', 'completed')->count();
            $total = $surveyAssessments->count();

            // Three Score Meters (Normalised, Self, Peer)
            $dualMeters = $this->scoreService->calculateSelfAndPeerScores($user, $survey);
            $selfPct = (float) ($dualMeters['self']['percentage'] ?? 0.0);
            $peerPct = (float) ($dualMeters['peer']['percentage'] ?? 0.0);
            $hasBoth = $dualMeters['self']['is_completed'] && (($dualMeters['peer']['completed_count'] ?? 0) > 0);

            if ($hasBoth) {
                $normPct = round(($selfPct + $peerPct) / 2, 2);
            } elseif ($dualMeters['self']['is_completed']) {
                $normPct = $selfPct;
            } elseif (($dualMeters['peer']['completed_count'] ?? 0) > 0) {
                $normPct = $peerPct;
            } else {
                $normPct = 0.0;
            }

            $normCategory = $this->categoryService->getCategory($normPct);
            $normalised = [
                'name' => 'CQ 3 (Normalised)',
                'label' => 'Calibrated Change Quotient',
                'percentage' => $normPct,
                'score' => round($normPct / 10, 1),
                'category' => $normCategory,
                'emoji' => $this->categoryService->getEmoji($normCategory),
                'color' => $this->categoryService->getColorHex($normCategory),
                'badge' => $this->categoryService->getBadgeClass($normCategory),
                'gap' => $dualMeters['comparison']['gap'] ?? 0.0,
            ];

            // Subjects list (self first, then colleagues)
            $cohortMembers = $survey->participants()
                ->get()
                ->sortBy(fn ($s) => $s->id === $user->id ? 0 : 1)
                ->values();

            return [
                'survey' => $survey,
                'selfAssessment' => $self,
                'peerAssessments' => $peers,
                'cohortMembers' => $cohortMembers,
                'isCompleted' => $isCompleted,
                'completedCount' => $completed,
                'totalCount' => $total,
                'normalised' => $normalised,
                'self' => $dualMeters['self'],
                'peer' => $dualMeters['peer'],
                'comparison' => $dualMeters['comparison'],
            ];
        });

        // 4. Handle survey selection from the top
        $selectedSurveyId = $request->query('survey_id');
        $selectedGroup = $selectedSurveyId
            ? $surveyGroups->first(fn ($g) => $g['survey']->id == $selectedSurveyId)
            : $surveyGroups->first();

        $selectedSurvey = $selectedGroup ? $selectedGroup['survey'] : null;

        // 5. Detailed matrix breakdown for selected survey
        $questionsBreakdown = [];
        $givenRatingsBreakdown = [];

        if ($selectedSurvey) {
            $questions = $selectedSurvey->questions()->where('is_active', true)->orderBy('sort_order')->get();
            $selfAssessmentRecord = Assessment::where('survey_id', $selectedSurvey->id)
                ->where('assessor_id', $user->id)
                ->where('subject_id', $user->id)
                ->first();

            $peerAssessmentIds = Assessment::where('survey_id', $selectedSurvey->id)
                ->where('assessor_id', '!=', $user->id)
                ->where('subject_id', $user->id)
                ->where('status', 'completed')
                ->pluck('id');

            foreach ($questions as $q) {
                $selfScore = $selfAssessmentRecord
                    ? AssessmentAnswer::where('assessment_id', $selfAssessmentRecord->id)->where('question_id', $q->id)->value('score')
                    : null;

                $peerAnswers = AssessmentAnswer::whereIn('assessment_id', $peerAssessmentIds)
                    ->where('question_id', $q->id)
                    ->pluck('score');

                $peerAvg = $peerAnswers->isNotEmpty() ? round($peerAnswers->avg(), 1) : null;
                $gap = ($selfScore !== null && $peerAvg !== null) ? round($selfScore - $peerAvg, 1) : null;

                $questionsBreakdown[] = [
                    'question' => $q,
                    'self_score' => $selfScore,
                    'peer_avg' => $peerAvg,
                    'peer_count' => $peerAnswers->count(),
                    'gap' => $gap,
                ];
            }

            // Given ratings for each cohort member
            $cohort = $selectedGroup['cohortMembers'];
            foreach ($cohort as $member) {
                $assessment = Assessment::where('survey_id', $selectedSurvey->id)
                    ->where('assessor_id', $user->id)
                    ->where('subject_id', $member->id)
                    ->first();

                $givenRatingsBreakdown[] = [
                    'member' => $member,
                    'is_self' => ($member->id === $user->id),
                    'assessment' => $assessment,
                    'is_completed' => $assessment?->isCompleted() ?? false,
                    'total_score' => $assessment?->total_score ?? 0,
                    'percentage' => $assessment?->percentage ?? 0.0,
                    'category' => $assessment?->category ?? 'Pending',
                ];
            }
        }

        return view('participant.assessments.index', compact(
            'assessments',
            'selfAssessment',
            'peerAssessments',
            'surveyGroups',
            'selectedGroup',
            'selectedSurvey',
            'questionsBreakdown',
            'givenRatingsBreakdown',
            'totalAssigned',
            'completedCount',
            'pendingCount',
            'inProgressCount',
            'completionRate'
        ));
    }

    /**
     * Show the assessment taking interface (redirects directly to 11-question cohort wizard).
     */
    public function show(Request $request, Assessment $assessment): RedirectResponse
    {
        if ($assessment->assessor_id !== $request->user()->id && ! $request->user()->isAdmin()) {
            abort(403, 'You are not authorized to view this assessment.');
        }

        return redirect()->route('participant.surveys.take', $assessment->survey_id);
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
    /**
     * Show the question-first multi-subject assessment slider wizard.
     */
    public function takeSurvey(Request $request, Survey $survey): View|RedirectResponse
    {
        $user = $request->user();

        // 1. If survey belongs to user's company, auto-enroll user if not yet attached
        if ($survey->company_id && $user->company_id === $survey->company_id && ! $survey->participants()->where('users.id', $user->id)->exists()) {
            $survey->participants()->syncWithoutDetaching([$user->id]);
        }

        $isParticipant = $survey->participants()->where('users.id', $user->id)->exists();
        $hasAssessments = Assessment::where('survey_id', $survey->id)->where('assessor_id', $user->id)->exists();

        if (! $isParticipant && ! $hasAssessments && ! $user->isAdmin()) {
            abort(403, 'You are not assigned to this survey cohort.');
        }

        // 2. Ensure survey has default questions if empty
        if ($survey->questions()->count() === 0) {
            app(ChangeQuotientQuestionService::class)->seedForSurvey($survey);
        }

        // 3. Always ensure assessments are synchronized for all participants
        app(AssessmentGenerationService::class)->generateForSurvey($survey);

        $assessments = Assessment::with(['subject', 'answers'])
            ->where('survey_id', $survey->id)
            ->where('assessor_id', $user->id)
            ->get();

        // 4. Questions: Split into 11 Individual CQ questions and 3 Group Sync questions
        $allQuestions = $survey->questions()->where('is_active', true)->orderBy('sort_order')->get();
        $individualQuestions = $allQuestions->where('type', 'individual')->values();
        if ($individualQuestions->isEmpty()) {
            $individualQuestions = $allQuestions->take(11)->values();
        }

        $groupSyncQuestions = $allQuestions->where('type', 'group_sync')->values();
        if ($groupSyncQuestions->isEmpty()) {
            $groupSyncQuestions = $allQuestions->slice(11)->values();
        }

        // 5. Subjects list: Current user (Self) STRICTLY FIRST (Index 0), followed by other colleagues
        $subjects = $survey->participants()
            ->get()
            ->sortBy(fn ($s) => $s->id === $user->id ? 0 : 1)
            ->values();

        if ($subjects->isEmpty()) {
            $subjects = $assessments->map(fn ($a) => $a->subject)->filter()->unique('id')->sortBy(fn ($s) => $s->id === $user->id ? 0 : 1)->values();
        }

        // Existing scores map: [subject_id => [question_id => score]]
        $existingScores = [];
        foreach ($assessments as $assessment) {
            foreach ($assessment->answers as $ans) {
                $existingScores[$assessment->subject_id][$ans->question_id] = $ans->score;
            }
        }

        // Existing Group Sync scores: [question_id => score]
        $existingGroupSyncScores = GroupSyncAnswer::where('survey_id', $survey->id)
            ->where('user_id', $user->id)
            ->pluck('score', 'question_id')
            ->all();

        $isAllCompleted = $assessments->isNotEmpty()
            && $assessments->every(fn ($a) => $a->isCompleted())
            && ($groupSyncQuestions->isEmpty() || count($existingGroupSyncScores) >= $groupSyncQuestions->count());

        return view('participant.surveys.take', [
            'survey' => $survey,
            'questions' => $individualQuestions,
            'individualQuestions' => $individualQuestions,
            'groupSyncQuestions' => $groupSyncQuestions,
            'allQuestions' => $allQuestions,
            'subjects' => $subjects,
            'existingScores' => $existingScores,
            'existingGroupSyncScores' => $existingGroupSyncScores,
            'isAllCompleted' => $isAllCompleted,
            'user' => $user,
        ]);
    }

    /**
     * Submit all answers across all questions for the entire cohort and group sync.
     */
    public function submitSurveyMatrix(Request $request, Survey $survey): RedirectResponse
    {
        $user = $request->user();

        $answersData = $request->input('answers', []); // [subject_id => [question_id => score]]
        $groupSyncData = $request->input('group_sync', []); // [question_id => score]

        if (empty($answersData) && empty($groupSyncData)) {
            return back()->with('error', 'Please provide ratings before submitting.');
        }

        DB::transaction(function () use ($survey, $user, $answersData, $groupSyncData) {
            // 1. Save Individual CQ ratings
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

            // 2. Save Group Sync ratings (My Views - About our Group)
            foreach ($groupSyncData as $questionId => $score) {
                if ($score !== null && $score !== '') {
                    GroupSyncAnswer::updateOrCreate(
                        [
                            'survey_id' => $survey->id,
                            'user_id' => $user->id,
                            'question_id' => $questionId,
                        ],
                        [
                            'score' => (int) $score,
                        ]
                    );
                }
            }
        });

        return redirect()->route('participant.assessments.report', $survey)
            ->with('success', "Great job! All evaluations and Group Sync ratings for '{$survey->title}' have been successfully submitted.");
    }

    /**
     * Entry point for the "CQ Report" user tab.
     * Automatically resolves the requested or active survey for the logged-in user.
     */
    public function showReport(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $surveyId = $request->query('survey_id');

        $survey = null;
        if ($surveyId) {
            $survey = Survey::find($surveyId);
        }

        if (! $survey) {
            $survey = $user->surveys()->where('status', 'published')->latest('published_at')->first()
                ?? Survey::whereHas('assessments', function ($q) use ($user) {
                    $q->where('subject_id', $user->id)->orWhere('assessor_id', $user->id);
                })->latest()->first()
                ?? Survey::where('status', 'published')->latest()->first();
        }

        if (! $survey) {
            return redirect()->route('participant.assessments.index')
                ->with('info', 'No active surveys assigned to generate a CQ Report.');
        }

        return $this->report($request, $survey);
    }

    /**
     * Display the confidential individual Change Quotient (CQ 1-3, CQ Sync) report.
     * Strictly confidential for the authenticated user's self-introspection.
     */
    public function report(Request $request, Survey $survey): View
    {
        $user = $request->user();

        // Check if user is a participant or has an assessment record in this survey
        $isCohortMember = $survey->participants()->where('users.id', $user->id)->exists()
            || Assessment::where('survey_id', $survey->id)->where(function ($q) use ($user) {
                $q->where('subject_id', $user->id)->orWhere('assessor_id', $user->id);
            })->exists();

        if (! $isCohortMember && ! $user->isAdmin()) {
            abort(403, 'Unauthorized. This individual CQ report is confidential.');
        }

        $cq = $this->scoreService->calculateChangeQuotientReport($user, $survey);

        $availableSurveys = $user->surveys()->where('status', 'published')->get();
        if ($availableSurveys->isEmpty()) {
            $availableSurveys = Survey::whereHas('assessments', function ($q) use ($user) {
                $q->where('subject_id', $user->id)->orWhere('assessor_id', $user->id);
            })->get();
        }

        return view('participant.assessments.report', [
            'survey' => $survey,
            'user' => $user,
            'cq' => $cq,
            'availableSurveys' => $availableSurveys,
        ]);
    }

    /**
     * Display the team-level anonymous group insights and recommendations for the survey cohort.
     * Public to cohort members with ZERO individual names displayed.
     */
    public function groupInsights(Request $request, Survey $survey): View
    {
        $user = $request->user();

        $isCohortMember = $survey->participants()->where('users.id', $user->id)->exists()
            || Assessment::where('survey_id', $survey->id)->where(function ($q) use ($user) {
                $q->where('subject_id', $user->id)->orWhere('assessor_id', $user->id);
            })->exists();

        if (! $isCohortMember && ! $user->isAdmin()) {
            abort(403, 'Unauthorized. You must belong to this survey cohort to view team insights.');
        }

        $insights = $this->scoreService->calculateGroupInsights($survey);

        return view('participant.assessments.group-insights', [
            'survey' => $survey,
            'insights' => $insights,
        ]);
    }
}
