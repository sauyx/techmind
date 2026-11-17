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

    public function test_landing_page_renders_welcome_inertia_component(): void
    {
        $this->get('/')
            ->assertInertia(fn (AssertableInertia $page) => $page->component('welcome'));
    }

    public function test_landing_page_contains_main_content(): void
    {
        $response = $this->get('/');

        $response->assertSee('TechMind');
        $response->assertSee('Kenali potensimu');
        $response->assertSee('Cara kerja TechMind');
    }
}
