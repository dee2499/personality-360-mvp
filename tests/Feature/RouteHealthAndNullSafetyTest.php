<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\AssessmentAnswer;
use App\Models\Company;
use App\Models\Question;
use App\Models\Survey;
use App\Models\User;
use App\Services\AssessmentGenerationService;
use App\Services\AssessmentScoreService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteHealthAndNullSafetyTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $participant1;

    private User $participant2;

    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Acme Corporation',
            'contact_email' => 'admin@acme.com',
            'description' => 'Test Company',
        ]);

        $this->admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'role' => 'admin',
            'company_id' => $this->company->id,
        ]);

        $this->participant1 = User::factory()->create([
            'name' => 'Alice Walker',
            'email' => 'alice@test.com',
            'role' => 'participant',
            'company_id' => $this->company->id,
        ]);

        $this->participant2 = User::factory()->create([
            'name' => 'Bob Smith',
            'email' => 'bob@test.com',
            'role' => 'participant',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_admin_dashboard_renders_with_no_surveys_or_assessments(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $response->assertOk();
        $response->assertSee('Executive Dashboard');
    }

    public function test_admin_dashboard_renders_with_active_data(): void
    {
        $survey = Survey::create([
            'title' => 'Leadership 360',
            'status' => 'published',
            'created_by' => $this->admin->id,
            'company_id' => $this->company->id,
        ]);

        $q = Question::create([
            'survey_id' => $survey->id,
            'question_text' => 'Demonstrates strong leadership?',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $survey->participants()->attach([$this->participant1->id, $this->participant2->id]);
        app(AssessmentGenerationService::class)->generateForSurvey($survey);

        $assessment = Assessment::first();
        AssessmentAnswer::create([
            'assessment_id' => $assessment->id,
            'question_id' => $q->id,
            'score' => 8,
        ]);
        $assessment->update(['status' => 'completed']);
        app(AssessmentScoreService::class)->calculateAssessmentScore($assessment);

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $response->assertOk();
        $response->assertSee('Leadership 360');
    }

    public function test_admin_dashboard_does_not_crash_when_survey_is_soft_deleted(): void
    {
        $survey = Survey::create([
            'title' => 'Culture 360',
            'status' => 'published',
            'created_by' => $this->admin->id,
            'company_id' => $this->company->id,
        ]);

        Question::create([
            'survey_id' => $survey->id,
            'question_text' => 'Promotes inclusive culture?',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $survey->participants()->attach([$this->participant1->id, $this->participant2->id]);
        app(AssessmentGenerationService::class)->generateForSurvey($survey);

        // Delete the survey via controller / model
        $survey->delete();

        // Dashboard MUST load cleanly with 200 OK and no null property error
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $response->assertOk();
    }

    public function test_admin_people_routes_render_cleanly_including_with_deleted_surveys(): void
    {
        $survey = Survey::create([
            'title' => 'People Survey 360',
            'status' => 'published',
            'created_by' => $this->admin->id,
            'company_id' => $this->company->id,
        ]);

        $q = Question::create([
            'survey_id' => $survey->id,
            'question_text' => 'Communicates clearly?',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $survey->participants()->attach([$this->participant1->id]);
        app(AssessmentGenerationService::class)->generateForSurvey($survey);

        // Check index
        $indexResponse = $this->actingAs($this->admin)->get(route('admin.people.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Alice Walker');

        // Check show
        $showResponse = $this->actingAs($this->admin)->get(route('admin.people.show', $this->participant1));
        $showResponse->assertOk();
        $showResponse->assertSee('Alice Walker');

        // Now soft delete the survey
        $survey->delete();

        $indexAfterDelete = $this->actingAs($this->admin)->get(route('admin.people.index'));
        $indexAfterDelete->assertOk();

        $showAfterDelete = $this->actingAs($this->admin)->get(route('admin.people.show', $this->participant1));
        $showAfterDelete->assertOk();
    }

    public function test_admin_assessments_matrix_and_show_render_cleanly(): void
    {
        $survey = Survey::create([
            'title' => 'Matrix Survey 360',
            'status' => 'published',
            'created_by' => $this->admin->id,
            'company_id' => $this->company->id,
        ]);

        $q = Question::create([
            'survey_id' => $survey->id,
            'question_text' => 'Solves problems proactively?',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $survey->participants()->attach([$this->participant1->id, $this->participant2->id]);
        app(AssessmentGenerationService::class)->generateForSurvey($survey);

        $assessment = Assessment::first();

        // Index page
        $res = $this->actingAs($this->admin)->get(route('admin.assessments.index'));
        $res->assertOk();
        $res->assertSee('Assessments Master Matrix');

        // Filter by survey
        $resFilter = $this->actingAs($this->admin)->get(route('admin.assessments.index', ['survey_id' => $survey->id]));
        $resFilter->assertOk();

        // Show page
        $showRes = $this->actingAs($this->admin)->get(route('admin.assessments.show', $assessment));
        $showRes->assertOk();
        $showRes->assertSee('Matrix Survey 360');
    }

    public function test_admin_companies_routes(): void
    {
        // Index
        $res = $this->actingAs($this->admin)->get(route('admin.companies.index'));
        $res->assertOk();
        $res->assertSee('Acme Corporation');

        // Create
        $res = $this->actingAs($this->admin)->get(route('admin.companies.create'));
        $res->assertOk();

        // Show
        $res = $this->actingAs($this->admin)->get(route('admin.companies.show', $this->company));
        $res->assertOk();
        $res->assertSee('Acme Corporation');

        // Edit
        $res = $this->actingAs($this->admin)->get(route('admin.companies.edit', $this->company));
        $res->assertOk();
    }

    public function test_participant_dashboard_and_taking_surveys(): void
    {
        $survey = Survey::create([
            'title' => 'Participant Test Survey',
            'status' => 'published',
            'created_by' => $this->admin->id,
            'company_id' => $this->company->id,
        ]);

        $q = Question::create([
            'survey_id' => $survey->id,
            'question_text' => 'Works well under pressure?',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $survey->participants()->attach([$this->participant1->id]);
        app(AssessmentGenerationService::class)->generateForSurvey($survey);

        // Participant Dashboard
        $res = $this->actingAs($this->participant1)->get(route('participant.assessments.index'));
        $res->assertOk();
        $res->assertSee('Participant Test Survey');

        // Survey Take Matrix Wizard
        $takeRes = $this->actingAs($this->participant1)->get(route('participant.surveys.take', $survey));
        $takeRes->assertOk();
        $takeRes->assertSee('Participant Test Survey');
    }

    public function test_user_profile_page(): void
    {
        $resAdmin = $this->actingAs($this->admin)->get(route('profile.show'));
        $resAdmin->assertOk();
        $resAdmin->assertSee('Admin User');

        $resPart = $this->actingAs($this->participant1)->get(route('profile.show'));
        $resPart->assertOk();
        $resPart->assertSee('Alice Walker');
    }

    public function test_survey_delete_action_via_controller_cascades_safely(): void
    {
        $survey = Survey::create([
            'title' => 'Survey To Delete',
            'status' => 'published',
            'created_by' => $this->admin->id,
            'company_id' => $this->company->id,
        ]);

        $q = Question::create([
            'survey_id' => $survey->id,
            'question_text' => 'Question to delete?',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $survey->participants()->attach([$this->participant1->id, $this->participant2->id]);
        app(AssessmentGenerationService::class)->generateForSurvey($survey);

        $assessments = Assessment::where('survey_id', $survey->id)->get();
        $this->assertNotEmpty($assessments);

        // Add answers to the assessments
        foreach ($assessments as $a) {
            AssessmentAnswer::create([
                'assessment_id' => $a->id,
                'question_id' => $q->id,
                'score' => 9,
            ]);
        }

        $this->assertDatabaseHas('surveys', ['id' => $survey->id]);
        $this->assertGreaterThan(0, AssessmentAnswer::where('question_id', $q->id)->count());

        // Call the delete route as Admin
        $delResponse = $this->actingAs($this->admin)->delete(route('admin.surveys.destroy', $survey));
        $delResponse->assertRedirect(route('admin.surveys.index'));

        // Survey should be soft deleted
        $this->assertSoftDeleted('surveys', ['id' => $survey->id]);

        // Assessments for this survey should have been cascaded/cleaned up
        $this->assertEquals(0, Assessment::where('survey_id', $survey->id)->count());

        // Answers for this survey must also be completely deleted
        $this->assertEquals(0, AssessmentAnswer::where('question_id', $q->id)->count());
        $this->assertEquals(0, AssessmentAnswer::whereIn('assessment_id', $assessments->pluck('id'))->count());

        // Admin dashboard must still be 200 OK
        $dashResponse = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $dashResponse->assertOk();
    }

    public function test_admin_assessments_show_handles_deleted_survey_gracefully(): void
    {
        $survey = Survey::create([
            'title' => 'Temp Survey',
            'status' => 'published',
            'created_by' => $this->admin->id,
            'company_id' => $this->company->id,
        ]);

        $survey->participants()->attach([$this->participant1->id]);
        app(AssessmentGenerationService::class)->generateForSurvey($survey);

        $assessment = Assessment::where('survey_id', $survey->id)->first();

        // Soft delete the survey directly in db to simulate orphaned assessment
        $survey->delete();

        // Visiting the assessment show route should abort with 404, not throw a 500 ErrorException
        $response = $this->actingAs($this->admin)->get(route('admin.assessments.show', $assessment));
        $response->assertNotFound();
    }

    public function test_admin_can_invite_employee_to_company(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.companies.invite', $this->company), [
            'name' => 'Charlie Brown',
            'email' => 'charlie@acme.com',
            'role' => 'participant',
        ]);

        $response->assertRedirect(route('admin.companies.show', $this->company));
        $this->assertDatabaseHas('users', [
            'email' => 'charlie@acme.com',
            'company_id' => $this->company->id,
        ]);

        $newUser = User::where('email', 'charlie@acme.com')->first();
        $this->assertNotNull($newUser->invitation_token);

        // Check invitation route renders for guest
        auth()->logout();
        $invitePage = $this->get(route('invitation.show', $newUser->invitation_token));
        $invitePage->assertOk();
        $invitePage->assertSee('Charlie Brown');
    }

    public function test_user_password_update(): void
    {
        $response = $this->actingAs($this->participant1)
            ->from(route('profile.show'))
            ->post(route('profile.password.update'), [
                'current_password' => 'password',
                'password' => 'NewSecurePassword123!',
                'password_confirmation' => 'NewSecurePassword123!',
            ]);

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('success');
    }
}
