<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSidebarNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_left_sidebar_and_mobile_menu_with_all_top_items_moved_to_sidebar(): void
    {
        $company = Company::factory()->create();
        $admin = User::factory()->create([
            'role' => 'admin',
            'company_id' => $company->id,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertOk();

        // Check Brand & Admin Console
        $response->assertSee('Admin Console');
        $response->assertSee('Change Quo');

        // Check Mobile Menu Toggle Button
        $response->assertSee('Open sidebar menu');

        // Check Navigation Links in Sidebar
        $response->assertSee(route('admin.dashboard'));
        $response->assertSee(route('admin.companies.index'));
        $response->assertSee(route('admin.surveys.index'));
        $response->assertSee(route('admin.people.index'));
        $response->assertSee(route('admin.categories.index'));

        // Check Navigation items specifically in unified navigation
        $response->assertSee('My Company');
        $response->assertSee('Company Surveys');
        $response->assertSee('Team Directory');
        $response->assertSee('Survey Assessment');
        $response->assertSee('All Companies');
        $response->assertSee('Categories');

        // Assessments menu item is hidden from the sidebar to protect rating anonymity
        $response->assertDontSee('<span>Assessments</span>', false);
    }

    public function test_participant_sees_participant_sidebar_and_mobile_menu(): void
    {
        $company = Company::factory()->create();
        $participant = User::factory()->create([
            'role' => 'participant',
            'company_id' => $company->id,
        ]);

        $response = $this->actingAs($participant)->get(route('participant.assessments.index'));
        $response->assertOk();

        // Check Brand & Participant Portal
        $response->assertSee('Participant Portal');
        $response->assertSee('Change Quo');

        // Check Mobile Menu Toggle Button
        $response->assertSee('Open sidebar menu');

        // Check Navigation Links
        $response->assertSee(route('participant.assessments.index'));
        $response->assertSee(route('profile.show'));
        $response->assertSee('My Assessments');
        $response->assertSee('My Profile');

        // Ensure Admin items are not rendered
        $response->assertDontSee('Admin Console');
        $response->assertDontSee(route('admin.companies.index'));
        $response->assertDontSee(route('admin.categories.index'));
    }

    public function test_manager_sees_team_sync_dashboard_and_survey_assessment_navigation(): void
    {
        $company = Company::factory()->create();
        $manager = User::factory()->create([
            'role' => 'manager',
            'company_id' => $company->id,
        ]);
        Survey::factory()->create([
            'company_id' => $company->id,
            'created_by' => $manager->id,
            'status' => 'active',
        ]);

        // 1. Manager on /admin/dashboard should see Team CQ Sync Report
        $response = $this->actingAs($manager)->get(route('admin.dashboard'));
        $response->assertOk();
        $response->assertSee('Team CQ Sync');
        $response->assertSee(route('admin.survey-assessment'));
        $response->assertSee('Survey Assessment');

        // 2. Manager on /admin/survey-assessment should see the overview assessment dashboard
        $responseAssessment = $this->actingAs($manager)->get(route('admin.survey-assessment'));
        $responseAssessment->assertOk();
        $responseAssessment->assertSee('Survey Assessment');
        $responseAssessment->assertSee('Recent Assessments');

        // 3. Admin on /admin/dashboard should also see Survey Assessment link in unified menu
        $admin = User::factory()->create([
            'role' => 'admin',
            'company_id' => $company->id,
        ]);
        $responseAdmin = $this->actingAs($admin)->get(route('admin.dashboard'));
        $responseAdmin->assertOk();
        $responseAdmin->assertSee(route('admin.survey-assessment'));
        $responseAdmin->assertSee('Survey Assessment');
    }
}
