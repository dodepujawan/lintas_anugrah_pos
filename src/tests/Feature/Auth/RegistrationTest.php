<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleMenuSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleMenuSeeder::class);
    }

    public function test_registration_screen_can_be_rendered_for_admin(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'Administrator']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        $response = $this->actingAs($admin)->get('/register');

        $response->assertStatus(200);
    }

    public function test_non_admin_users_cannot_access_registration_page(): void
    {
        $userRole = Role::firstOrCreate(['name' => 'User']);
        $user = User::factory()->create(['role_id' => $userRole->id]);

        $response = $this->actingAs($user)->get('/register');

        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_admin_can_register_new_users(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'Administrator']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        $response = $this->actingAs($admin)->post('/register', [
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect(route('dashboard', absolute: false));
    }
}
