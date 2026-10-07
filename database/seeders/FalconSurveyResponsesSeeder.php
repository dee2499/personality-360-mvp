<?php

namespace Database\Seeders;

use App\Models\Assessment;
use App\Models\AssessmentAnswer;
use App\Models\Company;
use App\Models\GroupSyncAnswer;
use App\Models\Question;
use App\Models\Survey;
use App\Services\AssessmentScoreService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FalconSurveyResponsesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Fills out all assessments for Falcon Group's ChangeQuo survey
     * with a realistic, diverse distribution of scores covering all categories:
     * - Resistor (1-3)
     * - Follower (3-5)
     * - Supporter (4-7)
     * - Initiator (6-9)
     * - Achiever (8-10)
     */
    public function run(): void
    {
        $company = Company::where('name', 'like', '%Falcon%')->first();
        if (! $company) {
            $this->command?->warn('Falcon Group company not found.');

            return;
        }

        $survey = Survey::where('company_id', $company->id)->first();
        if (! $survey) {
            $this->command?->warn('Falcon Group survey not found.');

            return;
        }

        $participants = $survey->participants()->orderBy('id')->get();
        if ($participants->isEmpty()) {
            $this->command?->warn('No participants found for Falcon Group survey.');

            return;
        }

        $individualQuestions = $survey->questions()
            ->where(function ($q) {
                $q->where('type', 'individual')->orWhereNull('type');
            })
            ->orderBy('sort_order')
            ->get();

        $groupSyncQuestions = $survey->questions()
            ->where('type', 'group_sync')
            ->orderBy('sort_order')
            ->get();

        $scoreService = app(AssessmentScoreService::class);

        // Predefined diverse behavioral archetype profiles across the 24 participants
        // This ensures the cohort displays a full, rich spectrum on the radar, matrix, and category breakdown:
        // Achievers (~9-10), Initiators (~7-8), Supporters (~5-6), Followers (~3-4), Resistors (~1-3)
        $archetypes = [
            0 => ['base' => 9, 'variance' => 1], // Achiever
            1 => ['base' => 8, 'variance' => 2], // Initiator
            2 => ['base' => 6, 'variance' => 2], // Supporter
            3 => ['base' => 4, 'variance' => 2], // Follower
            4 => ['base' => 2, 'variance' => 1], // Resistor
            5 => ['base' => 8, 'variance' => 1], // Initiator
            6 => ['base' => 9, 'variance' => 1], // Achiever
            7 => ['base' => 5, 'variance' => 2], // Supporter
            8 => ['base' => 3, 'variance' => 2], // Follower
            9 => ['base' => 7, 'variance' => 2], // Initiator
            10 => ['base' => 9, 'variance' => 1], // Achiever
            11 => ['base' => 2, 'variance' => 1], // Resistor
            12 => ['base' => 6, 'variance' => 2], // Supporter
            13 => ['base' => 4, 'variance' => 2], // Follower
            14 => ['base' => 8, 'variance' => 2], // Initiator
            15 => ['base' => 10, 'variance' => 1], // Achiever
            16 => ['base' => 5, 'variance' => 2], // Supporter
            17 => ['base' => 3, 'variance' => 2], // Follower
            18 => ['base' => 7, 'variance' => 2], // Initiator
            19 => ['base' => 2, 'variance' => 1], // Resistor
            20 => ['base' => 6, 'variance' => 1], // Supporter
            21 => ['base' => 8, 'variance' => 2], // Initiator
            22 => ['base' => 4, 'variance' => 2], // Follower
            23 => ['base' => 9, 'variance' => 1], // Achiever
        ];

        DB::transaction(function () use ($survey, $participants, $individualQuestions, $groupSyncQuestions, $scoreService, $archetypes) {
            $assessmentsToRecalc = [];

            // 1. Fill out all 360 multi-rater assessments (24 x 24 = 576 pairings: self & peer)
            foreach ($participants as $assessorIdx => $assessor) {
                // Each assessor also has their own rating tendency (critical, generous, balanced)
                $assessorBias = (($assessorIdx % 5) - 2) * 0.5; // -1.0 to +1.0

                foreach ($participants as $subjectIdx => $subject) {
                    $isSelf = ($assessor->id === $subject->id);
                    $profile = $archetypes[$subjectIdx % count($archetypes)];

                    $assessment = Assessment::firstOrCreate(
                        [
                            'survey_id' => $survey->id,
                            'assessor_id' => $assessor->id,
                            'subject_id' => $subject->id,
                        ],
                        [
                            'started_at' => now()->subDays(rand(2, 7)),
                        ]
                    );

                    $assessmentAnswers = [];
                    foreach ($individualQuestions as $qIdx => $question) {
                        // Blend subject's baseline aptitude with question variation and assessor bias
                        $qOffset = sin(($qIdx + 1) * 1.5) * 1.2;
                        $selfBonus = $isSelf ? (rand(-1, 2) * 0.5) : 0;
                        $randomNoise = (rand(-10, 10) / 10.0) * $profile['variance'];

                        $calculatedScore = (int) round($profile['base'] + $assessorBias + $qOffset + $selfBonus + $randomNoise);
                        $finalScore = max(1, min(10, $calculatedScore));

                        $assessmentAnswers[] = [
                            'assessment_id' => $assessment->id,
                            'question_id' => $question->id,
                            'score' => $finalScore,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }

                    // Upsert answers
                    AssessmentAnswer::where('assessment_id', $assessment->id)->delete();
                    AssessmentAnswer::insert($assessmentAnswers);

                    $assessment->update([
                        'status' => 'completed',
                        'completed_at' => now()->subHours(rand(1, 48)),
                    ]);

                    $assessmentsToRecalc[] = $assessment;
                }

                // 2. Fill out Group Sync answers for this participant (3 questions)
                foreach ($groupSyncQuestions as $gqIdx => $gQuestion) {
                    // Varied team sync alignment score across participants
                    $syncScore = max(1, min(10, (int) round(6 + sin(($assessorIdx + $gqIdx) * 2) * 3 + rand(-1, 1))));

                    GroupSyncAnswer::updateOrCreate(
                        [
                            'survey_id' => $survey->id,
                            'user_id' => $assessor->id,
                            'question_id' => $gQuestion->id,
                        ],
                        [
                            'score' => $syncScore,
                        ]
                    );
                }
            }

            // Recalculate scores, percentages, and categories for all completed assessments
            foreach ($assessmentsToRecalc as $assessment) {
                $scoreService->calculateAssessmentScore($assessment);
            }
        });

        $this->command?->info('Successfully seeded all 576 assessment responses and Group Sync answers for Falcon Group with a full mixed score distribution.');
    }
}
