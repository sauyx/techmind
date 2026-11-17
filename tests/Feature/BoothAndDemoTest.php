<?php

namespace Tests\Feature;

use App\Models\TestResult;
use App\Models\TestSession;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class BoothAndDemoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ReferenceDataSeeder::class);
    }

    public function test_admin_booth_access_is_restricted_to_admin(): void
    {
        $this->get('/admin/booth')->assertRedirect(route('login'));

        $nonAdmin = User::factory()->create();
        $this->actingAs($nonAdmin)->get('/admin/booth')->assertForbidden();

        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->get('/admin/booth')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('admin/booth')
                ->has('status')
                ->where('status.active_criteria', 12)
                ->where('status.active_careers', 10)
                ->where('status.database_ready', true)
            );
    }

    public function test_demo_mode_flag_is_shared_with_inertia_pages(): void
    {
        config(['techmind.demo_mode' => true]);
        $this->get('/tes')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('public/test')
                ->where('demo_mode', true)
            );

        config(['techmind.demo_mode' => false]);
        $this->get('/tes')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('public/test')
                ->where('demo_mode', false)
            );
    }

    public function test_demo_seeder_creates_sessions_feedback_and_explanations(): void
    {
        config(['techmind.demo_mode' => true]);

        $this->seed(DemoSeeder::class);

        $this->assertSame(18, TestSession::query()->whereNotNull('completed_at')->count());
        $this->assertSame(18, TestResult::query()->where('rank', 1)->count());
        $this->assertSame(10, TestResult::query()->where('rank', 1)->distinct()->count('career_name'));
        $this->assertDatabaseCount('visitor_feedbacks', 18);
        $this->assertDatabaseCount('ai_explanations', 18);

        $this->assertDatabaseHas('visitor_feedbacks', ['score' => 86]);
        $this->assertDatabaseHas('ai_explanations', ['driver_used' => 'template', 'is_fallback' => false]);
    }

    public function test_custom_404_page_renders_without_debug_details(): void
    {
        $this->get('/halaman-yang-tidak-ada')
            ->assertNotFound()
            ->assertSee('Halaman tidak ditemukan')
            ->assertDontSee('Stack trace');
    }
}
