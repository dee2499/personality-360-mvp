<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SurveyCompanyFilterTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Company $companyA;

    protected Company $companyB;

    protected Survey $surveyA;

    protected Survey $surveyB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->companyA = Company::create([
            'name' => 'Acme Corporation',
            'contact_email' => 'contact@acme.com',
        ]);

        $this->companyB = Company::create([
            'name' => 'Stark Industries',
            'contact_email' => 'tony@stark.com',
        ]);

        $this->surveyA = Survey::create([
            'company_id' => $this->companyA->id,
            'title' => 'Acme Leadership 360',
            'description' => 'Annual leadership review for Acme',
            'status' => 'published',
            'created_by' => $this->admin->id,
        ]);

        $this->surveyB = Survey::create([
            'company_id' => $this->companyB->id,
            'title' => 'Stark Engineering 360',
            'description' => 'Engineering performance review for Stark',
            'status' => 'published',
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_surveys_index_displays_all_surveys_when_no_filter_applied(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.surveys.index'));

        $response->assertOk();
        $response->assertSee('Acme Leadership 360');
        $response->assertSee('Stark Engineering 360');
        $response->assertSee('Acme Corporation');
        $response->assertSee('Stark Industries');
    }

    public function test_admin_can_filter_surveys_by_company_name(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.surveys.index', [
            'company' => 'Acme Corporation',
        ]));

        $response->assertOk();
        $response->assertSee('Acme Leadership 360');
        $response->assertDontSee('Stark Engineering 360');
        $response->assertSee('Filtering surveys for company:');
        $response->assertSee('Acme Corporation');
    }

    public function test_admin_can_filter_surveys_by_partial_case_insensitive_company_name(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.surveys.index', [
            'company' => 'stark',
        ]));

        $response->assertOk();
        $response->assertSee('Stark Engineering 360');
        $response->assertDontSee('Acme Leadership 360');
    }

    public function test_admin_can_filter_surveys_by_company_id(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.surveys.index', [
            'company_id' => $this->companyA->id,
        ]));

        $response->assertOk();
        $response->assertSee('Acme Leadership 360');
        $response->assertDontSee('Stark Engineering 360');
    }

    public function test_admin_can_search_surveys_by_title_or_description(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.surveys.index', [
            'search' => 'Leadership',
        ]));

        $response->assertOk();
        $response->assertSee('Acme Leadership 360');
        $response->assertDontSee('Stark Engineering 360');
    }

    public function test_admin_can_search_surveys_within_selected_company(): void
    {
        // Another Acme survey
        Survey::create([
            'company_id' => $this->companyA->id,
            'title' => 'Acme Sales 360',
            'status' => 'published',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.surveys.index', [
            'company' => 'Acme Corporation',
            'search' => 'Sales',
        ]));

        $response->assertOk();
        $response->assertSee('Acme Sales 360');
        $response->assertDontSee('Acme Leadership 360');
        $response->assertDontSee('Stark Engineering 360');
    }

    public function test_survey_table_displays_company_name_prominently_in_list(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.surveys.index'));

        $response->assertOk();
        // Checks that company table header and company names exist in the table
        $response->assertSee('Company</th>', false);
        $response->assertSee('Acme Corporation');
        $response->assertSee('Stark Industries');
    }
}
