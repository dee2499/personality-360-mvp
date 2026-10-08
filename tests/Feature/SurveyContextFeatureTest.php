<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SurveyContextFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_survey_create_page_contains_context_field(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.surveys.create'));

        $response->assertStatus(200);
        $response->assertSee('name="context"', false);
        $response->assertSee('Context');
        $response->assertSee('Do you see life changing around - This Person? (Respond to all names given below)');
        $response->assertDontSee('[object Object]');
    }

    public function test_admin_can_create_survey_with_context(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $company = Company::create([
            'name' => 'Falcon Group',
            'contact_email' => 'admin@falcon.com',
        ]);

        $surveyData = [
            'company_id' => $company->id,
            'title' => 'Falcon Leadership Assessment 2026',
            'description' => 'General assessment instructions.',
            'context' => 'This survey is aimed at evaluating organizational change readiness and team synergy.',
        ];

        $response = $this->actingAs($admin)->post(route('admin.surveys.store'), $surveyData);

        $survey = Survey::where('title', 'Falcon Leadership Assessment 2026')->first();
        $this->assertNotNull($survey);
        $this->assertSame('This survey is aimed at evaluating organizational change readiness and team synergy.', $survey->context);
        $response->assertRedirect(route('admin.surveys.show', $survey));
    }

    public function test_admin_can_update_survey_context(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $survey = Survey::create([
            'title' => 'Test Survey',
            'description' => 'Test Description',
            'context' => 'Initial context',
            'status' => 'published',
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.surveys.update', $survey), [
            'title' => 'Test Survey Updated',
            'description' => 'Updated Description',
            'context' => 'Updated context information',
        ]);

        $response->assertRedirect(route('admin.surveys.show', $survey));
        $survey->refresh();
        $this->assertSame('Updated context information', $survey->context);
    }

    public function test_participant_sees_context_on_my_assessments_page(): void
    {
        $company = Company::create([
            'name' => 'Falcon Group',
            'contact_email' => 'admin@falcon.com',
        ]);

        $participant = User::factory()->create([
            'role' => 'participant',
            'company_id' => $company->id,
        ]);

        $survey = Survey::create([
            'company_id' => $company->id,
            'title' => 'Falcon Q3 Evaluation',
            'description' => 'About Falcon Evaluation',
            'context' => 'Specific context explaining the purpose of this survey.',
            'status' => 'published',
            'created_by' => $participant->id,
        ]);

        $survey->participants()->attach($participant->id);

        $response = $this->actingAs($participant)->get(route('participant.assessments.index'));

        $response->assertStatus(200);
        $response->assertSee('Specific context explaining the purpose of this survey.');
        $response->assertSee('Survey Context');
    }

    public function test_admin_can_update_individual_survey_question_via_question_route(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $survey = Survey::create([
            'title' => 'Test Survey',
            'status' => 'published',
            'created_by' => $admin->id,
        ]);

        $question = $survey->questions()->create([
            'question_text' => 'Original Question Text',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(
            route('admin.surveys.questions.update', [$survey, $question]),
            ['question_text' => 'Updated Single Question Text']
        );

        $response->assertSessionHas('success');
        $question->refresh();
        $this->assertSame('Updated Single Question Text', $question->question_text);
    }

    public function test_admin_can_update_survey_questions_via_survey_edit_route(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $survey = Survey::create([
            'title' => 'Test Survey',
            'status' => 'published',
            'created_by' => $admin->id,
        ]);

        $q1 = $survey->questions()->create([
            'question_text' => 'First Question',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $q2 = $survey->questions()->create([
            'question_text' => 'Second Question',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.surveys.update', $survey), [
            'title' => 'Test Survey Updated',
            'questions' => [
                ['id' => $q1->id, 'question_text' => 'First Question Altered'],
                ['id' => $q2->id, 'question_text' => 'Second Question Altered'],
            ],
        ]);

        $response->assertRedirect(route('admin.surveys.show', $survey));
        $q1->refresh();
        $q2->refresh();
        $this->assertSame('First Question Altered', $q1->question_text);
        $this->assertSame('Second Question Altered', $q2->question_text);
    }

    public function test_survey_edit_page_contains_questions(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $survey = Survey::create([
            'title' => 'Test Survey with Questions',
            'status' => 'published',
            'created_by' => $admin->id,
        ]);

        $survey->questions()->create([
            'question_text' => 'Unique Editable Question',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.surveys.edit', $survey));

        $response->assertStatus(200);
        $response->assertSee('Unique Editable Question');
    }
}
