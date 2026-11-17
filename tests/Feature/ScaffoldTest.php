<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScaffoldTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_returns_ok(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_guests_are_redirected_to_login_for_admin_area(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
    }

    public function test_non_admin_users_receive_forbidden_on_admin_area(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_admin_users_can_visit_admin_area(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->get('/admin')->assertOk();
    }

    public function test_public_registration_route_is_not_available(): void
    {
        $this->get('/register')->assertNotFound();
    }
}
