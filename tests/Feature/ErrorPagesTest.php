<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Register temporary test routes for status codes not tied to existing static routes
        Route::get('/test-error-401', fn () => abort(401, 'Custom unauthorized message'));
        Route::get('/test-error-402', fn () => abort(402, 'Custom payment required message'));
    }

    public function test_404_page_displays_custom_branded_design(): void
    {
        $response = $this->get('/non-existent-random-route-xyz');

        $response->assertStatus(404);
        $response->assertSee('404');
        $response->assertSee('Page Not Found');
        $response->assertSee('change');
        $response->assertSee('quo');
        $response->assertSee('Return to Home');
    }

    public function test_404_page_displays_dashboard_link_for_authenticated_users(): void
    {
        $user = User::factory()->create(['role' => 'participant']);

        $response = $this->actingAs($user)->get('/non-existent-random-route-xyz');

        $response->assertStatus(404);
        $response->assertSee('Back to Dashboard');
        $response->assertSee($user->name);
    }

    public function test_403_page_displays_when_participant_accesses_restricted_admin_route(): void
    {
        $participant = User::factory()->create(['role' => 'participant']);

        $response = $this->actingAs($participant)->get('/admin/dashboard');

        $response->assertStatus(403);
        $response->assertSee('403');
        $response->assertSee('Access Restricted');
        $response->assertSee('Back to Dashboard');
        $response->assertSee($participant->name);
        $response->assertSee('Participant');
    }

    public function test_401_page_displays_custom_branded_design(): void
    {
        $response = $this->get('/test-error-401');

        $response->assertStatus(401);
        $response->assertSee('401');
        $response->assertSee('Authentication Required');
        $response->assertSee('Custom unauthorized message');
        $response->assertSee('Sign In');
    }

    public function test_402_page_displays_custom_branded_design(): void
    {
        $response = $this->get('/test-error-402');

        $response->assertStatus(402);
        $response->assertSee('402');
        $response->assertSee('Subscription Required');
        $response->assertSee('Custom payment required message');
        $response->assertSee('Contact Administrator');
    }
}
