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
        ];
    }
}
