<?php

namespace Tests\Feature;

use App\Models\Company;
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
        $response->assertSee('Personality 360');

        // Check Mobile Menu Toggle Button
        $response->assertSee('Open sidebar menu');

        // Check All Navigation Links moved to Sidebar
        $response->assertSee(route('admin.dashboard'));
        $response->assertSee(route('admin.companies.index'));
        $response->assertSee(route('admin.surveys.index'));
        $response->assertSee(route('admin.people.index'));
        $response->assertSee(route('admin.assessments.index'));
        $response->assertSee(route('admin.categories.index'));

        // Check Categories & Companies specifically in navigation
        $response->assertSee('Companies');
        $response->assertSee('Surveys');
        $response->assertSee('People Directory');
        $response->assertSee('Assessments');
        $response->assertSee('Categories');
    }

    public function test_participant_does_not_see_admin_sidebar_navigation(): void
    {
        $company = Company::factory()->create();
        $participant = User::factory()->create([
            'role' => 'participant',
            'company_id' => $company->id,
        ]);

        $response = $this->actingAs($participant)->get(route('participant.assessments.index'));
        $response->assertOk();

        $response->assertDontSee('Admin Console');
        $response->assertDontSee(route('admin.companies.index'));
        $response->assertDontSee(route('admin.categories.index'));
        $response->assertSee('My Assessments');
    }
}
