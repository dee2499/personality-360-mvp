<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Question;
use App\Models\Survey;
use App\Models\User;
use App\Services\AssessmentCategoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamCQSyncAndCompanyDetailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        AssessmentCategoryService::clearCache();
    }

    protected function createCompanyWithSurveyAndEmployees(): array
    {
        $company = Company::factory()->create([
            'name' => 'Acme Corporation',
        ]);

        $admin = User::factory()->create([
            'role' => 'admin',
            'company_id' => $company->id,
        ]);

        $employee1 = User::factory()->create([
            'name' => 'Alice Worker',
            'role' => 'participant',
            'company_id' => $company->id,
        ]);

        $employee2 = User::factory()->create([
            'name' => 'Bob Worker',
            'role' => 'participant',
            'company_id' => $company->id,
        ]);

        $survey = Survey::create([
            'company_id' => $company->id,
            'title' => 'Leadership & Team Performance 360 Survey',
            'description' => 'Comprehensive team 360 survey',
            'status' => 'published',
            'published_at' => now(),
            'created_by' => $admin->id,
        ]);

        $survey->participants()->attach([$employee1->id, $employee2->id]);

        // Create standard questions
        for ($i = 1; $i <= 11; $i++) {
            Question::create([
                'survey_id' => $survey->id,
                'question_text' => "Question {$i}",
                'peer_question_text' => "Peer Question {$i}",
                'type' => 'scale',
                'dimension' => 'Agility',
                'sort_order' => $i,
            ]);
        }

        // Create group sync questions (See, Agree, Act)
        Question::create([
            'survey_id' => $survey->id,
            'question_text' => 'See Together: Shared understanding of the change',
            'peer_question_text' => 'See Together',
            'type' => 'group_sync',
            'dimension' => 'See Together',
            'sort_order' => 12,
        ]);
        Question::create([
            'survey_id' => $survey->id,
            'question_text' => 'Agree Together: Common direction and priorities',
            'peer_question_text' => 'Agree Together',
            'type' => 'group_sync',
            'dimension' => 'Agree Together',
            'sort_order' => 13,
        ]);
        Question::create([
            'survey_id' => $survey->id,
            'question_text' => 'Act Together: Collective commitment and action',
            'peer_question_text' => 'Act Together',
            'type' => 'group_sync',
            'dimension' => 'Act Together',
            'sort_order' => 14,
        ]);

        return [$company, $admin, $survey, $employee1, $employee2];
    }

    public function test_company_detail_page_displays_team_cq_sync_intelligence_hub_and_all_buttons(): void
    {
        [$company, $admin, $survey] = $this->createCompanyWithSurveyAndEmployees();

        $response = $this->actingAs($admin)->get(route('admin.companies.show', $company));

        $response->assertStatus(200);

        // Header & Hub titles
        $response->assertSee('Team CQ Sync');
        $response->assertSee('Cohort Intelligence');
        $response->assertSee('Team Intelligence');
        $response->assertSee($survey->title);

        // All Team Action Buttons requested by user
        $response->assertSee(route('admin.companies.team-sync', ['company' => $company, 'survey_id' => $survey->id]));
        $response->assertSee('Team Report');
        $response->assertSee('Team CQ Report');

        $response->assertSee('Company 360° Benchmark');
        $response->assertSee('Employees of '.$company->name);
    }

    public function test_admin_can_view_executive_team_cq_sync_report_via_company_route(): void
    {
        [$company, $admin, $survey] = $this->createCompanyWithSurveyAndEmployees();

        $response = $this->actingAs($admin)->get(route('admin.companies.team-sync', [
            'company' => $company,
            'survey_id' => $survey->id,
        ]));

        $response->assertStatus(200);

        // Core Document Elements matching media_1791104045986.jpg
        $response->assertSee('Team Report');
        $response->assertSee('How well is your team aligned to see, agree and act on change together?');
        $response->assertSee('Unlocking Possibilities');
        $response->assertSee('Confidential');
        $response->assertSee('Team Size');
        $response->assertSee('Overall CQ Sync Score');
        $response->assertSee('Expected / Benchmark');
        $response->assertSee('8.0');
        $response->assertSee('Gap to Benchmark');

        // 3 Visual Middle Panels
        $response->assertSee('Team CQ Sync Maturity Level');
        $response->assertSee('Team CQ Sync Growth Journey');
        $response->assertSee('Team View across the 3 CQ Sync Dimensions');
        $response->assertSee('See Together');
        $response->assertSee('Agree Together');
        $response->assertSee('Act Together');

        // 3 Bottom Commentary Panels
        $response->assertSee('Key Insights');
        $response->assertSee('What This Means');
        $response->assertSee('Positive Foundation');
        $response->assertSee('Execution Gap');
        $response->assertSee('Opportunity');
        $response->assertSee('Top Recommendations');
        $response->assertSee('Facilitate Alignment Workshops');
        $response->assertSee('Enable Open Team Dialogues');
        $response->assertSee('Define Clear Collective Commitments');
        $response->assertSee('Track Progress Regularly');
    }

    public function test_admin_can_view_executive_team_cq_sync_report_via_survey_route(): void
    {
        [$company, $admin, $survey] = $this->createCompanyWithSurveyAndEmployees();

        $response = $this->actingAs($admin)->get(route('admin.surveys.team-sync', $survey));

        $response->assertStatus(200);
        $response->assertSee('Team Report');
        $response->assertSee($survey->title);
        $response->assertSee('Print Report');
    }

    public function test_non_admin_cannot_access_team_cq_sync_routes(): void
    {
        [$company, $admin, $survey, $employee] = $this->createCompanyWithSurveyAndEmployees();

        $response = $this->actingAs($employee)->get(route('admin.companies.team-sync', $company));
        $response->assertStatus(403);

        $responseSurvey = $this->actingAs($employee)->get(route('admin.surveys.team-sync', $survey));
        $responseSurvey->assertStatus(403);
    }
}
