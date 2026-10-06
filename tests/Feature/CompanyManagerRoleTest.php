<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Survey;
use App\Models\User;
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
}
