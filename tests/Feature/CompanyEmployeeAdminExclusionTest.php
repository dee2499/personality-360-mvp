<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyEmployeeAdminExclusionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admins_are_excluded_from_company_employees_relationship(): void
    {
        $company = Company::factory()->create(['name' => 'Acme Corp']);

        $admin = User::factory()->create([
            'name' => 'Super Admin',
            'role' => 'admin',
            'company_id' => $company->id,
        ]);

        $employee = User::factory()->create([
            'name' => 'Jane Employee',
            'role' => 'participant',
            'company_id' => $company->id,
        ]);

        $company->refresh();

        $this->assertCount(2, $company->users);
        $this->assertCount(1, $company->employees);
        $this->assertTrue($company->employees->contains($employee));
        $this->assertFalse($company->employees->contains($admin));
    }

    public function test_company_views_exclude_admin_from_employee_counts_and_list(): void
    {
        $currentUser = User::factory()->create([
            'name' => 'Platform Operator',
            'role' => 'admin',
        ]);

        $company = Company::factory()->create(['name' => 'Initech']);

        $adminInCompany = User::factory()->create([
            'name' => 'Company Admin Bill',
            'role' => 'admin',
            'company_id' => $company->id,
        ]);

        $employee = User::factory()->create([
            'name' => 'Peter Gibbons',
            'role' => 'participant',
            'company_id' => $company->id,
        ]);

        // Index page check
        $indexResponse = $this->actingAs($currentUser)->get(route('admin.companies.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('1 Employees');

        // Show page check
        $showResponse = $this->actingAs($currentUser)->get(route('admin.companies.show', $company));
        $showResponse->assertOk();
        $showResponse->assertSee('1 Employees');
        $showResponse->assertSee('1 Total');
        $showResponse->assertSee('Peter Gibbons');
        // The admin's name should NOT appear in the employee table or page
        $showResponse->assertDontSee('Company Admin Bill');
    }

    public function test_invite_employee_always_creates_participant(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $company = Company::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.companies.invite', $company), [
            'name' => 'Milton Waddams',
            'email' => 'milton@initech.com',
            'role' => 'admin', // Even if submitted, controller forces participant
        ]);

        $response->assertRedirect(route('admin.companies.show', $company));

        $user = User::where('email', 'milton@initech.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('participant', $user->role);
        $this->assertEquals($company->id, $user->company_id);
    }

    public function test_cannot_delete_admin_via_company_employee_delete_endpoint(): void
    {
        $adminA = User::factory()->create(['role' => 'admin']);
        $company = Company::factory()->create();
        $adminB = User::factory()->create([
            'role' => 'admin',
            'company_id' => $company->id,
        ]);

        $response = $this->actingAs($adminA)->delete(route('admin.companies.employees.destroy', [$company, $adminB]));

        $response->assertSessionHas('error', 'Administrators cannot be managed or deleted as company employees.');
        $this->assertDatabaseHas('users', ['id' => $adminB->id]);
    }

    public function test_company_survey_creation_only_enrolls_employees_not_admins(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $company = Company::factory()->create();

        $adminInCompany = User::factory()->create([
            'role' => 'admin',
            'company_id' => $company->id,
        ]);

        $employee = User::factory()->create([
            'role' => 'participant',
            'company_id' => $company->id,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.surveys.store'), [
            'company_id' => $company->id,
            'title' => 'Q4 Performance Review',
            'description' => '360 survey for cohort',
            'questions' => [
                'Demonstrates leadership skills?',
            ],
        ]);

        $response->assertSessionHas('success');
        $survey = Survey::where('title', 'Q4 Performance Review')->first();
        $this->assertNotNull($survey);

        $this->assertTrue($survey->participants->contains($employee));
        $this->assertFalse($survey->participants->contains($adminInCompany));
    }
}
