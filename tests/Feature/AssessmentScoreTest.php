<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\AssessmentAnswer;
use App\Models\Question;
use App\Models\Survey;
use App\Models\User;
use App\Services\AssessmentCategoryService;
use App\Services\AssessmentGenerationService;
use App\Services\AssessmentScoreService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessmentScoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_service_calculates_boundaries_correctly(): void
    {
        $categoryService = new AssessmentCategoryService;

        // Boundary tests per Change Quotient (CQ) specifications:
        // 0-20 -> Resistor
        $this->assertEquals('Resistor', $categoryService->getCategory(0.0));
        $this->assertEquals('Resistor', $categoryService->getCategory(15.5));
        $this->assertEquals('Resistor', $categoryService->getCategory(20.0));

        // >20-40 -> Follower
        $this->assertEquals('Follower', $categoryService->getCategory(20.01));
        $this->assertEquals('Follower', $categoryService->getCategory(35.0));
        $this->assertEquals('Follower', $categoryService->getCategory(40.0));

        // >40-60 -> Supporter
        $this->assertEquals('Supporter', $categoryService->getCategory(40.01));
        $this->assertEquals('Supporter', $categoryService->getCategory(55.0));
        $this->assertEquals('Supporter', $categoryService->getCategory(60.0));

        // >60-80 -> Initiator
        $this->assertEquals('Initiator', $categoryService->getCategory(60.01));
        $this->assertEquals('Initiator', $categoryService->getCategory(75.0));
        $this->assertEquals('Initiator', $categoryService->getCategory(80.0));

        // >80-100 -> Achiever
        $this->assertEquals('Achiever', $categoryService->getCategory(80.01));
        $this->assertEquals('Achiever', $categoryService->getCategory(95.0));
        $this->assertEquals('Achiever', $categoryService->getCategory(100.0));
    }

    public function test_assessment_generation_creates_n_squared_matrix(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $survey = Survey::create([
            'title' => 'Test 360',
            'status' => 'published',
            'created_by' => $admin->id,
        ]);

        $participants = User::factory()->count(3)->create(['role' => 'participant']);
        $survey->participants()->sync($participants->pluck('id'));

        $generationService = app(AssessmentGenerationService::class);
        $count = $generationService->generateForSurvey($survey);

        $this->assertEquals(9, $count);
        $this->assertDatabaseCount('assessments', 9);

        // Verify each participant evaluates themselves and both peers
        foreach ($participants as $assessor) {
            foreach ($participants as $subject) {
                $this->assertDatabaseHas('assessments', [
                    'survey_id' => $survey->id,
                    'assessor_id' => $assessor->id,
                    'subject_id' => $subject->id,
                ]);
            }
        }
    }

    public function test_score_calculation_matches_exact_percentages(): void
    {
        $scoreService = app(AssessmentScoreService::class);

        // Test 72 / 110 = 65.45%
        $percentage = $scoreService->calculatePercentage(72, 110);
        $this->assertEquals(65.45, $percentage);

        // Test 81 / 110 = 73.64%
        $percentage2 = $scoreService->calculatePercentage(81, 110);
        $this->assertEquals(73.64, $percentage2);

        // Test 65 / 110 = 59.09%
        $percentage3 = $scoreService->calculatePercentage(65, 110);
        $this->assertEquals(59.09, $percentage3);
    }

    public function test_subject_combined_score_matches_dipak_specification_example(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $survey = Survey::create([
            'title' => 'Personality Assessment 2026',
            'status' => 'published',
            'created_by' => $admin->id,
        ]);

        $dipak = User::factory()->create(['name' => 'Dipak', 'role' => 'participant']);
        $vishy = User::factory()->create(['name' => 'Vishy', 'role' => 'participant']);
        $srini = User::factory()->create(['name' => 'Srini', 'role' => 'participant']);

        $survey->participants()->sync([$dipak->id, $vishy->id, $srini->id]);

        $generationService = app(AssessmentGenerationService::class);
        $generationService->generateForSurvey($survey);

        $questions = [];
        for ($i = 1; $i <= 11; $i++) {
            $questions[] = Question::create([
                'survey_id' => $survey->id,
                'question_text' => "Question $i",
                'sort_order' => $i,
            ]);
        }

        $scoreService = app(AssessmentScoreService::class);

        // 1. Dipak -> Dipak = 72
        $a1 = Assessment::where(['survey_id' => $survey->id, 'assessor_id' => $dipak->id, 'subject_id' => $dipak->id])->first();
        $scores1 = [7, 6, 7, 6, 7, 6, 7, 7, 6, 6, 7]; // sum = 72
        foreach ($questions as $idx => $q) {
            AssessmentAnswer::create(['assessment_id' => $a1->id, 'question_id' => $q->id, 'score' => $scores1[$idx]]);
        }
        $a1->update(['status' => 'completed', 'completed_at' => now()]);
        $scoreService->calculateAssessmentScore($a1);

        // 2. Vishy -> Dipak = 81
        $a2 = Assessment::where(['survey_id' => $survey->id, 'assessor_id' => $vishy->id, 'subject_id' => $dipak->id])->first();
        $scores2 = [8, 7, 6, 9, 5, 8, 7, 6, 9, 8, 8]; // sum = 81
        foreach ($questions as $idx => $q) {
            AssessmentAnswer::create(['assessment_id' => $a2->id, 'question_id' => $q->id, 'score' => $scores2[$idx]]);
        }
        $a2->update(['status' => 'completed', 'completed_at' => now()]);
        $scoreService->calculateAssessmentScore($a2);

        // 3. Srini -> Dipak = 65
        $a3 = Assessment::where(['survey_id' => $survey->id, 'assessor_id' => $srini->id, 'subject_id' => $dipak->id])->first();
        $scores3 = [6, 6, 6, 6, 6, 6, 6, 6, 6, 5, 6]; // sum = 65
        foreach ($questions as $idx => $q) {
            AssessmentAnswer::create(['assessment_id' => $a3->id, 'question_id' => $q->id, 'score' => $scores3[$idx]]);
        }
        $a3->update(['status' => 'completed', 'completed_at' => now()]);
        $scoreService->calculateAssessmentScore($a3);

        // Calculate combined score for Dipak
        $metrics = $scoreService->calculateSubjectCombinedScore($dipak, $survey);

        $this->assertEquals(3, $metrics['completed_count']);
        $this->assertEquals(3, $metrics['total_count']);
        $this->assertEquals(100.0, $metrics['completion_rate']);
        $this->assertEquals(218, $metrics['combined_score']); // 72 + 81 + 65
        $this->assertEquals(330, $metrics['combined_max_score']); // 3 * 110
        $this->assertEquals(66.06, $metrics['percentage']); // 218 / 330 * 100
        $this->assertEquals('Initiator', $metrics['category']);
    }

    public function test_people_profile_and_participant_portal_list_pending_surveys(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $participant = User::factory()->create(['name' => 'Dipak', 'role' => 'participant']);

        // Survey 1 (Completed)
        $survey1 = Survey::create([
            'title' => 'Survey Alpha 2026',
            'status' => 'published',
            'created_by' => $admin->id,
        ]);
        $survey1->participants()->attach($participant->id);
        $q1 = Question::create(['survey_id' => $survey1->id, 'question_text' => 'Q1', 'sort_order' => 1]);
        $a1 = Assessment::create([
            'survey_id' => $survey1->id,
            'assessor_id' => $participant->id,
            'subject_id' => $participant->id,
            'status' => 'completed',
            'total_score' => 10,
            'max_score' => 10,
            'percentage' => 100.0,
            'category' => 'Cucumber',
        ]);
        AssessmentAnswer::create(['assessment_id' => $a1->id, 'question_id' => $q1->id, 'score' => 10]);

        // Survey 2 (Pending)
        $survey2 = Survey::create([
            'title' => 'Survey Beta 2026',
            'status' => 'published',
            'created_by' => $admin->id,
        ]);
        $survey2->participants()->attach($participant->id);
        $q2 = Question::create(['survey_id' => $survey2->id, 'question_text' => 'Q2', 'sort_order' => 1]);
        $a2 = Assessment::create([
            'survey_id' => $survey2->id,
            'assessor_id' => $participant->id,
            'subject_id' => $participant->id,
            'status' => 'pending',
            'total_score' => 0,
            'max_score' => 10,
            'percentage' => 0.0,
        ]);

        // 1. Admin User Details (/admin/people/{participant})
        $responseAdmin = $this->actingAs($admin)->get(route('admin.people.show', $participant));
        $responseAdmin->assertOk();
        $responseAdmin->assertSee('Assigned Surveys');
        $responseAdmin->assertSee('Survey Alpha 2026');
        $responseAdmin->assertSee('Survey Beta 2026');
        $responseAdmin->assertSee('Pending Survey');
        $responseAdmin->assertSee('Completed');

        // 2. Participant Portal (/my-assessments)
        $responseParticipant = $this->actingAs($participant)->get(route('participant.assessments.index'));
        $responseParticipant->assertOk();
        $responseParticipant->assertSee('Survey Alpha 2026');
        $responseParticipant->assertSee('Survey Beta 2026');
        $responseParticipant->assertSee('Pending Survey');
    }
}
