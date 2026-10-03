<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\Company;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class AssessmentScoreService
{
    public function __construct(
        protected AssessmentCategoryService $categoryService
    ) {}

    /**
     * Calculate score, percentage, and category for a single assessment.
     *
     * @return array{total_score: int, max_score: int, percentage: float, category: string}
     */
    public function calculateAssessmentScore(Assessment $assessment): array
    {
        $answers = $assessment->answers()->get();
        $totalScore = (int) $answers->sum('score');

        // Dynamic max score based on survey question count or answers count (each rated 1-10)
        $questionCount = $assessment->survey?->questions()->count() ?? $answers->count();
        $maxScore = max(10, $questionCount * 10);

        $percentage = $this->calculatePercentage($totalScore, $maxScore);
        $category = $this->categoryService->getCategory($percentage);

        $assessment->update([
            'total_score' => $totalScore,
            'max_score' => $maxScore,
            'percentage' => $percentage,
            'category' => $category,
        ]);

        return [
            'total_score' => $totalScore,
            'max_score' => $maxScore,
            'percentage' => $percentage,
            'category' => $category,
        ];
    }

    /**
     * Calculate percentage rounded to 2 decimal places.
     */
    public function calculatePercentage(int|float $totalScore, int|float $maxScore): float
    {
        if ($maxScore <= 0) {
            return 0.0;
        }

        return round(($totalScore / $maxScore) * 100, 2);
    }

    /**
     * Calculate combined score for a subject across completed assessments.
     *
     * @return array{
     *     completed_count: int,
     *     total_count: int,
     *     completion_rate: float,
     *     combined_score: int,
     *     combined_max_score: int,
     *     percentage: float,
     *     category: string,
     *     category_emoji: string,
     *     category_color: string,
     *     category_badge: string,
     *     completed_assessments: Collection<int, Assessment>,
     *     all_assessments: Collection<int, Assessment>
     * }
     */
    public function calculateSubjectCombinedScore(User $subject, ?Survey $survey = null): array
    {
        $query = Assessment::query()
            ->has('survey')
            ->with(['assessor', 'survey', 'answers.question'])
            ->where('subject_id', $subject->id);

        if ($survey !== null) {
            $query->where('survey_id', $survey->id);
        }

        $allAssessments = $query->get();
        $totalCount = $allAssessments->count();

        $completedAssessments = $allAssessments->where('status', 'completed');
        $completedCount = $completedAssessments->count();

        $completionRate = $totalCount > 0 ? round(($completedCount / $totalCount) * 100, 2) : 0.0;

        if ($completedCount === 0) {
            return [
                'completed_count' => 0,
                'total_count' => $totalCount,
                'completion_rate' => $completionRate,
                'combined_score' => 0,
                'combined_max_score' => 0,
                'percentage' => 0.0,
                'category' => 'Pending',
                'category_emoji' => '⏳',
                'category_color' => '#9CA3AF',
                'category_badge' => 'bg-gray-100 text-gray-700 border-gray-200',
                'completed_assessments' => $completedAssessments,
                'all_assessments' => $allAssessments,
            ];
        }

        $combinedScore = (int) $completedAssessments->sum('total_score');
        $combinedMaxScore = (int) $completedAssessments->sum('max_score');

        // Fallback for max score if not pre-calculated
        if ($combinedMaxScore === 0) {
            $questionCount = $survey?->questions()->count() ?? 11;
            $combinedMaxScore = $completedCount * ($questionCount * 10);
        }

        $percentage = $this->calculatePercentage($combinedScore, $combinedMaxScore);
        $category = $this->categoryService->getCategory($percentage);

        $selfAndPeer = $this->calculateSelfAndPeerScores($subject, $survey);

        return [
            'completed_count' => $completedCount,
            'total_count' => $totalCount,
            'completion_rate' => $completionRate,
            'combined_score' => $combinedScore,
            'combined_max_score' => $combinedMaxScore,
            'percentage' => $percentage,
            'category' => $category,
            'category_emoji' => $this->categoryService->getEmoji($category),
            'category_color' => $this->categoryService->getColorHex($category),
            'category_badge' => $this->categoryService->getBadgeClass($category),
            'completed_assessments' => $completedAssessments,
            'all_assessments' => $allAssessments,
            'self_metrics' => $selfAndPeer['self'],
            'peer_metrics' => $selfAndPeer['peer'],
            'comparison' => $selfAndPeer['comparison'],
        ];
    }

    /**
     * Calculate distinct Self-Assessed and Peer-Assessed metrics for a subject.
     *
     * @return array{
     *     self: array{
     *         has_assessment: bool,
     *         is_completed: bool,
     *         score: int,
     *         max_score: int,
     *         percentage: float,
     *         category: string,
     *         category_emoji: string,
     *         category_color: string,
     *         category_badge: string,
     *         assessment: ?Assessment
     *     },
     *     peer: array{
     *         has_assessment: bool,
     *         completed_count: int,
     *         total_count: int,
     *         completion_rate: float,
     *         average_score: float,
     *         score: int,
     *         max_score: int,
     *         percentage: float,
     *         category: string,
     *         category_emoji: string,
     *         category_color: string,
     *         category_badge: string
     *     },
     *     comparison: array{
     *         has_both: bool,
     *         gap: float,
     *         direction: string,
     *         direction_text: string,
     *         alignment_badge: string,
     *         alignment_label: string,
     *         insight: string
     *     }
     * }
     */
    public function calculateSelfAndPeerScores(User $subject, ?Survey $survey = null): array
    {
        $query = Assessment::query()
            ->has('survey')
            ->with(['assessor', 'survey', 'answers.question'])
            ->where('subject_id', $subject->id);

        if ($survey !== null) {
            $query->where('survey_id', $survey->id);
        }

        $allAssessments = $query->get();

        // 1. Self Assessment (assessor_id == subject_id)
        $selfAssessment = $allAssessments->firstWhere('assessor_id', $subject->id);
        $selfIsCompleted = $selfAssessment && $selfAssessment->status === 'completed';

        if ($selfIsCompleted) {
            $selfScore = (int) $selfAssessment->total_score;
            $selfMaxScore = (int) ($selfAssessment->max_score ?: 110);
            $selfPercentage = (float) $selfAssessment->percentage;
            $selfCategory = $selfAssessment->category ?: $this->categoryService->getCategory($selfPercentage);

            $selfMetrics = [
                'has_assessment' => true,
                'is_completed' => true,
                'score' => $selfScore,
                'max_score' => $selfMaxScore,
                'percentage' => $selfPercentage,
                'category' => $selfCategory,
                'category_emoji' => $this->categoryService->getEmoji($selfCategory),
                'category_color' => $this->categoryService->getColorHex($selfCategory),
                'category_badge' => $this->categoryService->getBadgeClass($selfCategory),
                'assessment' => $selfAssessment,
            ];
        } else {
            $selfMetrics = [
                'has_assessment' => (bool) $selfAssessment,
                'is_completed' => false,
                'score' => 0,
                'max_score' => 110,
                'percentage' => 0.0,
                'category' => 'Pending',
                'category_emoji' => '⏳',
                'category_color' => '#9CA3AF',
                'category_badge' => 'bg-gray-100 text-gray-700 border-gray-200',
                'assessment' => $selfAssessment,
            ];
        }

        // 2. Peer Assessments (assessor_id != subject_id)
        $peerAssessments = $allAssessments->filter(fn ($a) => $a->assessor_id !== $subject->id);
        $peerTotalCount = $peerAssessments->count();
        $peerCompleted = $peerAssessments->where('status', 'completed');
        $peerCompletedCount = $peerCompleted->count();
        $peerCompletionRate = $peerTotalCount > 0 ? round(($peerCompletedCount / $peerTotalCount) * 100, 2) : 0.0;

        if ($peerCompletedCount > 0) {
            $peerScore = (int) $peerCompleted->sum('total_score');
            $peerMaxScore = (int) $peerCompleted->sum('max_score');
            if ($peerMaxScore === 0) {
                $peerMaxScore = $peerCompletedCount * 110;
            }
            $peerPercentage = $this->calculatePercentage($peerScore, $peerMaxScore);
            $peerCategory = $this->categoryService->getCategory($peerPercentage);
            $peerAverageScore = round($peerCompleted->avg('total_score'), 1);

            $peerMetrics = [
                'has_assessment' => true,
                'completed_count' => $peerCompletedCount,
                'total_count' => $peerTotalCount,
                'completion_rate' => $peerCompletionRate,
                'average_score' => $peerAverageScore,
                'score' => $peerScore,
                'max_score' => $peerMaxScore,
                'percentage' => $peerPercentage,
                'category' => $peerCategory,
                'category_emoji' => $this->categoryService->getEmoji($peerCategory),
                'category_color' => $this->categoryService->getColorHex($peerCategory),
                'category_badge' => $this->categoryService->getBadgeClass($peerCategory),
            ];
        } else {
            $peerMetrics = [
                'has_assessment' => $peerTotalCount > 0,
                'completed_count' => 0,
                'total_count' => $peerTotalCount,
                'completion_rate' => $peerCompletionRate,
                'average_score' => 0.0,
                'score' => 0,
                'max_score' => $peerTotalCount * 110,
                'percentage' => 0.0,
                'category' => 'Pending',
                'category_emoji' => '⏳',
                'category_color' => '#9CA3AF',
                'category_badge' => 'bg-gray-100 text-gray-700 border-gray-200',
            ];
        }

        // 3. Comparison & Perception Gap
        $hasBoth = $selfMetrics['is_completed'] && ($peerMetrics['completed_count'] > 0);
        if ($hasBoth) {
            $gap = round($selfMetrics['percentage'] - $peerMetrics['percentage'], 2);
            $absGap = abs($gap);

            if ($absGap <= 4.0) {
                $direction = 'aligned';
                $directionText = 'in close alignment with peers';
                $alignmentBadge = 'bg-emerald-50 text-emerald-800 border-emerald-200';
                $alignmentLabel = 'High Self-Awareness (Aligned)';
                $insight = "Your self-evaluation ({$selfMetrics['percentage']}%) aligns tightly with how colleagues perceive you ({$peerMetrics['percentage']}%). This demonstrates strong self-awareness.";
            } elseif ($gap > 4.0) {
                $direction = 'higher';
                $directionText = "{$absGap}% higher than peers";
                $alignmentBadge = 'bg-amber-50 text-amber-800 border-amber-200';
                $alignmentLabel = 'Self-Overestimate Gap';
                $insight = "You rated yourself {$absGap}% higher than your peer average ({$selfMetrics['percentage']}% vs {$peerMetrics['percentage']}%). Colleague feedback points to growth opportunities.";
            } else {
                $direction = 'lower';
                $directionText = "{$absGap}% lower than peers";
                $alignmentBadge = 'bg-blue-50 text-blue-800 border-blue-200';
                $alignmentLabel = 'Hidden Strengths (Modest)';
                $insight = "Your colleagues rated your competency {$absGap}% higher than you rated yourself ({$peerMetrics['percentage']}% vs {$selfMetrics['percentage']}%), highlighting strong hidden strengths.";
            }

            $comparison = [
                'has_both' => true,
                'gap' => $gap,
                'direction' => $direction,
                'direction_text' => $directionText,
                'alignment_badge' => $alignmentBadge,
                'alignment_label' => $alignmentLabel,
                'insight' => $insight,
            ];
        } else {
            $comparison = [
                'has_both' => false,
                'gap' => 0.0,
                'direction' => 'pending',
                'direction_text' => 'Pending evaluations',
                'alignment_badge' => 'bg-gray-100 text-gray-700 border-gray-200',
                'alignment_label' => 'Pending Comparisons',
                'insight' => 'Complete both self-assessment and peer reviews to unlock 360° gap analysis.',
            ];
        }

        return [
            'self' => $selfMetrics,
            'peer' => $peerMetrics,
            'comparison' => $comparison,
        ];
    }

    /**
     * Calculate aggregated 360 metrics and average score for an entire company.
     *
     * @return array{
     *     total_assessments: int,
     *     completed_assessments: int,
     *     pending_assessments: int,
     *     completion_rate: float,
     *     average_percentage: float,
     *     total_score: int,
     *     max_score: int,
     *     category: string,
     *     category_emoji: string,
     *     category_color: string,
     *     category_badge: string,
     *     self_average_percentage: float,
     *     peer_average_percentage: float,
     *     self_completed_count: int,
     *     peer_completed_count: int,
     *     perception_gap: float
     * }
     */
    public function calculateCompanyMetrics(Company $company, ?Survey $survey = null): array
    {
        $query = Assessment::query()
            ->has('survey')
            ->where(function ($q) use ($company) {
                $q->whereHas('survey', fn ($sq) => $sq->where('company_id', $company->id))
                    ->orWhereHas('subject', fn ($sq) => $sq->where('company_id', $company->id));
            });

        if ($survey !== null) {
            $query->where('survey_id', $survey->id);
        }

        $allAssessments = $query->get();
        $totalCount = $allAssessments->count();

        $completedAssessments = $allAssessments->where('status', 'completed');
        $completedCount = $completedAssessments->count();
        $pendingCount = $totalCount - $completedCount;
        $completionRate = $totalCount > 0 ? round(($completedCount / $totalCount) * 100, 2) : 0.0;

        if ($completedCount === 0) {
            return [
                'total_assessments' => $totalCount,
                'completed_assessments' => 0,
                'pending_assessments' => $pendingCount,
                'completion_rate' => $completionRate,
                'average_percentage' => 0.0,
                'total_score' => 0,
                'max_score' => 0,
                'category' => 'Pending',
                'category_emoji' => '⏳',
                'category_color' => '#9CA3AF',
                'category_badge' => 'bg-gray-100 text-gray-700 border-gray-200',
                'self_average_percentage' => 0.0,
                'peer_average_percentage' => 0.0,
                'self_completed_count' => 0,
                'peer_completed_count' => 0,
                'perception_gap' => 0.0,
            ];
        }

        $totalScore = (int) $completedAssessments->sum('total_score');
        $maxScore = (int) $completedAssessments->sum('max_score');
        $avgPercentage = round((float) $completedAssessments->avg('percentage'), 2);

        $category = $this->categoryService->getCategory($avgPercentage);

        // Self vs Peer breakdowns for the company
        $selfAssessments = $completedAssessments->filter(fn ($a) => $a->isSelfAssessment());
        $peerAssessments = $completedAssessments->filter(fn ($a) => ! $a->isSelfAssessment());

        $selfAvg = $selfAssessments->isNotEmpty() ? round((float) $selfAssessments->avg('percentage'), 2) : 0.0;
        $peerAvg = $peerAssessments->isNotEmpty() ? round((float) $peerAssessments->avg('percentage'), 2) : 0.0;
        $perceptionGap = round($selfAvg - $peerAvg, 2);

        return [
            'total_assessments' => $totalCount,
            'completed_assessments' => $completedCount,
            'pending_assessments' => $pendingCount,
            'completion_rate' => $completionRate,
            'average_percentage' => $avgPercentage,
            'total_score' => $totalScore,
            'max_score' => $maxScore,
            'category' => $category,
            'category_emoji' => $this->categoryService->getEmoji($category),
            'category_color' => $this->categoryService->getColorHex($category),
            'category_badge' => $this->categoryService->getBadgeClass($category),
            'self_average_percentage' => $selfAvg,
            'peer_average_percentage' => $peerAvg,
            'self_completed_count' => $selfAssessments->count(),
            'peer_completed_count' => $peerAssessments->count(),
            'perception_gap' => $perceptionGap,
        ];
    }

    /**
     * Calculate comprehensive 5-metric Change Quotient (CQ) individual report.
     *
     * 1. CQ 1 (Self)
     * 2. CQ 2 (Others / Peers)
     * 3. CQ 3 (Normalised)
     * 4. CQ Sync (Team Score / Group Helpfulness & Motivation)
     * 5. Competency matrix details
     *
     * @return array<string, mixed>
     */
    public function calculateChangeQuotientReport(User $subject, Survey $survey): array
    {
        $selfAndPeer = $this->calculateSelfAndPeerScores($subject, $survey);
        $self = $selfAndPeer['self'];
        $peer = $selfAndPeer['peer'];
        $comparison = $selfAndPeer['comparison'];

        // CQ 1 - Self
        $cq1Percentage = $self['is_completed'] ? $self['percentage'] : 0.0;
        $cq1Category = $self['category'];
        $cq1 = [
            'name' => 'CQ 1 (Self)',
            'label' => 'Personal Change Quotient',
            'score' => $self['score'],
            'max_score' => $self['max_score'],
            'percentage' => $cq1Percentage,
            'category' => $cq1Category,
            'emoji' => $this->categoryService->getEmoji($cq1Category),
            'color' => $this->categoryService->getColorHex($cq1Category),
            'badge' => $this->categoryService->getBadgeClass($cq1Category),
            'is_completed' => $self['is_completed'],
            'description' => 'Self-evaluation score on adaptability, resilience, and behavioral change agility.',
        ];

        // CQ 2 - Others
        $cq2Percentage = $peer['completed_count'] > 0 ? $peer['percentage'] : 0.0;
        $cq2Category = $peer['category'];
        $cq2 = [
            'name' => 'CQ 2 (Others)',
            'label' => 'Observer Change Quotient',
            'score' => $peer['score'],
            'max_score' => $peer['max_score'],
            'average_score' => $peer['average_score'],
            'percentage' => $cq2Percentage,
            'category' => $cq2Category,
            'emoji' => $this->categoryService->getEmoji($cq2Category),
            'color' => $this->categoryService->getColorHex($cq2Category),
            'badge' => $this->categoryService->getBadgeClass($cq2Category),
            'completed_count' => $peer['completed_count'],
            'total_count' => $peer['total_count'],
            'description' => 'Consensus evaluation of your change capabilities as experienced by colleagues.',
        ];

        // CQ 3 - Normalised
        if ($self['is_completed'] && $peer['completed_count'] > 0) {
            $cq3Percentage = round((0.40 * $cq1Percentage) + (0.60 * $cq2Percentage), 2);
        } elseif ($self['is_completed']) {
            $cq3Percentage = $cq1Percentage;
        } elseif ($peer['completed_count'] > 0) {
            $cq3Percentage = $cq2Percentage;
        } else {
            $cq3Percentage = 0.0;
        }
        $cq3Category = $cq3Percentage > 0 ? $this->categoryService->getCategory($cq3Percentage) : 'Pending';

        $gap = round($cq1Percentage - $cq2Percentage, 2);
        $absGap = abs($gap);
        if (! $self['is_completed'] || $peer['completed_count'] === 0) {
            $alignmentStatus = 'Pending Evaluations';
            $alignmentBadge = 'bg-gray-100 text-gray-700 border-gray-200';
            $alignmentInsight = 'Complete self-assessment and peer reviews to unlock normalized alignment insights.';
        } elseif ($absGap <= 4.0) {
            $alignmentStatus = 'High Self-Awareness (Aligned)';
            $alignmentBadge = 'bg-emerald-50 text-emerald-800 border-emerald-200';
            $alignmentInsight = 'Your self-perception mirrors peer perception very closely, demonstrating acute self-awareness and transparent teamwork.';
        } elseif ($gap > 4.0) {
            $alignmentStatus = 'Self-Overestimate Gap (Blind Spot)';
            $alignmentBadge = 'bg-amber-50 text-amber-800 border-amber-200';
            $alignmentInsight = "You rated yourself {$absGap}% higher than peer observations ({$cq1Percentage}% vs {$cq2Percentage}%). Focusing on peer feedback will reveal hidden growth levers.";
        } else {
            $alignmentStatus = 'Hidden Strengths (Modest Perceiver)';
            $alignmentBadge = 'bg-blue-50 text-blue-800 border-blue-200';
            $alignmentInsight = "Your colleagues rated you {$absGap}% higher than your self-score ({$cq2Percentage}% vs {$cq1Percentage}%). You possess latent strengths you may be under-acknowledging.";
        }

        $cq3 = [
            'name' => 'CQ 3 (Normalised)',
            'label' => 'Calibrated Change Quotient',
            'percentage' => $cq3Percentage,
            'category' => $cq3Category,
            'emoji' => $this->categoryService->getEmoji($cq3Category),
            'color' => $this->categoryService->getColorHex($cq3Category),
            'badge' => $this->categoryService->getBadgeClass($cq3Category),
            'gap' => $gap,
            'alignment_status' => $alignmentStatus,
            'alignment_badge' => $alignmentBadge,
            'alignment_insight' => $alignmentInsight,
            'description' => 'Standardized 360 benchmark synthesizing self-awareness with peer perception.',
        ];

        // CQ Sync - Team Score (Group helpfulness, motivation, and mutual support)
        $cqSync = $this->calculateTeamSyncScore($survey);

        // Detailed question-by-question breakdown for this individual
        $questionsBreakdown = $this->calculateIndividualQuestionsBreakdown($subject, $survey);

        return [
            'cq1' => $cq1,
            'cq2' => $cq2,
            'cq3' => $cq3,
            'cq_sync' => $cqSync,
            'comparison' => $comparison,
            'questions_breakdown' => $questionsBreakdown,
            'confidential_notice' => 'Individual reports are confidential and strictly for self-introspection.',
        ];
    }

    /**
     * Calculate Team Synchronization & Mutual Motivation score across the cohort.
     *
     * @return array<string, mixed>
     */
    public function calculateTeamSyncScore(Survey $survey): array
    {
        $allAssessments = $survey->assessments()->with(['answers.question'])->get();
        $completedAssessments = $allAssessments->where('status', 'completed');
        $completedCount = $completedAssessments->count();

        if ($completedCount === 0) {
            return [
                'name' => 'CQ Sync (Team Score)',
                'label' => 'Team Synchronization & Motivation Index',
                'percentage' => 0.0,
                'category' => 'Pending',
                'emoji' => '⏳',
                'color' => '#9CA3AF',
                'badge' => 'bg-gray-100 text-gray-700 border-gray-200',
                'mutual_helpfulness' => 0.0,
                'motivation_score' => 0.0,
                'cohesion_rate' => 0.0,
                'completed_assessments' => 0,
                'total_assessments' => $allAssessments->count(),
                'insight' => 'Awaiting survey completions to calculate cohort team sync and mutual motivation metrics.',
            ];
        }

        // Calculate overall average
        $avgScore = (float) $completedAssessments->avg('percentage');

        // Helpfulness & Motivation questions
        $helpfulnessKeywords = ['empathy', 'team', 'listen', 'conflict', 'motivat', 'initiative', 'help'];
        $answers = $completedAssessments->flatMap->answers;

        $supportAnswers = $answers->filter(function ($ans) use ($helpfulnessKeywords) {
            $text = strtolower($ans->question?->question_text ?? '');
            foreach ($helpfulnessKeywords as $kw) {
                if (str_contains($text, $kw)) {
                    return true;
                }
            }

            return false;
        });

        if ($supportAnswers->isNotEmpty()) {
            $helpfulnessPercentage = round(($supportAnswers->avg('score') / 10) * 100, 2);
        } else {
            $helpfulnessPercentage = round($avgScore, 2);
        }

        // Variance / Consensus across peers
        $percentages = $completedAssessments->pluck('percentage')->all();
        $count = count($percentages);
        if ($count > 1) {
            $mean = array_sum($percentages) / $count;
            $variance = array_sum(array_map(fn ($p) => pow($p - $mean, 2), $percentages)) / $count;
            $stdDev = sqrt($variance);
            $cohesionRate = max(0.0, min(100.0, round(100 - ($stdDev * 1.5), 1)));
        } else {
            $cohesionRate = 100.0;
        }

        // Blended CQ Sync Score
        $syncPercentage = round((0.60 * $helpfulnessPercentage) + (0.40 * $cohesionRate), 1);
        $syncCategory = $this->categoryService->getCategory($syncPercentage);

        if ($syncPercentage >= 80) {
            $syncInsight = 'High Team Synergy: The team is remarkably supportive, collaborative, and motivates one another proactively during change.';
        } elseif ($syncPercentage >= 60) {
            $syncInsight = 'Good Alignment: Team members generally cooperate and assist each other, with occasional friction in high-stress transitions.';
        } else {
            $syncInsight = 'Growth Needed: Team members operate largely in silos. Structured mutual-support and team building action plans will unlock higher synergy.';
        }

        return [
            'name' => 'CQ Sync (Team Score)',
            'label' => 'Team Synchronization & Motivation Index',
            'percentage' => $syncPercentage,
            'category' => $syncCategory,
            'emoji' => $this->categoryService->getEmoji($syncCategory),
            'color' => $this->categoryService->getColorHex($syncCategory),
            'badge' => $this->categoryService->getBadgeClass($syncCategory),
            'mutual_helpfulness' => $helpfulnessPercentage,
            'motivation_score' => $helpfulnessPercentage,
            'cohesion_rate' => $cohesionRate,
            'completed_assessments' => $completedCount,
            'total_assessments' => $allAssessments->count(),
            'insight' => $syncInsight,
        ];
    }

    /**
     * Calculate individual competency breakdown across all questions in the survey.
     *
     * @return array<int, array<string, mixed>>
     */
    public function calculateIndividualQuestionsBreakdown(User $subject, Survey $survey): array
    {
        $questions = $survey->questions()->orderBy('sort_order')->get();
        if ($questions->isEmpty()) {
            return [];
        }

        $allAssessments = $survey->assessments()
            ->with(['answers'])
            ->where('subject_id', $subject->id)
            ->where('status', 'completed')
            ->get();

        $selfAssessment = $allAssessments->firstWhere('assessor_id', $subject->id);
        $peerAssessments = $allAssessments->filter(fn ($a) => $a->assessor_id !== $subject->id);

        $breakdown = [];
        foreach ($questions as $index => $q) {
            $selfScore = null;
            if ($selfAssessment) {
                $ans = $selfAssessment->answers->firstWhere('question_id', $q->id);
                $selfScore = $ans ? $ans->score : null;
            }

            $peerScores = [];
            foreach ($peerAssessments as $pa) {
                $ans = $pa->answers->firstWhere('question_id', $q->id);
                if ($ans && $ans->score !== null) {
                    $peerScores[] = $ans->score;
                }
            }

            $peerAvg = ! empty($peerScores) ? round(array_sum($peerScores) / count($peerScores), 1) : null;
            $gap = ($selfScore !== null && $peerAvg !== null) ? round($selfScore - $peerAvg, 1) : null;

            $dimension = $this->inferDimensionTag($q->question_text);

            $breakdown[] = [
                'number' => $index + 1,
                'question_id' => $q->id,
                'question_text' => $q->question_text,
                'dimension' => $dimension,
                'self_score' => $selfScore,
                'peer_avg' => $peerAvg,
                'gap' => $gap,
                'self_percentage' => $selfScore !== null ? $selfScore * 10 : null,
                'peer_percentage' => $peerAvg !== null ? round($peerAvg * 10, 1) : null,
            ];
        }

        return $breakdown;
    }

    /**
     * Calculate Group Insights & Recommendations (What to Solve) for the team/cohort.
     * Note: Zero individual names are exposed in this report.
     *
     * @return array<string, mixed>
     */
    public function calculateGroupInsights(Survey $survey): array
    {
        $assessments = $survey->assessments()->with(['answers.question', 'assessor', 'subject'])->get();
        $totalAssessments = $assessments->count();
        $completedAssessments = $assessments->where('status', 'completed');
        $completedCount = $completedAssessments->count();
        $completionRate = $totalAssessments > 0 ? round(($completedCount / $totalAssessments) * 100, 2) : 0.0;

        $selfAssessments = $completedAssessments->filter(fn ($a) => $a->isSelfAssessment());
        $peerAssessments = $completedAssessments->filter(fn ($a) => ! $a->isSelfAssessment());

        $selfAvg = $selfAssessments->isNotEmpty() ? round((float) $selfAssessments->avg('percentage'), 2) : 0.0;
        $peerAvg = $peerAssessments->isNotEmpty() ? round((float) $peerAssessments->avg('percentage'), 2) : 0.0;
        $normalisedAvg = ($selfAvg > 0 && $peerAvg > 0) ? round((0.40 * $selfAvg) + (0.60 * $peerAvg), 2) : round((float) $completedAssessments->avg('percentage'), 2);

        $groupCategory = $this->categoryService->getCategory($normalisedAvg);
        $teamSync = $this->calculateTeamSyncScore($survey);

        // Competency Questions Analysis across all answers
        $questions = $survey->questions()->orderBy('sort_order')->get();
        $questionsData = [];

        foreach ($questions as $q) {
            $qSelfScores = [];
            $qPeerScores = [];

            foreach ($completedAssessments as $a) {
                $ans = $a->answers->firstWhere('question_id', $q->id);
                if ($ans && $ans->score !== null) {
                    if ($a->isSelfAssessment()) {
                        $qSelfScores[] = $ans->score;
                    } else {
                        $qPeerScores[] = $ans->score;
                    }
                }
            }

            $selfScoreAvg = ! empty($qSelfScores) ? round(array_sum($qSelfScores) / count($qSelfScores), 2) : 0.0;
            $peerScoreAvg = ! empty($qPeerScores) ? round(array_sum($qPeerScores) / count($qPeerScores), 2) : 0.0;
            $allScores = array_merge($qSelfScores, $qPeerScores);
            $overallAvg = ! empty($allScores) ? round(array_sum($allScores) / count($allScores), 2) : 0.0;
            $overallPercentage = round($overallAvg * 10, 1);

            $questionsData[] = [
                'id' => $q->id,
                'question_text' => $q->question_text,
                'dimension' => $this->inferDimensionTag($q->question_text),
                'overall_avg' => $overallAvg,
                'overall_percentage' => $overallPercentage,
                'self_avg' => $selfScoreAvg,
                'peer_avg' => $peerScoreAvg,
                'gap' => round($selfScoreAvg - $peerScoreAvg, 2),
                'response_count' => count($allScores),
            ];
        }

        // Sort by overall percentage to find Top Strengths and Critical Growth Gaps (What to Solve)
        $sortedQuestions = collect($questionsData)->sortBy('overall_percentage')->values();
        $criticalGaps = $sortedQuestions->take(3)->all();
        $topStrengths = $sortedQuestions->reverse()->take(3)->values()->all();

        // Generate tailored intent-level action plan recommendations based on critical gaps
        $recommendations = $this->generateRecommendationsForGaps($criticalGaps);

        return [
            'cohort_size' => $survey->participants()->count(),
            'total_assessments' => $totalAssessments,
            'completed_assessments' => $completedCount,
            'completion_rate' => $completionRate,
            'average_cq1_self' => $selfAvg,
            'average_cq2_others' => $peerAvg,
            'average_cq3_normalised' => $normalisedAvg,
            'cq_sync' => $teamSync,
            'group_category' => $groupCategory,
            'group_emoji' => $this->categoryService->getEmoji($groupCategory),
            'group_color' => $this->categoryService->getColorHex($groupCategory),
            'group_badge' => $this->categoryService->getBadgeClass($groupCategory),
            'questions_data' => $questionsData,
            'top_strengths' => $topStrengths,
            'critical_gaps' => $criticalGaps,
            'recommendations' => $recommendations,
            'sign_off' => [
                'status' => $survey->sign_off_status ?? 'pending',
                'lead' => $survey->sign_off_lead,
                'notes' => $survey->sign_off_notes,
                'signed_off_at' => $survey->signed_off_at,
            ],
            'confidentiality_guarantee' => 'This group report is public to the team with ZERO individual names exposed to protect psychological safety and focus on team action plans.',
        ];
    }

    /**
     * Infer human-readable competency dimension tag from question phrasing.
     */
    protected function inferDimensionTag(string $text): string
    {
        $lower = strtolower($text);

        if (str_contains($lower, 'empath')) {
            return 'Empathy & Support';
        }
        if (str_contains($lower, 'communicat')) {
            return 'Communication Clarity';
        }
        if (str_contains($lower, 'conflict')) {
            return 'Conflict Resolution';
        }
        if (str_contains($lower, 'team')) {
            return 'Team Collaboration';
        }
        if (str_contains($lower, 'listen')) {
            return 'Active Listening';
        }
        if (str_contains($lower, 'decis')) {
            return 'Decision Agility';
        }
        if (str_contains($lower, 'change') || str_contains($lower, 'adapt')) {
            return 'Change Adaptability';
        }
        if (str_contains($lower, 'initiat') || str_contains($lower, 'motivat')) {
            return 'Initiative & Drive';
        }
        if (str_contains($lower, 'problem')) {
            return 'Problem Solving';
        }
        if (str_contains($lower, 'responsib')) {
            return 'Ownership & Accountability';
        }
        if (str_contains($lower, 'goal') || str_contains($lower, 'reliab')) {
            return 'Goal Execution';
        }

        return 'Leadership Competency';
    }

    /**
     * Generate dynamic intent-level action recommendations based on identified gaps.
     *
     * @param  array<int, array<string, mixed>>  $gaps
     * @return array<int, array<string, string>>
     */
    protected function generateRecommendationsForGaps(array $gaps): array
    {
        $recommendationMap = [
            'Empathy & Support' => [
                'title' => 'Cultivate Psychological Safety & Active Check-Ins',
                'intent' => 'Enhance mutual empathy and relational support across the team.',
                'action' => 'Implement structured 10-minute weekly peer connection syncs and normalize sharing emotional bandwidth during change sprints.',
            ],
            'Conflict Resolution' => [
                'title' => 'Formalize Constructive Dissent Frameworks',
                'intent' => 'Address interpersonal tensions early and turn disagreements into creative problem-solving.',
                'action' => 'Adopt an "interest-based" debrief protocol for project retrospectives to air friction points safely and establish team consensus.',
            ],
            'Active Listening' => [
                'title' => 'Reflective Inquiry & Dialogue Workshops',
                'intent' => 'Improve team receptivity and minimize misunderstandings.',
                'action' => 'Incorporate 2-minute reflective summarization ("What I heard you say is...") during team alignment and standup discussions.',
            ],
            'Decision Agility' => [
                'title' => 'Decentralized Decision Delegation (DACI / RACI)',
                'intent' => 'Accelerate decision-making velocity and eliminate consensus bottlenecks.',
                'action' => 'Document explicit decision owners for key team workflows to empower rapid experimentation without executive sign-off delays.',
            ],
            'Change Adaptability' => [
                'title' => 'Iterative Change Sprints & Agility Retrospectives',
                'intent' => 'Reduce organizational friction when navigating pivots and new business realities.',
                'action' => 'Break large initiatives into 2-week agile experiments with explicit learning reviews to celebrate adaptability.',
            ],
            'Team Collaboration' => [
                'title' => 'Cross-Functional Peer Shadowing & Shared OKRs',
                'intent' => 'Eliminate siloed working habits and foster collective ownership.',
                'action' => 'Align quarterly goals across adjacent team members so success metrics require mutual support.',
            ],
            'Initiative & Drive' => [
                'title' => 'Autonomous Sandbox & Innovation Time',
                'intent' => 'Encourage proactive self-starting and grassroots leadership.',
                'action' => 'Provide team members with dedicated bandwidth each cycle to independently research and propose workflow improvements.',
            ],
            'Communication Clarity' => [
                'title' => 'Asynchronous Transparency & Playbook Documentation',
                'intent' => 'Ensure information cascades cleanly without message distortion.',
                'action' => 'Standardize project briefing templates with clear definitions of done, timelines, and deliverables.',
            ],
        ];

        $recs = [];
        foreach ($gaps as $gap) {
            $dim = $gap['dimension'];
            if (isset($recommendationMap[$dim])) {
                $recs[] = array_merge(['dimension' => $dim, 'question' => $gap['question_text']], $recommendationMap[$dim]);
            } else {
                $recs[] = [
                    'dimension' => $dim,
                    'question' => $gap['question_text'],
                    'title' => "Targeted Team Development on {$dim}",
                    'intent' => "Elevate team performance on {$dim}.",
                    'action' => "Schedule a team workshop focused on aligning expectations and operational rituals surrounding {$dim}.",
                ];
            }
        }

        return $recs;
    }
}
