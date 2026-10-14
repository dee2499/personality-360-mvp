<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\Company;
use App\Models\Survey;
use App\Models\User;
use App\Services\AssessmentGenerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyManagerRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_access_admin_dashboard_scoped_to_their_company(): void
    {
        $falcon = Company::factory()->create(['name' => 'Falcon Group']);
        $acme = Company::factory()->create(['name' => 'Acme Corp']);

        $manager = User::factory()->manager()->create([
            'company_id' => $falcon->id,
            'name' => 'Falcon Manager',
        ]);

        $admin = User::factory()->admin()->create();

        $falconSurvey = Survey::create([
            'company_id' => $falcon->id,
            'title' => 'Falcon Q3 Assessment',
            'created_by' => $admin->id,
            'status' => 'draft',
        ]);

        $acmeSurvey = Survey::create([
            'company_id' => $acme->id,
            'title' => 'Acme Annual Review',
            'created_by' => $admin->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($manager)->get(route('admin.dashboard'));
        $response->assertOk();

        // Should see Manager Console and Falcon data
        $response->assertSee('Manager Console');
        $response->assertSee('Falcon Q3 Assessment');

        // Should not see Acme data
        $response->assertDontSee('Acme Annual Review');

        // Should not see admin-only Score Categories
        $response->assertDontSee(route('admin.categories.index'));
    }

    public function test_manager_visiting_companies_index_is_redirected_to_their_own_company(): void
    {
        $falcon = Company::factory()->create(['name' => 'Falcon Group']);
        $manager = User::factory()->manager()->create([
            'company_id' => $falcon->id,
        ]);

        $response = $this->actingAs($manager)->get(route('admin.companies.index'));
        $response->assertRedirect(route('admin.companies.show', $falcon));
    }

    public function test_manager_cannot_view_or_manipulate_another_company(): void
    {
        $falcon = Company::factory()->create(['name' => 'Falcon Group']);
        $acme = Company::factory()->create(['name' => 'Acme Corp']);

        $manager = User::factory()->manager()->create([
            'company_id' => $falcon->id,
        ]);

        // Attempt to view other company
        $this->actingAs($manager)
            ->get(route('admin.companies.show', $acme))
            ->assertForbidden();

        // Attempt to create company
        $this->actingAs($manager)
            ->get(route('admin.companies.create'))
            ->assertForbidden();

        // Attempt to edit company
        $this->actingAs($manager)
            ->get(route('admin.companies.edit', $acme))
            ->assertForbidden();

        // Attempt to delete company
        $this->actingAs($manager)
            ->delete(route('admin.companies.destroy', $acme))
            ->assertForbidden();
    }

    public function test_manager_surveys_are_scoped_to_their_company(): void
    {
        $falcon = Company::factory()->create(['name' => 'Falcon Group']);
        $acme = Company::factory()->create(['name' => 'Acme Corp']);

        $manager = User::factory()->manager()->create([
            'company_id' => $falcon->id,
        ]);

        $admin = User::factory()->admin()->create();

        $falconSurvey = Survey::create([
            'company_id' => $falcon->id,
            'title' => 'Falcon Survey Alpha',
            'created_by' => $admin->id,
            'status' => 'draft',
        ]);

        $acmeSurvey = Survey::create([
            'company_id' => $acme->id,
            'title' => 'Acme Survey Secret',
            'created_by' => $admin->id,
            'status' => 'draft',
        ]);

        // List surveys
        $response = $this->actingAs($manager)->get(route('admin.surveys.index'));
        $response->assertOk();
        $response->assertSee('Falcon Survey Alpha');
        $response->assertDontSee('Acme Survey Secret');

        // Access falcon survey details
        $this->actingAs($manager)
            ->get(route('admin.surveys.show', $falconSurvey))
            ->assertOk();

        // Forbidden on other company's survey
        $this->actingAs($manager)
            ->get(route('admin.surveys.show', $acmeSurvey))
            ->assertForbidden();
    }

    public function test_manager_cannot_access_score_categories_crud(): void
    {
        $falcon = Company::factory()->create(['name' => 'Falcon Group']);
        $manager = User::factory()->manager()->create([
            'company_id' => $falcon->id,
        ]);

        $this->actingAs($manager)
            ->get(route('admin.categories.index'))
            ->assertForbidden();
    }

    public function test_admin_can_assign_manager_role_to_employee(): void
    {
        $falcon = Company::factory()->create(['name' => 'Falcon Group']);
        $admin = User::factory()->admin()->create();

        $employee = User::factory()->participant()->create([
            'company_id' => $falcon->id,
            'name' => 'Jane Falcon',
        ]);

        $this->assertFalse($employee->isManager());

        // Admin promotes employee to manager
        $response = $this->actingAs($admin)->post(
            route('admin.companies.employees.role', [$falcon, $employee]),
            ['role' => 'manager']
        );

        $response->assertRedirect();
        $employee->refresh();
        $this->assertTrue($employee->isManager());

        // Admin demotes back to participant
        $response = $this->actingAs($admin)->post(
            route('admin.companies.employees.role', [$falcon, $employee]),
            ['role' => 'participant']
        );

        $response->assertRedirect();
        $employee->refresh();
        $this->assertFalse($employee->isManager());
    }

    public function test_admin_can_invite_new_employee_directly_as_manager(): void
    {
        $falcon = Company::factory()->create(['name' => 'Falcon Group']);
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(
            route('admin.companies.invite', $falcon),
            [
                'name' => 'New Company Leader',
                'email' => 'leader@falcon.com',
                'role' => 'manager',
            ]
        );

        $response->assertRedirect();
        $createdUser = User::where('email', 'leader@falcon.com')->first();
        $this->assertNotNull($createdUser);
        $this->assertTrue($createdUser->isManager());
        $this->assertEquals($falcon->id, $createdUser->company_id);
    }

    public function test_manager_cannot_change_employee_roles(): void
    {
        $falcon = Company::factory()->create(['name' => 'Falcon Group']);
        $manager = User::factory()->manager()->create([
            'company_id' => $falcon->id,
        ]);

        $employee = User::factory()->participant()->create([
            'company_id' => $falcon->id,
        ]);

        $response = $this->actingAs($manager)->post(
            route('admin.companies.employees.role', [$falcon, $employee]),
            ['role' => 'manager']
        );

        $response->assertForbidden();
        $employee->refresh();
        $this->assertFalse($employee->isManager());
    }

    public function test_manager_people_directory_is_scoped_to_company(): void
    {
        $falcon = Company::factory()->create(['name' => 'Falcon Group']);
        $acme = Company::factory()->create(['name' => 'Acme Corp']);

        $manager = User::factory()->manager()->create([
            'company_id' => $falcon->id,
        ]);

        $falconEmployee = User::factory()->participant()->create([
            'company_id' => $falcon->id,
            'name' => 'Alice Falcon',
        ]);

        $acmeEmployee = User::factory()->participant()->create([
            'company_id' => $acme->id,
            'name' => 'Bob Acme',
        ]);

        $response = $this->actingAs($manager)->get(route('admin.people.index'));
        $response->assertOk();
        $response->assertSee('Alice Falcon');
        $response->assertDontSee('Bob Acme');

        // Cannot view Bob Acme's 360 profile
        $this->actingAs($manager)
            ->get(route('admin.people.show', $acmeEmployee))
            ->assertForbidden();

        // Can view Alice Falcon's 360 profile
        $this->actingAs($manager)
            ->get(route('admin.people.show', $falconEmployee))
            ->assertOk();
    }

    public function test_user_changed_to_manager_is_removed_from_all_surveys_and_assessments_deleted(): void
    {
        $falcon = Company::factory()->create(['name' => 'Falcon Group']);
        $admin = User::factory()->admin()->create();

        $employee1 = User::factory()->participant()->create([
            'company_id' => $falcon->id,
            'name' => 'John Participant',
        ]);
        $employee2 = User::factory()->participant()->create([
            'company_id' => $falcon->id,
            'name' => 'Jane Colleague',
        ]);

        $survey = Survey::create([
            'company_id' => $falcon->id,
            'title' => 'Falcon Survey',
            'status' => 'published',
            'created_by' => $admin->id,
        ]);

        $survey->participants()->sync([$employee1->id, $employee2->id]);
        app(AssessmentGenerationService::class)->generateForSurvey($survey);

        // Initially 2 participants => 4 assessments
        $this->assertEquals(2, $survey->participants()->count());
        $this->assertEquals(4, Assessment::where('survey_id', $survey->id)->count());

        // Admin changes employee1 to manager
        $response = $this->actingAs($admin)->post(
            route('admin.companies.employees.role', [$falcon, $employee1]),
            ['role' => 'manager']
        );

        $response->assertRedirect();
        $employee1->refresh();
        $this->assertTrue($employee1->isManager());

        // Manager MUST be completely removed from survey participants
        $this->assertFalse($survey->participants()->where('users.id', $employee1->id)->exists());
        $this->assertFalse($employee1->surveys()->where('surveys.id', $survey->id)->exists());
        $this->assertEquals(1, $survey->participants()->count());

        // Any assessment where manager is assessor or subject MUST be permanently deleted
        $this->assertDatabaseMissing('assessments', ['survey_id' => $survey->id, 'assessor_id' => $employee1->id]);
        $this->assertDatabaseMissing('assessments', ['survey_id' => $survey->id, 'subject_id' => $employee1->id]);

        // Remaining cohort only has self-assessment for employee2
        $this->assertEquals(1, Assessment::where('survey_id', $survey->id)->count());
    }

    public function test_manager_cannot_take_part_in_survey_or_access_taking_screens(): void
    {
        $falcon = Company::factory()->create(['name' => 'Falcon Group']);
        $manager = User::factory()->manager()->create([
            'company_id' => $falcon->id,
        ]);

        $survey = Survey::create([
            'company_id' => $falcon->id,
            'title' => 'Falcon Survey',
            'status' => 'published',
            'created_by' => $manager->id,
        ]);

        // Accessing my-assessments redirects to admin dashboard
        $indexResponse = $this->actingAs($manager)->get(route('participant.assessments.index'));
        $indexResponse->assertRedirect(route('admin.dashboard'));

        // Accessing survey taking screen is forbidden with 403
        $takeResponse = $this->actingAs($manager)->get(route('participant.surveys.take', $survey));
        $takeResponse->assertForbidden();

        // Submitting survey matrix ratings is forbidden with 403
        $submitResponse = $this->actingAs($manager)->post(route('participant.surveys.submit-matrix', $survey), [
            'answers' => [1 => [1 => 8]],
        ]);
        $submitResponse->assertForbidden();
    }

    public function test_newly_invited_manager_is_not_enrolled_in_surveys(): void
    {
        $falcon = Company::factory()->create(['name' => 'Falcon Group']);
        $admin = User::factory()->admin()->create();

        $survey = Survey::create([
            'company_id' => $falcon->id,
            'title' => 'Falcon Survey',
            'status' => 'published',
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->post(
            route('admin.companies.invite', $falcon),
            [
                'name' => 'Director Dave',
                'username' => 'directordave',
                'email' => 'dave@falcon.com',
                'role' => 'manager',
            ]
        );

        $response->assertRedirect();
        $manager = User::where('username', 'directordave')->first();
        $this->assertNotNull($manager);
        $this->assertTrue($manager->isManager());

        // Manager MUST NOT be enrolled in the company survey
        $this->assertFalse($survey->participants()->where('users.id', $manager->id)->exists());
        $this->assertDatabaseMissing('assessments', ['survey_id' => $survey->id, 'assessor_id' => $manager->id]);
        $this->assertDatabaseMissing('assessments', ['survey_id' => $survey->id, 'subject_id' => $manager->id]);
    }
}
