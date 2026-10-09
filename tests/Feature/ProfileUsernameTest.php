<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileUsernameTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_profile_with_username_field(): void
    {
        $user = User::factory()->create([
            'username' => 'testuser@falcon',
            'role' => 'participant',
        ]);

        $response = $this->actingAs($user)->get(route('profile.show'));

        $response->assertOk();
        $response->assertSee('Username');
        $response->assertSee('testuser@falcon');
    }

    public function test_check_username_endpoint_reports_availability(): void
    {
        $user1 = User::factory()->create([
            'username' => 'existing@falcon',
            'role' => 'participant',
        ]);

        $user2 = User::factory()->create([
            'username' => 'myuser@falcon',
            'role' => 'participant',
        ]);

        // Own username should be available to user2
        $resOwn = $this->actingAs($user2)->getJson(route('profile.check-username', ['username' => 'myuser@falcon']));
        $resOwn->assertOk()->assertJson(['available' => true]);

        // Taken username should NOT be available
        $resTaken = $this->actingAs($user2)->getJson(route('profile.check-username', ['username' => 'existing@falcon']));
        $resTaken->assertOk()->assertJson(['available' => false]);

        // Case-insensitive check
        $resTakenCase = $this->actingAs($user2)->getJson(route('profile.check-username', ['username' => 'EXISTING@falcon']));
        $resTakenCase->assertOk()->assertJson(['available' => false]);

        // Completely new username should be available
        $resNew = $this->actingAs($user2)->getJson(route('profile.check-username', ['username' => 'fresh@falcon']));
        $resNew->assertOk()->assertJson(['available' => true]);
    }

    public function test_user_can_update_their_username_when_available(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'username' => 'olduser@falcon',
            'role' => 'participant',
        ]);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'username' => 'newuser@falcon',
        ]);

        $response->assertRedirect();
        $user->refresh();
        $this->assertEquals('newuser@falcon', $user->username);
        $this->assertEquals('Updated Name', $user->name);
    }

    public function test_user_cannot_update_username_to_one_already_taken(): void
    {
        $user1 = User::factory()->create([
            'username' => 'alreadytaken@falcon',
            'role' => 'participant',
        ]);

        $user2 = User::factory()->create([
            'name' => 'Second User',
            'username' => 'second@falcon',
            'role' => 'participant',
        ]);

        $response = $this->actingAs($user2)->put(route('profile.update'), [
            'first_name' => 'Second',
            'last_name' => 'User',
            'username' => 'alreadytaken@falcon',
        ]);

        $response->assertSessionHasErrors('username');
        $user2->refresh();
        $this->assertEquals('second@falcon', $user2->username);
    }
}
