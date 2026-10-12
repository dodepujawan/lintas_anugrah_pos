<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileEmailUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_own_recovery_email(): void
    {
        $user = User::factory()->create(['email' => 'lama@example.com']);

        $response = $this->actingAs($user)->patch(route('profile.email.update'), [
            'email' => 'baru@example.com',
        ]);

        $response->assertRedirect(route('profile.password'));
        $this->assertSame('baru@example.com', $user->fresh()->email);
    }

    public function test_recovery_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'dipakai@example.com']);
        $user = User::factory()->create(['email' => 'sendiri@example.com']);

        $this->actingAs($user)->from(route('profile.password'))->patch(route('profile.email.update'), [
            'email' => 'dipakai@example.com',
        ])->assertSessionHasErrors('email', null, 'updateEmail');

        $this->assertSame('sendiri@example.com', $user->fresh()->email);
    }
}
