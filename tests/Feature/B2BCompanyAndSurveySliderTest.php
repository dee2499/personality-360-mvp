<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Question;
use App\Models\Survey;
use App\Models\User;
use App\Notifications\EmployeeInvitationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class B2BCompanyAndSurveySliderTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_company_and_invite_employee(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin']);

        // 1. Admin creates company
        $responseCreate = $this->actingAs($admin)->post(route('admin.companies.store'), [
            'name' => 'Stark Industries',
            'contact_email' => 'tony@stark.com',
            'description' => 'Advanced technologies and robotics division.',
        ]);

        $company = Company::where('name', 'Stark Industries')->first();
        $this->assertNotNull($company);
        $responseCreate->assertRedirect(route('admin.companies.show', $company));

        // 2. Admin invites employee
        $responseInvite = $this->actingAs($admin)->post(route('admin.companies.invite', $company), [
            'name' => 'Pepper Potts',
            'email' => 'pepper@stark.com',
            'role' => 'participant',
        ]);

        $responseInvite->assertRedirect(route('admin.companies.show', $company));
        $employee = User::where('email', 'pepper@stark.com')->first();
        $this->assertNotNull($employee);
        $this->assertEquals($company->id, $employee->company_id);
        $this->assertNotNull($employee->invitation_token);
        $this->assertTrue($employee->isInvited());

        Notification::assertSentTo($employee, EmployeeInvitationNotification::class);
    }

    public function test_employee_can_accept_invitation_and_set_password(): void
    {
        $company = Company::factory()->create(['name' => 'Wayne Enterprises']);
        $employee = User::factory()->create([
            'company_id' => $company->id,
            'email' => 'bruce@wayne.com',
            'password' => Hash::make('temporary-secret'),
            'invitation_token' => 'test-invitation-token-12345',
            'invitation_sent_at' => now(),
            'invitation_accepted_at' => null,
        ]);

        // 1. Visit invitation link
        $responseShow = $this->get(route('invitation.show', ['token' => 'test-invitation-token-12345']));
        $responseShow->assertOk();
        $responseShow->assertSee('Wayne Enterprises');
        $responseShow->assertSee('bruce@wayne.com');

        // 2. Submit new password
        $responseAccept = $this->post(route('invitation.accept', ['token' => 'test-invitation-token-12345']), [
            'password' => 'NewSecurePassword123!',
            'password_confirmation' => 'NewSecurePassword123!',
        ]);

        $responseAccept->assertRedirect(route('participant.assessments.index'));
        $this->assertAuthenticatedAs($employee);

        $employee->refresh();
        $this->assertNull($employee->invitation_token);
        $this->assertNotNull($employee->invitation_accepted_at);
        $this->assertTrue(Hash::check('NewSecurePassword123!', $employee->password));
    }

    public function test_user_can_update_password_from_profile(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword123!'),
        ]);

        $response = $this->actingAs($user)->post(route('profile.password.update'), [
            'current_password' => 'OldPassword123!',
            'password' => 'UpdatedPassword456!',
            'password_confirmation' => 'UpdatedPassword456!',
        ]);

        $response->assertSessionHas('success');
        $user->refresh();
        $this->assertTrue(Hash::check('UpdatedPassword456!', $user->password));
    }

    public function test_participant_can_open_and_submit_11_question_slider_matrix(): void
    {
        $company = Company::factory()->create(['name' => 'OmniCorp']);
        $admin = User::factory()->create(['role' => 'admin', 'company_id' => $company->id]);

        $dipak = User::factory()->create(['name' => 'Dipak', 'role' => 'participant', 'company_id' => $company->id]);
        $ankit = User::factory()->create(['name' => 'Ankit', 'role' => 'participant', 'company_id' => $company->id]);
        $srini = User::factory()->create(['name' => 'Srini', 'role' => 'participant', 'company_id' => $company->id]);

        $cohort = collect([$dipak, $ankit, $srini]);

        $survey = Survey::create([
            'company_id' => $company->id,
            'title' => 'Leadership Cohort 360',
            'status' => 'published',
            'created_by' => $admin->id,
        ]);
        $survey->participants()->attach($cohort->pluck('id'));

        // Create 11 questions
        $questions = collect();
        for ($i = 1; $i <= 11; $i++) {
            $questions->push(Question::create([
                'survey_id' => $survey->id,
                'question_text' => "Question {$i}: Evaluates competency dimension {$i}",
                'sort_order' => $i,
                'is_active' => true,
            ]));
        }

        // 1. Dipak opens the survey slider view
        $responseView = $this->actingAs($dipak)->get(route('participant.surveys.take', $survey));
        $responseView->assertOk();
        $responseView->assertSee('Leadership Cohort 360');
        $responseView->assertSee('Question 1: Evaluates competency dimension 1');
        $responseView->assertSee('Dipak');
        $responseView->assertSee('Ankit');
        $responseView->assertSee('Srini');

        // 2. Dipak submits answers for all 3 cohort members across all 11 questions
        $answersMatrix = [];
        foreach ($cohort as $subject) {
            foreach ($questions as $q) {
                $answersMatrix[$subject->id][$q->id] = 8; // Score 8 out of 10
            }
        }

        $responseSubmit = $this->actingAs($dipak)->post(route('participant.surveys.submit-matrix', $survey), [
            'answers' => $answersMatrix,
        ]);

        $responseSubmit->assertRedirect(route('participant.assessments.index'));
        $responseSubmit->assertSessionHas('success');

        // Verify that 3 completed assessments were stored for Dipak
        $assessments = $survey->assessments()->where('assessor_id', $dipak->id)->get();
        $this->assertCount(3, $assessments);

        foreach ($assessments as $assessment) {
            $this->assertEquals('completed', $assessment->status);
            $this->assertEquals(88, $assessment->total_score); // 11 * 8
            $this->assertEquals(110, $assessment->max_score); // 11 * 10
            $this->assertEquals(80.0, $assessment->percentage);
            $this->assertEquals('Lemon', $assessment->category);
        }
    }

    public function test_admin_can_delete_employee_from_company(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $company = Company::factory()->create(['name' => 'Cyberdyne Systems']);
        $employee = User::factory()->create([
            'name' => 'Miles Dyson',
            'company_id' => $company->id,
            'role' => 'participant',
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.companies.employees.destroy', [$company, $employee]));

        $response->assertRedirect(route('admin.companies.show', $company));
        $response->assertSessionHas('success');
        $this->assertSoftDeleted('users', ['id' => $employee->id]);
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $company = Company::factory()->create();
        $admin->update(['company_id' => $company->id]);

        $response = $this->actingAs($admin)->delete(route('admin.companies.employees.destroy', [$company, $admin]));

        $response->assertSessionHas('error', 'You cannot delete your own account.');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_can_delete_person_from_people_directory(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $person = User::factory()->create(['role' => 'participant']);

        $response = $this->actingAs($admin)->delete(route('admin.people.destroy', $person));

        $response->assertRedirect(route('admin.people.index'));
        $response->assertSessionHas('success');
        $this->assertSoftDeleted('users', ['id' => $person->id]);
    }
}
