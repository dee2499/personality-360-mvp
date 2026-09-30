<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessmentSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_participant_cannot_access_another_participants_assessment(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $survey = Survey::create([
            'title' => 'Security Test Survey',
            'status' => 'published',
            'created_by' => $admin->id,
        ]);

        $alice = User::factory()->create(['name' => 'Alice', 'role' => 'participant']);
        $bob = User::factory()->create(['name' => 'Bob', 'role' => 'participant']);

        // Assessment assigned to Bob to evaluate Alice
        $bobsAssessment = Assessment::create([
            'survey_id' => $survey->id,
            'assessor_id' => $bob->id,
            'subject_id' => $alice->id,
            'status' => 'pending',
        ]);

        // Alice tries to open Bob's assessment assignment
        $response = $this->actingAs($alice)->get(route('participant.assessments.show', $bobsAssessment));
        $response->assertStatus(403);
    }

    public function test_participant_cannot_access_admin_dashboard_or_people(): void
    {
        $participant = User::factory()->create(['role' => 'participant']);

        $response = $this->actingAs($participant)->get(route('admin.dashboard'));
        $response->assertStatus(403);

        $response2 = $this->actingAs($participant)->get(route('admin.people.index'));
        $response2->assertStatus(403);
    }

    public function test_admin_can_access_dashboard_and_people(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);

        $response2 = $this->actingAs($admin)->get(route('admin.people.index'));
        $response2->assertStatus(200);
    }

    public function test_unauthenticated_users_are_redirected_to_login(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));

        $response2 = $this->get(route('participant.assessments.index'));
        $response2->assertRedirect(route('login'));
    }
}
