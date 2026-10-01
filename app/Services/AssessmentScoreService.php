<?php

namespace App\Services;

use App\Models\Assessment;
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
}
