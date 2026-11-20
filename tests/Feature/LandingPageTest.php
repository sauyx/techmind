<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_is_accessible_without_authentication(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_landing_page_renders_public_landing_component(): void
    {
        $this->get('/')
            ->assertInertia(fn (AssertableInertia $page) => $page->component('public/landing'));
    }

    public function test_landing_page_contains_main_content(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('public/landing')
            ->where('name', 'TechMind')
        );
    }
}
