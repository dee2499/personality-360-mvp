<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\AssessmentAnswer;
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

        // 0. Visiting individual assessment show redirects to unified slider
        $dipakSelfAssessment = Assessment::create([
            'survey_id' => $survey->id,
            'assessor_id' => $dipak->id,
            'subject_id' => $dipak->id,
            'status' => 'pending',
            'total_score' => 0,
            'max_score' => 110,
            'percentage' => 0.0,
        ]);
        $responseRedirect = $this->actingAs($dipak)->get(route('participant.assessments.show', $dipakSelfAssessment));
        $responseRedirect->assertRedirect(route('participant.surveys.take', $survey));

        // 1. Dipak opens the survey slider view
        $responseView = $this->actingAs($dipak)->get(route('participant.surveys.take', $survey));
        $responseView->assertOk();
        $responseView->assertSee('Leadership Cohort 360');
        $responseView->assertSee('Question 1: Evaluates competency dimension 1');
        $responseView->assertSee('Self Evaluation');
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
        $this->assertDatabaseMissing('users', ['id' => $employee->id]);
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
        $this->assertDatabaseMissing('users', ['id' => $person->id]);
    }

    public function test_user_profile_displays_three_meters_normalised_self_and_peer(): void
    {
        $company = Company::factory()->create(['name' => 'Acme Labs']);
        $admin = User::factory()->create(['role' => 'admin', 'company_id' => $company->id]);
        $alice = User::factory()->create(['name' => 'Alice', 'role' => 'participant', 'company_id' => $company->id]);
        $bob = User::factory()->create(['name' => 'Bob', 'role' => 'participant', 'company_id' => $company->id]);

        $survey = Survey::create([
            'company_id' => $company->id,
            'title' => 'Executive Leadership 360',
            'status' => 'published',
            'created_by' => $admin->id,
        ]);
        $survey->participants()->attach([$alice->id, $bob->id]);

        // Alice self assessment
        Assessment::create([
            'survey_id' => $survey->id,
            'assessor_id' => $alice->id,
            'subject_id' => $alice->id,
            'status' => 'completed',
            'total_score' => 95,
            'max_score' => 110,
            'percentage' => 86.36,
            'category' => 'Cucumber',
        ]);

        // Bob peer assessment of Alice
        Assessment::create([
            'survey_id' => $survey->id,
            'assessor_id' => $bob->id,
            'subject_id' => $alice->id,
            'status' => 'completed',
            'total_score' => 88,
            'max_score' => 110,
            'percentage' => 80.0,
            'category' => 'Lemon',
        ]);

        // Visit Alice's profile
        $response = $this->actingAs($alice)->get(route('profile.show'));
        $response->assertOk();
        $response->assertSee('Alice');
        $response->assertSee('Acme Labs');
        $response->assertSee('Change Password');

        // Check the 3 Meters: Normalised, Self, Peer
        $response->assertSee('Three Score Meters (Normalised, Self, Peer)');
        $response->assertSee('Meter 1: Normalised');
        $response->assertSee('Normalised Score');
        $response->assertSee('82.54'); // 40% of 86.36 + 60% of 80.00
        $response->assertSee('Meter 2: Self');
        $response->assertSee('Self Score');
        $response->assertSee('86.36');
        $response->assertSee('Meter 3: Peer');
        $response->assertSee('Peer Score');
        $response->assertSee('80.00');
    }

    public function test_user_dashboard_displays_dual_meters_and_switches_between_two_surveys(): void
    {
        $company = Company::factory()->create(['name' => 'Stark Global']);
        $admin = User::factory()->create(['role' => 'admin', 'company_id' => $company->id]);
        $tony = User::factory()->create(['name' => 'Tony Stark', 'role' => 'participant', 'company_id' => $company->id]);
        $rhodey = User::factory()->create(['name' => 'James Rhodes', 'role' => 'participant', 'company_id' => $company->id]);

        // Survey 1
        $survey1 = Survey::create([
            'company_id' => $company->id,
            'title' => 'Leadership & Strategy 360',
            'status' => 'published',
            'created_by' => $admin->id,
        ]);
        $survey1->participants()->attach([$tony->id, $rhodey->id]);
        $q1 = Question::create([
            'survey_id' => $survey1->id,
            'question_text' => 'Demonstrates technical visionary leadership',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $tonySelf1 = Assessment::create([
            'survey_id' => $survey1->id,
            'assessor_id' => $tony->id,
            'subject_id' => $tony->id,
            'status' => 'completed',
            'total_score' => 95,
            'max_score' => 110,
            'percentage' => 86.36,
            'category' => 'Cucumber',
        ]);
        AssessmentAnswer::create(['assessment_id' => $tonySelf1->id, 'question_id' => $q1->id, 'score' => 10]);

        $rhodeyPeer1 = Assessment::create([
            'survey_id' => $survey1->id,
            'assessor_id' => $rhodey->id,
            'subject_id' => $tony->id,
            'status' => 'completed',
            'total_score' => 90,
            'max_score' => 110,
            'percentage' => 81.82,
            'category' => 'Cucumber',
        ]);
        AssessmentAnswer::create(['assessment_id' => $rhodeyPeer1->id, 'question_id' => $q1->id, 'score' => 9]);

        // Survey 2
        $survey2 = Survey::create([
            'company_id' => $company->id,
            'title' => 'Innovation & Team Culture 360',
            'status' => 'published',
            'created_by' => $admin->id,
        ]);
        $survey2->participants()->attach([$tony->id, $rhodey->id]);
        $q2 = Question::create([
            'survey_id' => $survey2->id,
            'question_text' => 'Fosters a collaborative environment of trust',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $tonySelf2 = Assessment::create([
            'survey_id' => $survey2->id,
            'assessor_id' => $tony->id,
            'subject_id' => $tony->id,
            'status' => 'completed',
            'total_score' => 80,
            'max_score' => 110,
            'percentage' => 72.73,
            'category' => 'Lemon',
        ]);
        AssessmentAnswer::create(['assessment_id' => $tonySelf2->id, 'question_id' => $q2->id, 'score' => 8]);

        $rhodeyPeer2 = Assessment::create([
            'survey_id' => $survey2->id,
            'assessor_id' => $rhodey->id,
            'subject_id' => $tony->id,
            'status' => 'completed',
            'total_score' => 85,
            'max_score' => 110,
            'percentage' => 77.27,
            'category' => 'Lemon',
        ]);
        AssessmentAnswer::create(['assessment_id' => $rhodeyPeer2->id, 'question_id' => $q2->id, 'score' => 9]);

        // 1. Visit Tony's dashboard (default view displays Survey 1)
        $response1 = $this->actingAs($tony)->get(route('participant.assessments.index'));
        $response1->assertOk();
        $response1->assertSee('Select Survey to View Matrix');
        $response1->assertSee('Leadership & Strategy 360');
        $response1->assertSee('Innovation & Team Culture 360');
        $response1->assertSee('Meter 1: Self Evaluation');
        $response1->assertSee('Meter 2: Peer Feedback');
        $response1->assertSee('86.36');
        $response1->assertSee('81.82');
        $response1->assertSee('Demonstrates technical visionary leadership');

        // 2. Select Survey 2 by name on top via query string
        $response2 = $this->actingAs($tony)->get(route('participant.assessments.index', ['survey_id' => $survey2->id]));
        $response2->assertOk();
        $response2->assertSee('Innovation & Team Culture 360');
        $response2->assertSee('72.73');
        $response2->assertSee('77.27');
        $response2->assertSee('Fosters a collaborative environment of trust');
    }
}
