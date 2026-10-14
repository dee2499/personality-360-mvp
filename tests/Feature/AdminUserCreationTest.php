<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_default_password_generation(): void
    {
        $falcon = Company::factory()->create(['name' => 'Falcon Group']);
        $this->assertEquals('falcon123', $falcon->defaultUserPassword());

        $gcode = Company::factory()->create(['name' => 'GCODE']);
        $this->assertEquals('gcode123', $gcode->defaultUserPassword());

        $acme = Company::factory()->create(['name' => 'Acme Corporation']);
        $this->assertEquals('acme123', $acme->defaultUserPassword());

        $special = Company::factory()->create(['name' => '***Special-Ops*** Tech']);
        $this->assertEquals('special123', $special->defaultUserPassword());
    }

    public function test_admin_can_create_company_employee_with_only_username_and_demographics(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $company = Company::factory()->create(['name' => 'Falcon Group']);

        $response = $this->actingAs($admin)->post(route('admin.companies.invite', $company), [
            'name' => 'John Doe',
            'username' => 'johndoe',
            'email' => '',
            'role' => 'participant',
            'designation' => 'Senior Developer',
            'department' => 'Engineering',
            'division' => 'Software',
            'age' => 29,
        ]);

        $response->assertRedirect(route('admin.companies.show', $company));
        $response->assertSessionHas('success');

        $user = User::where('username', 'johndoe')->first();
        $this->assertNotNull($user);
        $this->assertNull($user->email);
        $this->assertEquals('Senior Developer', $user->designation);
        $this->assertEquals('Engineering', $user->department);
        $this->assertEquals('Software', $user->division);
        $this->assertEquals(29, $user->age);
        $this->assertEquals($company->id, $user->company_id);
        $this->assertTrue(Hash::check('falcon123', $user->password));

        // Ensure user can log in immediately using their username and the default password
        auth()->logout();
        $loginResponse = $this->post(route('login'), [
            'email' => 'johndoe',
            'password' => 'falcon123',
        ]);
        $loginResponse->assertRedirect(route('participant.assessments.index'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_can_create_company_employee_with_only_email(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $company = Company::factory()->create(['name' => 'GCODE']);

        $response = $this->actingAs($admin)->post(route('admin.companies.invite', $company), [
            'name' => 'Alice Smith',
            'username' => '',
            'email' => 'alice@gcode.com',
            'role' => 'participant',
        ]);

        $response->assertRedirect(route('admin.companies.show', $company));

        $user = User::where('email', 'alice@gcode.com')->first();
        $this->assertNotNull($user);
        $this->assertNull($user->username);
        $this->assertTrue(Hash::check('gcode123', $user->password));

        // Ensure user can log in immediately with email and default password
        auth()->logout();
        $loginResponse = $this->post(route('login'), [
            'email' => 'alice@gcode.com',
            'password' => 'gcode123',
        ]);
        $loginResponse->assertRedirect(route('participant.assessments.index'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_creating_company_employee_requires_at_least_username_or_email(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $company = Company::factory()->create(['name' => 'Falcon Group']);

        $response = $this->actingAs($admin)->post(route('admin.companies.invite', $company), [
            'name' => 'Incomplete User',
            'username' => '',
            'email' => '',
        ]);

        $response->assertSessionHasErrors(['username', 'email']);
        $this->assertDatabaseMissing('users', ['name' => 'Incomplete User']);
    }

    public function test_admin_can_create_survey_participant_with_username_and_company_password(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $company = Company::factory()->create(['name' => 'Falcon Group']);
        $survey = Survey::factory()->create(['company_id' => $company->id]);

        $response = $this->actingAs($admin)
            ->from(route('admin.surveys.show', $survey))
            ->post(route('admin.surveys.participants.store', $survey), [
                'new_name' => 'Bob Builder',
                'new_username' => 'bobbuilder',
                'new_email' => '',
                'new_designation' => 'Lead Architect',
                'new_department' => 'Infrastructure',
                'new_division' => 'Construction',
                'new_age' => 35,
            ]);

        $response->assertRedirect(route('admin.surveys.show', $survey));
        $response->assertSessionHas('success');

        $user = User::where('username', 'bobbuilder')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Lead Architect', $user->designation);
        $this->assertEquals('Infrastructure', $user->department);
        $this->assertEquals('Construction', $user->division);
        $this->assertEquals(35, $user->age);
        $this->assertEquals($company->id, $user->company_id);
        $this->assertTrue(Hash::check('falcon123', $user->password));
        $this->assertTrue($survey->participants()->where('users.id', $user->id)->exists());

        // Verify login
        auth()->logout();
        $loginResponse = $this->post(route('login'), [
            'email' => 'bobbuilder',
            'password' => 'falcon123',
        ]);
        $loginResponse->assertRedirect(route('participant.assessments.index'));
        $this->assertAuthenticatedAs($user);
    }
}
