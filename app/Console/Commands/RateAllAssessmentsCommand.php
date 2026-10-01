<?php

namespace App\Console\Commands;

use App\Models\Assessment;
use App\Models\AssessmentAnswer;
use App\Models\Question;
use App\Models\Survey;
use App\Services\AssessmentCategoryService;
use App\Services\AssessmentScoreService;
use Illuminate\Console\Command;

class RateAllAssessmentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assessment:rate-all {--survey= : Optional Survey ID to rate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate realistic 360 ratings for all employees across self and peer assessments';

    /**
     * Execute the console command.
     */
    public function handle(
        AssessmentCategoryService $categoryService,
        AssessmentScoreService $scoreService
    ): int {
        $surveyId = $this->option('survey');
        $survey = $surveyId ? Survey::find($surveyId) : Survey::latest()->first();

        if (! $survey) {
            $this->error('No survey found to rate.');

            return self::FAILURE;
        }

        $questions = $survey->questions()->orderBy('sort_order')->get();
        if ($questions->isEmpty()) {
            $this->error("Survey #{$survey->id} has no questions.");

            return self::FAILURE;
        }

        $participants = $survey->participants()->get();
        if ($participants->isEmpty()) {
            $this->error("Survey #{$survey->id} has no attached participants.");

            return self::FAILURE;
        }

        $this->info("Rating all {$participants->count()} employees for Survey: '{$survey->title}' (#{$survey->id})...");

        // Realistic baseline profiles for each participant (target average percentage between 60% and 92%)
        $profiles = [
            'Alex Morgan' => ['self' => 8.5, 'peer' => 8.8],
            'Brenda Vance' => ['self' => 7.5, 'peer' => 7.8],
            'Carlos Diaz' => ['self' => 7.0, 'peer' => 8.4],
            'Danielle Brooks' => ['self' => 8.2, 'peer' => 7.6],
            'Ethan Wright' => ['self' => 6.8, 'peer' => 7.2],
            'Fiona Gallagher' => ['self' => 7.9, 'peer' => 8.3],
            'George Clark' => ['self' => 6.4, 'peer' => 6.9],
            'Hannah Abbott' => ['self' => 7.6, 'peer' => 8.6],
            'Ian Malcolm' => ['self' => 8.8, 'peer' => 7.3],
            'Julia Roberts' => ['self' => 9.2, 'peer' => 9.0],
            'Kevin Bacon' => ['self' => 8.0, 'peer' => 8.2],
            'Laura Croft' => ['self' => 8.5, 'peer' => 8.5],
            'Michael Scott' => ['self' => 9.1, 'peer' => 6.5],
            'Natalie Portman' => ['self' => 8.6, 'peer' => 8.8],
            'Oliver Queen' => ['self' => 7.7, 'peer' => 7.9],
            'Rachel Green' => ['self' => 7.3, 'peer' => 7.6],
            'Samuel Jackson' => ['self' => 8.7, 'peer' => 8.4],
            'Tina Fey' => ['self' => 8.2, 'peer' => 8.6],
            'Victor Stone' => ['self' => 8.4, 'peer' => 8.2],
            'Wendy Rhoades' => ['self' => 8.9, 'peer' => 9.1],
        ];

        $assessments = Assessment::where('survey_id', $survey->id)->get();
        $totalAnswersCount = 0;
        $totalAssessmentsRated = 0;

        foreach ($assessments as $assessment) {
            $isSelf = ($assessment->assessor_id === $assessment->subject_id);
            $subjectName = $assessment->subject?->name ?? '';

            // Find target baseline
            $profile = $profiles[$subjectName] ?? ['self' => 7.5, 'peer' => 7.5];
            $baseRating = $isSelf ? $profile['self'] : $profile['peer'];

            // Clear old answers if any
            $assessment->answers()->delete();

            $totalScore = 0;
            foreach ($questions as $qIndex => $question) {
                // Add natural question variance based on question index & assessor
                $variance = sin($qIndex * 1.7 + $assessment->assessor_id * 0.9) * 0.9;
                $score = (int) round(max(1, min(10, $baseRating + $variance)));

                AssessmentAnswer::create([
                    'assessment_id' => $assessment->id,
                    'question_id' => $question->id,
                    'score' => $score,
                ]);

                $totalScore += $score;
                $totalAnswersCount++;
            }

            $maxScore = $questions->count() * 10;
            $percentage = round(($totalScore / $maxScore) * 100, 2);
            $category = $categoryService->getCategory($percentage);

            $assessment->update([
                'status' => 'completed',
                'total_score' => $totalScore,
                'max_score' => $maxScore,
                'percentage' => $percentage,
                'category' => $category,
                'completed_at' => now(),
            ]);

            $totalAssessmentsRated++;
        }

        $this->info("Successfully rated {$totalAssessmentsRated} assessments with {$totalAnswersCount} question answers!");
        $this->line('- 20 Self-Assessments completed');
        $this->line('- 380 Peer-Assessments completed');

        return self::SUCCESS;
    }
}
