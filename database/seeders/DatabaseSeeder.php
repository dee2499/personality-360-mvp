<?php

namespace Database\Seeders;

use App\Models\Assessment;
use App\Models\AssessmentAnswer;
use App\Models\Question;
use App\Models\Survey;
use App\Models\User;
use App\Services\AssessmentGenerationService;
use App\Services\AssessmentScoreService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $generationService = app(AssessmentGenerationService::class);
        $scoreService = app(AssessmentScoreService::class);

        // 1. Create Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // 2. Create Participants (Dipak, Vishy, Srini)
        $dipak = User::firstOrCreate(
            ['email' => 'dipak@example.com'],
            [
                'name' => 'Dipak',
                'password' => Hash::make('password'),
                'role' => 'participant',
            ]
        );

        $vishy = User::firstOrCreate(
            ['email' => 'vishy@example.com'],
            [
                'name' => 'Vishy',
                'password' => Hash::make('password'),
                'role' => 'participant',
            ]
        );

        $srini = User::firstOrCreate(
            ['email' => 'srini@example.com'],
            [
                'name' => 'Srini',
                'password' => Hash::make('password'),
                'role' => 'participant',
            ]
        );

        $participants = collect([$dipak, $vishy, $srini]);

        // 3. Create Survey
        $survey = Survey::firstOrCreate(
            ['title' => 'Personality Assessment 2026'],
            [
                'description' => 'Comprehensive 360-degree personality and self-assessment survey. Rate each person on 11 behavioral competencies on a scale of 1 to 10.',
                'status' => 'published',
                'created_by' => $admin->id,
                'published_at' => now(),
            ]
        );

        // 4. Create 11 Standard Questions (Section 42 of spec)
        $questionTexts = [
            'How effectively does this person communicate with others?',
            'How well does this person work in a team?',
            'How confidently does this person make decisions?',
            'How adaptable is this person to change?',
            'How well does this person handle responsibility?',
            'How effectively does this person solve problems?',
            'How well does this person listen to others?',
            'How effectively does this person manage conflict?',
            'How consistently does this person demonstrate empathy?',
            'How effectively does this person take initiative?',
            'How reliable is this person when working toward goals?',
        ];

        $questions = collect();
        foreach ($questionTexts as $index => $text) {
            $question = Question::firstOrCreate(
                [
                    'survey_id' => $survey->id,
                    'sort_order' => $index + 1,
                ],
                [
                    'question_text' => $text,
                    'is_active' => true,
                ]
            );
            $questions->push($question);
        }

        // 5. Attach Participants to Survey
        $survey->participants()->sync($participants->pluck('id'));

        // 6. Generate 3 x 3 = 9 Assessments Matrix
        $generationService->generateForSurvey($survey);

        // 7. Seed Assessment Answers matching Section 6 & 39 of MVP Specification:
        // Dipak -> Dipak = 72
        // Vishy -> Dipak = 81
        // Srini -> Dipak = 65
        // Combined for Dipak = 218 / 330 = 66.06% Lemon!

        // Assessor: Dipak -> Subject: Dipak (72 points: sum = 72)
        $dipakToDipak = Assessment::where([
            'survey_id' => $survey->id,
            'assessor_id' => $dipak->id,
            'subject_id' => $dipak->id,
        ])->first();

        if ($dipakToDipak) {
            $scores = [7, 6, 7, 6, 7, 6, 7, 7, 6, 6, 7]; // sum = 72
            foreach ($questions as $idx => $q) {
                AssessmentAnswer::updateOrCreate(
                    ['assessment_id' => $dipakToDipak->id, 'question_id' => $q->id],
                    ['score' => $scores[$idx]]
                );
            }
            $dipakToDipak->update([
                'status' => 'completed',
                'completed_at' => now()->subHours(5),
            ]);
            $scoreService->calculateAssessmentScore($dipakToDipak);
        }

        // Assessor: Vishy -> Subject: Dipak (81 points: sum = 81)
        $vishyToDipak = Assessment::where([
            'survey_id' => $survey->id,
            'assessor_id' => $vishy->id,
            'subject_id' => $dipak->id,
        ])->first();

        if ($vishyToDipak) {
            $scores = [8, 7, 6, 9, 5, 8, 7, 6, 9, 8, 8]; // sum = 81
            foreach ($questions as $idx => $q) {
                AssessmentAnswer::updateOrCreate(
                    ['assessment_id' => $vishyToDipak->id, 'question_id' => $q->id],
                    ['score' => $scores[$idx]]
                );
            }
            $vishyToDipak->update([
                'status' => 'completed',
                'completed_at' => now()->subHours(3),
            ]);
            $scoreService->calculateAssessmentScore($vishyToDipak);
        }

        // Assessor: Srini -> Subject: Dipak (65 points: sum = 65)
        $sriniToDipak = Assessment::where([
            'survey_id' => $survey->id,
            'assessor_id' => $srini->id,
            'subject_id' => $dipak->id,
        ])->first();

        if ($sriniToDipak) {
            $scores = [6, 6, 6, 6, 6, 6, 6, 6, 6, 5, 6]; // sum = 65
            foreach ($questions as $idx => $q) {
                AssessmentAnswer::updateOrCreate(
                    ['assessment_id' => $sriniToDipak->id, 'question_id' => $q->id],
                    ['score' => $scores[$idx]]
                );
            }
            $sriniToDipak->update([
                'status' => 'completed',
                'completed_at' => now()->subHour(),
            ]);
            $scoreService->calculateAssessmentScore($sriniToDipak);
        }

        // Also seed Vishy's and Srini's evaluations for realistic dashboard data:
        // Vishy -> Vishy (Self): score 77
        $vishyToVishy = Assessment::where([
            'survey_id' => $survey->id,
            'assessor_id' => $vishy->id,
            'subject_id' => $vishy->id,
        ])->first();
        if ($vishyToVishy) {
            $scores = [7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7]; // sum = 77
            foreach ($questions as $idx => $q) {
                AssessmentAnswer::updateOrCreate(
                    ['assessment_id' => $vishyToVishy->id, 'question_id' => $q->id],
                    ['score' => $scores[$idx]]
                );
            }
            $vishyToVishy->update([
                'status' => 'completed',
                'completed_at' => now()->subHours(2),
            ]);
            $scoreService->calculateAssessmentScore($vishyToVishy);
        }

        // Dipak -> Vishy: score 84
        $dipakToVishy = Assessment::where([
            'survey_id' => $survey->id,
            'assessor_id' => $dipak->id,
            'subject_id' => $vishy->id,
        ])->first();
        if ($dipakToVishy) {
            $scores = [8, 8, 8, 8, 7, 8, 7, 8, 7, 7, 8]; // sum = 84
            foreach ($questions as $idx => $q) {
                AssessmentAnswer::updateOrCreate(
                    ['assessment_id' => $dipakToVishy->id, 'question_id' => $q->id],
                    ['score' => $scores[$idx]]
                );
            }
            $dipakToVishy->update([
                'status' => 'completed',
                'completed_at' => now()->subHours(4),
            ]);
            $scoreService->calculateAssessmentScore($dipakToVishy);
        }

        // Srini -> Srini (Self): score 75
        $sriniToSrini = Assessment::where([
            'survey_id' => $survey->id,
            'assessor_id' => $srini->id,
            'subject_id' => $srini->id,
        ])->first();
        if ($sriniToSrini) {
            $scores = [7, 7, 7, 6, 7, 7, 7, 7, 6, 7, 7]; // sum = 75
            foreach ($questions as $idx => $q) {
                AssessmentAnswer::updateOrCreate(
                    ['assessment_id' => $sriniToSrini->id, 'question_id' => $q->id],
                    ['score' => $scores[$idx]]
                );
            }
            $sriniToSrini->update([
                'status' => 'completed',
                'completed_at' => now()->subMinutes(30),
            ]);
            $scoreService->calculateAssessmentScore($sriniToSrini);
        }

        // Dipak -> Srini: in_progress
        $dipakToSrini = Assessment::where([
            'survey_id' => $survey->id,
            'assessor_id' => $dipak->id,
            'subject_id' => $srini->id,
        ])->first();
        if ($dipakToSrini) {
            $dipakToSrini->update([
                'status' => 'in_progress',
                'started_at' => now()->subMinutes(15),
            ]);
            // answered 4 of 11 questions
            AssessmentAnswer::updateOrCreate(['assessment_id' => $dipakToSrini->id, 'question_id' => $questions[0]->id], ['score' => 8]);
            AssessmentAnswer::updateOrCreate(['assessment_id' => $dipakToSrini->id, 'question_id' => $questions[1]->id], ['score' => 7]);
            AssessmentAnswer::updateOrCreate(['assessment_id' => $dipakToSrini->id, 'question_id' => $questions[2]->id], ['score' => 9]);
            AssessmentAnswer::updateOrCreate(['assessment_id' => $dipakToSrini->id, 'question_id' => $questions[3]->id], ['score' => 8]);
        }
    }
}
