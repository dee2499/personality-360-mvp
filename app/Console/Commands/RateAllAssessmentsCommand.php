<?php

namespace App\Console\Commands;

use App\Models\Assessment;
use App\Models\AssessmentAnswer;
use App\Models\GroupSyncAnswer;
use App\Models\Question;
use App\Models\Survey;
use App\Models\User;
use App\Services\AssessmentCategoryService;
use App\Services\AssessmentScoreService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class RateAllAssessmentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assessment:rate-all {--survey= : Optional Survey ID to rate} {--all : Rate all published surveys}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate calibrated 360 assessment scenarios spanning min-to-max meter categories and all matrix quadrants for all employees';

    /**
     * Pre-calibrated 20-employee profiles covering all meter archetypes and 2x2 matrix quadrants:
     * - Archetypes: Resistant (1.0–2.0), Follower (2.1–4.0), Supporter (4.1–6.0), Driver (6.1–8.0), Champion (8.1–10.0)
     * - Quadrants: Perception Gap (Self High, Peer Low), Undervalued Potential (Self Low, Peer High),
     *              Aligned Strength (Self High, Peer High), Key Development Area (Self Low, Peer Low)
     *
     * @var array<string, array{self: float, peer: float, archetype: string, quadrant: string, description: string}>
     */
    protected array $profiles = [
        // 1. Top Tier Champion: Brenda Vance (9.5 Self, 9.3 Peer, 9.4 CQ -> Champion, Aligned Strength)
        'Brenda Vance' => [
            'self' => 9.5,
            'peer' => 9.3,
            'archetype' => 'Champion',
            'quadrant' => 'Aligned Strength',
            'description' => 'Top tier leader, 9.4 CQ Score, Aligned Strength',
        ],

        // 2. Champion: Victor Stone (8.0 Self, 9.4 Peer, 8.7 CQ -> Champion, Aligned Strength)
        'Victor Stone' => [
            'self' => 8.0,
            'peer' => 9.4,
            'archetype' => 'Champion',
            'quadrant' => 'Aligned Strength',
            'description' => 'Top tier 8.7 CQ Score, stellar peer acclaim',
        ],

        // 3. Champion: Carlos Diaz (8.5 Self, 8.3 Peer, 8.4 CQ -> Champion, Aligned Strength)
        'Carlos Diaz' => [
            'self' => 8.5,
            'peer' => 8.3,
            'archetype' => 'Champion',
            'quadrant' => 'Aligned Strength',
            'description' => '8.4 CQ Score, Champion, Aligned Strength',
        ],

        // 4. Driver / Perception Gap: Wendy Rhoades (8.8 Self, 6.8 Peer, 7.8 CQ -> Driver, Perception Gap)
        'Wendy Rhoades' => [
            'self' => 8.8,
            'peer' => 6.8,
            'archetype' => 'Driver',
            'quadrant' => 'Perception Gap',
            'description' => '7.8 CQ Score, Driver, Perception Gap (Self 8.8, Peer 6.8)',
        ],

        // 5. Driver / Aligned Strength: Danielle Brooks (7.6 Self, 7.4 Peer, 7.5 CQ -> Driver, Aligned Strength)
        'Danielle Brooks' => [
            'self' => 7.6,
            'peer' => 7.4,
            'archetype' => 'Driver',
            'quadrant' => 'Aligned Strength',
            'description' => '7.5 CQ Score, Driver, Aligned Strength',
        ],

        // 6. Driver / Perception Gap (Reference): Alex Morgan (8.1 Self, 6.4 Peer, 7.2 CQ -> Driver, Perception Gap)
        'Alex Morgan' => [
            'self' => 8.1,
            'peer' => 6.4,
            'archetype' => 'Driver',
            'quadrant' => 'Perception Gap',
            'description' => '7.2 CQ Score, Driver, Perception Gap (Self 8.1, Peer 6.4)',
        ],

        // 7. Driver / Undervalued Potential: Ethan Wright (5.5 Self, 8.1 Peer, 6.8 CQ -> Driver, Undervalued Potential)
        'Ethan Wright' => [
            'self' => 5.5,
            'peer' => 8.1,
            'archetype' => 'Driver',
            'quadrant' => 'Undervalued Potential',
            'description' => '6.8 CQ Score, Driver, Undervalued Potential (Self 5.5, Peer 8.1)',
        ],

        // 8. Driver / Perception Gap: Fiona Gallagher (7.8 Self, 5.6 Peer, 6.7 CQ -> Driver, Perception Gap)
        'Fiona Gallagher' => [
            'self' => 7.8,
            'peer' => 5.6,
            'archetype' => 'Driver',
            'quadrant' => 'Perception Gap',
            'description' => '6.7 CQ Score, Driver, Perception Gap (Self 7.8, Peer 5.6)',
        ],

        // 9. Driver / Undervalued Potential: Tina Fey (4.8 Self, 7.6 Peer, 6.2 CQ -> Driver, Undervalued Potential)
        'Tina Fey' => [
            'self' => 4.8,
            'peer' => 7.6,
            'archetype' => 'Driver',
            'quadrant' => 'Undervalued Potential',
            'description' => '6.2 CQ Score, Driver, Undervalued Potential (Self 4.8, Peer 7.6)',
        ],

        // 10. Supporter / Undervalued Potential: George Clark (4.2 Self, 6.8 Peer, 5.5 CQ -> Supporter, Undervalued Potential)
        'George Clark' => [
            'self' => 4.2,
            'peer' => 6.8,
            'archetype' => 'Supporter',
            'quadrant' => 'Undervalued Potential',
            'description' => '5.5 CQ Score, Supporter, Undervalued Potential (Self 4.2, Peer 6.8)',
        ],

        // 11. Supporter / Undervalued Potential: Hannah Abbott (3.8 Self, 6.4 Peer, 5.1 CQ -> Supporter, Undervalued Potential)
        'Hannah Abbott' => [
            'self' => 3.8,
            'peer' => 6.4,
            'archetype' => 'Supporter',
            'quadrant' => 'Undervalued Potential',
            'description' => '5.1 CQ Score, Supporter, Undervalued Potential (Self 3.8, Peer 6.4)',
        ],

        // 12. Supporter / Key Development Area: Ian Malcolm (5.3 Self, 5.1 Peer, 5.2 CQ -> Supporter, Key Development Area)
        'Ian Malcolm' => [
            'self' => 5.3,
            'peer' => 5.1,
            'archetype' => 'Supporter',
            'quadrant' => 'Key Development Area',
            'description' => '5.2 CQ Score, Supporter, Key Development Area (Self 5.3, Peer 5.1)',
        ],

        // 13. Supporter / Perception Gap: Kevin Bacon (6.4 Self, 3.6 Peer, 5.0 CQ -> Supporter, Perception Gap)
        'Kevin Bacon' => [
            'self' => 6.4,
            'peer' => 3.6,
            'archetype' => 'Supporter',
            'quadrant' => 'Perception Gap',
            'description' => '5.0 CQ Score, Supporter, Perception Gap (Self 6.4, Peer 3.6)',
        ],

        // 14. Supporter / Key Development Area: Julia Roberts (4.6 Self, 5.0 Peer, 4.8 CQ -> Supporter, Key Development Area)
        'Julia Roberts' => [
            'self' => 4.6,
            'peer' => 5.0,
            'archetype' => 'Supporter',
            'quadrant' => 'Key Development Area',
            'description' => '4.8 CQ Score, Supporter, Key Development Area (Self 4.6, Peer 5.0)',
        ],

        // 15. Follower / Perception Gap: Michael Scott (6.2 Self, 1.8 Peer, 4.0 CQ -> Follower, Perception Gap)
        'Michael Scott' => [
            'self' => 6.2,
            'peer' => 1.8,
            'archetype' => 'Follower',
            'quadrant' => 'Perception Gap',
            'description' => '4.0 CQ Score, Follower, Perception Gap (Self 6.2, Peer 1.8)',
        ],

        // 16. Follower / Key Development Area: Laura Croft (3.2 Self, 4.4 Peer, 3.8 CQ -> Follower, Key Development Area)
        'Laura Croft' => [
            'self' => 3.2,
            'peer' => 4.4,
            'archetype' => 'Follower',
            'quadrant' => 'Key Development Area',
            'description' => '3.8 CQ Score, Follower, Key Development Area (Self 3.2, Peer 4.4)',
        ],

        // 17. Follower / Key Development Area: Natalie Portman (2.8 Self, 3.6 Peer, 3.2 CQ -> Follower, Key Development Area)
        'Natalie Portman' => [
            'self' => 2.8,
            'peer' => 3.6,
            'archetype' => 'Follower',
            'quadrant' => 'Key Development Area',
            'description' => '3.2 CQ Score, Follower, Key Development Area (Self 2.8, Peer 3.6)',
        ],

        // 18. Follower / Key Development Area: Oliver Queen (2.2 Self, 2.6 Peer, 2.4 CQ -> Follower, Key Development Area)
        'Oliver Queen' => [
            'self' => 2.2,
            'peer' => 2.6,
            'archetype' => 'Follower',
            'quadrant' => 'Key Development Area',
            'description' => '2.4 CQ Score, Lower Follower, Key Development Area (Self 2.2, Peer 2.6)',
        ],

        // 19. Resistor / Key Development Area: Rachel Green (1.8 Self, 2.0 Peer, 1.9 CQ -> Resistor, Key Development Area)
        'Rachel Green' => [
            'self' => 1.8,
            'peer' => 2.0,
            'archetype' => 'Resistor',
            'quadrant' => 'Key Development Area',
            'description' => '1.9 CQ Score, Resistor, Key Development Area (Self 1.8, Peer 2.0)',
        ],

        // 20. Absolute Min Resistor / Key Development Area: Samuel Jackson (1.2 Self, 1.4 Peer, 1.3 CQ -> Resistor, Key Development Area)
        'Samuel Jackson' => [
            'self' => 1.2,
            'peer' => 1.4,
            'archetype' => 'Resistor',
            'quadrant' => 'Key Development Area',
            'description' => '1.3 CQ Score, Minimum Resistor, Key Development Area (Self 1.2, Peer 1.4)',
        ],

        // System Admin benchmark
        'System Admin' => [
            'self' => 8.5,
            'peer' => 8.5,
            'archetype' => 'Champion',
            'quadrant' => 'Aligned Strength',
            'description' => '8.5 CQ Score, Admin benchmark',
        ],
    ];

    /**
     * Execute the console command.
     */
    public function handle(
        AssessmentCategoryService $categoryService,
        AssessmentScoreService $scoreService
    ): int {
        $surveyId = $this->option('survey');
        $surveys = $surveyId
            ? Survey::where('id', $surveyId)->get()
            : Survey::where('status', 'published')->get();

        if ($surveys->isEmpty()) {
            $this->error('No published surveys found to rate.');

            return self::FAILURE;
        }

        foreach ($surveys as $survey) {
            $individualQuestions = $survey->questions()
                ->where(function ($q) {
                    $q->where('type', 'individual')
                        ->orWhereNull('type');
                })
                ->orderBy('sort_order')
                ->take(11)
                ->get();

            if ($individualQuestions->isEmpty()) {
                $individualQuestions = $survey->questions()->orderBy('sort_order')->take(11)->get();
            }

            if ($individualQuestions->isEmpty()) {
                $this->warn("Survey #{$survey->id} has no questions. Skipping.");

                continue;
            }

            $groupSyncQuestions = $survey->questions()
                ->where('type', 'group_sync')
                ->orderBy('sort_order')
                ->get();

            $participants = $survey->participants()->get();
            if ($participants->isEmpty()) {
                $this->warn("Survey #{$survey->id} has no attached participants. Skipping.");

                continue;
            }

            $this->info("=== Rating all {$participants->count()} employees for Survey: '{$survey->title}' (#{$survey->id}) ===");

            $assessments = Assessment::where('survey_id', $survey->id)->get();
            $totalAnswersCount = 0;
            $totalAssessmentsRated = 0;

            foreach ($assessments as $assessment) {
                $isSelf = ($assessment->assessor_id === $assessment->subject_id);
                $subjectName = $assessment->subject?->name ?? '';

                $profile = $this->profiles[$subjectName] ?? ['self' => 7.0, 'peer' => 7.0];

                if ($isSelf) {
                    $scores = $this->generateQuestionScores(
                        $profile['self'],
                        $individualQuestions->count(),
                        (int) $assessment->subject_id * 3
                    );
                } else {
                    // Slight rater offset (-0.2, 0, +0.2) to simulate natural reviewer differences while maintaining target mean
                    $offset = ((($assessment->assessor_id * 7 + $assessment->subject_id) % 3) - 1) * 0.2;
                    $peerTarget = max(1.0, min(10.0, $profile['peer'] + $offset));

                    $scores = $this->generateQuestionScores(
                        $peerTarget,
                        $individualQuestions->count(),
                        (int) ($assessment->assessor_id * 11 + $assessment->subject_id * 5)
                    );
                }

                // Clean old assessment answers
                $assessment->answers()->delete();

                foreach ($individualQuestions as $qIndex => $question) {
                    AssessmentAnswer::create([
                        'assessment_id' => $assessment->id,
                        'question_id' => $question->id,
                        'score' => $scores[$qIndex],
                    ]);

                    $totalAnswersCount++;
                }

                $assessment->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);

                $scoreService->calculateAssessmentScore($assessment);
                $totalAssessmentsRated++;
            }

            // Populate Group Sync ratings for all participants
            if ($groupSyncQuestions->isNotEmpty()) {
                foreach ($participants as $participant) {
                    foreach ($groupSyncQuestions as $gIndex => $gQuestion) {
                        $syncScore = max(1, min(10, (int) round(7.0 + sin($participant->id * 1.5 + $gIndex) * 1.8)));
                        GroupSyncAnswer::updateOrCreate(
                            [
                                'survey_id' => $survey->id,
                                'user_id' => $participant->id,
                                'question_id' => $gQuestion->id,
                            ],
                            [
                                'score' => $syncScore,
                            ]
                        );
                    }
                }
            }

            $this->info("Completed {$totalAssessmentsRated} assessments with {$totalAnswersCount} question answers for Survey #{$survey->id}!");

            // Print summary verification table for all participants
            $this->displaySummaryTable($participants, $survey, $scoreService);
        }

        return self::SUCCESS;
    }

    /**
     * Generate an array of integer question scores (1-10) with realistic distribution matching target average.
     *
     * @return array<int, int>
     */
    protected function generateQuestionScores(float $targetAverage, int $questionCount = 11, int $seed = 0): array
    {
        $targetAverage = max(1.0, min(10.0, $targetAverage));
        $targetSum = (int) round($targetAverage * $questionCount);
        $base = (int) floor($targetAverage);

        $scores = array_fill(0, $questionCount, $base);

        // Realistic variation pattern across the 11 change journey dimensions
        $pattern = [-1, 0, 1, 0, 1, -1, 0, 1, -1, 1, 0];
        for ($i = 0; $i < $questionCount; $i++) {
            $offset = $pattern[($i + $seed) % count($pattern)];
            $scores[$i] = max(1, min(10, $scores[$i] + $offset));
        }

        // Adjust to match exact targetSum
        $currentSum = array_sum($scores);
        $diff = $targetSum - $currentSum;
        $step = $diff > 0 ? 1 : -1;
        $rem = abs($diff);

        $attempt = 0;
        while ($rem > 0 && $attempt < 120) {
            $idx = ($attempt * 3 + $seed) % $questionCount;
            $newVal = $scores[$idx] + $step;
            if ($newVal >= 1 && $newVal <= 10) {
                $scores[$idx] = $newVal;
                $rem--;
            }
            $attempt++;
        }

        return $scores;
    }

    /**
     * Display a formatted CLI table summarizing all participant CQ reports.
     *
     * @param  Collection<int, User>  $participants
     */
    protected function displaySummaryTable($participants, Survey $survey, AssessmentScoreService $scoreService): void
    {
        $rows = [];

        foreach ($participants as $user) {
            $cq = $scoreService->calculateChangeQuotientReport($user, $survey);

            $rows[] = [
                $user->id,
                $user->name,
                number_format((float) ($cq['self_score'] ?? 0), 1),
                number_format((float) ($cq['peer_score'] ?? 0), 1),
                number_format((float) ($cq['overall_cq_score'] ?? 0), 1),
                $cq['profile_name'] ?? 'Pending',
                $cq['matrix']['quadrant_name'] ?? 'N/A',
            ];
        }

        $this->table(
            ['ID', 'Employee Name', 'Self Score', 'Peer Score', 'CQ Score', 'Archetype', 'Matrix Quadrant'],
            $rows
        );
    }
}
