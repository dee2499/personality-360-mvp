<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Survey;
use App\Models\User;
use App\Services\AssessmentGenerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessmentCompanyFilterTest extends TestCase
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

        $userA1 = User::factory()->create(['company_id' => $this->companyA->id, 'role' => 'participant']);
        $userA2 = User::factory()->create(['company_id' => $this->companyA->id, 'role' => 'participant']);
        $userB1 = User::factory()->create(['company_id' => $this->companyB->id, 'role' => 'participant']);
        $userB2 = User::factory()->create(['company_id' => $this->companyB->id, 'role' => 'participant']);

        $this->surveyA = Survey::create([
            'company_id' => $this->companyA->id,
            'title' => 'Acme Leadership 360',
            'status' => 'published',
            'created_by' => $this->admin->id,
        ]);
        $this->surveyA->participants()->attach([$userA1->id, $userA2->id]);
        app(AssessmentGenerationService::class)->generateForSurvey($this->surveyA);

        $this->surveyB = Survey::create([
            'company_id' => $this->companyB->id,
            'title' => 'Stark Defense 360',
            'status' => 'published',
            'created_by' => $this->admin->id,
        ]);
        $this->surveyB->participants()->attach([$userB1->id, $userB2->id]);
        app(AssessmentGenerationService::class)->generateForSurvey($this->surveyB);
    }

    public function test_assessments_index_displays_company_name_and_filters_by_company_id(): void
    {
        // Without filter - shows both companies
        $response = $this->actingAs($this->admin)->get(route('admin.assessments.index'));
        $response->assertOk();
        $response->assertSee('Acme Leadership 360');
        $response->assertSee('Stark Defense 360');
        $response->assertSee('Acme Corporation');
        $response->assertSee('Stark Industries');

        // Filter by Company A ID
        $resFilteredA = $this->actingAs($this->admin)->get(route('admin.assessments.index', [
            'company_id' => $this->companyA->id,
        ]));
        $resFilteredA->assertOk();
        $resFilteredA->assertSee('Acme Leadership 360');
        $resFilteredA->assertDontSee('Stark Defense 360');

        // Filter by Company B ID
        $resFilteredB = $this->actingAs($this->admin)->get(route('admin.assessments.index', [
            'company_id' => $this->companyB->id,
        ]));
        $resFilteredB->assertOk();
        $resFilteredB->assertSee('Stark Defense 360');
        $resFilteredB->assertDontSee('Acme Leadership 360');
    }

    public function test_admin_can_filter_assessments_by_company_name(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.assessments.index', [
            'company' => 'Acme Corporation',
        ]));

        $response->assertOk();
        $response->assertSee('Acme Leadership 360');
        $response->assertDontSee('Stark Defense 360');
    }
}
