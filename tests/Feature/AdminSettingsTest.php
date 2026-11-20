<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_change_password_and_login_with_new_one(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'email_verified_at' => now(),
            'password' => 'old-password-123',
        ]);

        $this->actingAs($admin)
            ->put('/settings/password', [
                'current_password' => 'old-password-123',
                'password' => 'new-password-456',
                'password_confirmation' => 'new-password-456',
            ])
            ->assertRedirect();

        $this->post('/logout');

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'new-password-456',
        ])->assertRedirect('/admin');
    }

    public function test_disabled_auth_routes_return_404(): void
    {
        $this->get('/register')->assertNotFound();
        $this->get('/settings/security')->assertNotFound();
    }
}
