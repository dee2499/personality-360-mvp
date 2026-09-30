<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\Question;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessmentSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_submit_assessment_with_missing_answers(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $survey = Survey::create([
            'title' => 'Validation Survey',
            'status' => 'published',
            'created_by' => $admin->id,
        ]);

        $q1 = Question::create(['survey_id' => $survey->id, 'question_text' => 'Q1', 'sort_order' => 1]);
        $q2 = Question::create(['survey_id' => $survey->id, 'question_text' => 'Q2', 'sort_order' => 2]);

        $user = User::factory()->create(['role' => 'participant']);

        $assessment = Assessment::create([
            'survey_id' => $survey->id,
            'assessor_id' => $user->id,
            'subject_id' => $user->id,
            'status' => 'pending',
        ]);

        // Submit only Q1, leaving Q2 missing
        $response = $this->actingAs($user)->post(route('participant.assessments.submit', $assessment), [
            'answers' => [
                $q1->id => 8,
            ],
        ]);

        $response->assertSessionHasErrors(["answers.{$q2->id}"]);
        $this->assertEquals('pending', $assessment->fresh()->status);
    }

    public function test_cannot_submit_scores_outside_one_to_ten_range(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $survey = Survey::create([
            'title' => 'Range Survey',
            'status' => 'published',
            'created_by' => $admin->id,
        ]);

        $q1 = Question::create(['survey_id' => $survey->id, 'question_text' => 'Q1', 'sort_order' => 1]);
        $user = User::factory()->create(['role' => 'participant']);

        $assessment = Assessment::create([
            'survey_id' => $survey->id,
            'assessor_id' => $user->id,
            'subject_id' => $user->id,
            'status' => 'pending',
        ]);

        // Submit invalid score (11)
        $response = $this->actingAs($user)->post(route('participant.assessments.submit', $assessment), [
            'answers' => [
                $q1->id => 11,
            ],
        ]);

        $response->assertSessionHasErrors(["answers.{$q1->id}"]);
    }

    public function test_successful_submission_calculates_and_marks_completed(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $survey = Survey::create([
            'title' => 'Completion Survey',
            'status' => 'published',
            'created_by' => $admin->id,
        ]);

        $q1 = Question::create(['survey_id' => $survey->id, 'question_text' => 'Q1', 'sort_order' => 1]);
        $q2 = Question::create(['survey_id' => $survey->id, 'question_text' => 'Q2', 'sort_order' => 2]);

        $alice = User::factory()->create(['name' => 'Alice', 'role' => 'participant']);
        $bob = User::factory()->create(['name' => 'Bob', 'role' => 'participant']);

        $assessment = Assessment::create([
            'survey_id' => $survey->id,
            'assessor_id' => $alice->id,
            'subject_id' => $bob->id,
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($alice)->post(route('participant.assessments.submit', $assessment), [
            'answers' => [
                $q1->id => 9,
                $q2->id => 8,
            ],
        ]);

        $response->assertRedirect(route('participant.assessments.index'));
        $response->assertSessionHas('success');

        $assessment->refresh();
        $this->assertEquals('completed', $assessment->status);
        $this->assertEquals(17, $assessment->total_score); // 9 + 8
        $this->assertEquals(20, $assessment->max_score);   // 2 * 10
        $this->assertEquals(85.0, $assessment->percentage); // 17 / 20 * 100
        $this->assertEquals('Cucumber', $assessment->category); // >80-100% -> Cucumber
        $this->assertNotNull($assessment->completed_at);
    }
}
