<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Survey;
use App\Models\User;
use App\Services\AssessmentCategoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SurveyExportPdfTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        AssessmentCategoryService::clearCache();
    }

    public function test_admin_can_access_survey_export_pdf(): void
    {
        $company = Company::factory()->create(['name' => 'Acme Corporation']);
        $admin = User::factory()->create(['role' => 'admin', 'company_id' => $company->id]);
        $survey = Survey::create([
            'company_id' => $company->id,
            'title' => 'Leadership Team Sync Assessment',
            'status' => 'published',
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.surveys.export-pdf', $survey));

        $response->assertStatus(200);
        $response->assertSee('Executive Team ChangeQuo & Sync Report', false);
        $response->assertSee('Leadership Team Sync Assessment');
        $response->assertSee('Acme Corporation');
        $response->assertSee('Save / Download PDF');
    }

    public function test_manager_can_access_own_company_survey_export_pdf(): void
    {
        $company = Company::factory()->create(['name' => 'Beta Tech']);
        $manager = User::factory()->create(['role' => 'manager', 'company_id' => $company->id]);
        $survey = Survey::create([
            'company_id' => $company->id,
            'title' => 'Beta Team Alignment Survey',
            'status' => 'published',
            'created_by' => $manager->id,
        ]);

        $response = $this->actingAs($manager)->get(route('admin.surveys.export-pdf', $survey));

        $response->assertStatus(200);
        $response->assertSee('Executive Team ChangeQuo & Sync Report', false);
        $response->assertSee('Beta Team Alignment Survey');
    }

    public function test_manager_cannot_access_other_company_survey_export_pdf(): void
    {
        $companyA = Company::factory()->create(['name' => 'Company A']);
        $companyB = Company::factory()->create(['name' => 'Company B']);
        $admin = User::factory()->create(['role' => 'admin', 'company_id' => $companyA->id]);
        $managerA = User::factory()->create(['role' => 'manager', 'company_id' => $companyA->id]);
        $surveyB = Survey::create([
            'company_id' => $companyB->id,
            'title' => 'Company B Survey',
            'status' => 'published',
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($managerA)->get(route('admin.surveys.export-pdf', $surveyB));

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_export_pdf(): void
    {
        $company = Company::factory()->create();
        $admin = User::factory()->create(['role' => 'admin', 'company_id' => $company->id]);
        $survey = Survey::create([
            'company_id' => $company->id,
            'title' => 'Secret Survey',
            'status' => 'published',
            'created_by' => $admin->id,
        ]);

        $response = $this->get(route('admin.surveys.export-pdf', $survey));

        $response->assertRedirect(route('login'));
    }
}
