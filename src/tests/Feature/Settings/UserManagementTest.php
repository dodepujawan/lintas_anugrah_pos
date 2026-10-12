<?php

namespace Tests\Feature\Settings;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_settings_page(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'Administrator']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        $response = $this->actingAs($admin)->get('/settings');

        $response->assertStatus(200);
        $response->assertSee('Register User');
    }

    public function test_non_admin_cannot_access_settings_page(): void
    {
        $userRole = Role::firstOrCreate(['name' => 'User']);
        $user = User::factory()->create(['role_id' => $userRole->id]);

        $response = $this->actingAs($user)->get('/settings');

        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_admin_can_create_edit_and_delete_users(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'Administrator']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        $createResponse = $this->actingAs($admin)->post('/settings/users', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $createResponse->assertRedirect('/settings');
        $this->assertDatabaseHas('users', ['email' => 'new@example.com']);

        $user = User::where('email', 'new@example.com')->firstOrFail();

        $editResponse = $this->actingAs($admin)->put('/settings/users/' . $user->id, [
            'name' => 'Updated User',
            'email' => 'updated@example.com',
        ]);

        $editResponse->assertRedirect('/settings');
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated User', 'email' => 'updated@example.com']);

        $deleteResponse = $this->actingAs($admin)->delete('/settings/users/' . $user->id);

        $deleteResponse->assertRedirect('/settings');
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
