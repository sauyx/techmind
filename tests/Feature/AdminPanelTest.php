<?php

namespace Tests\Feature;

use App\Models\Career;
use App\Models\Criterion;
use App\Models\TestSession;
use App\Models\User;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ReferenceDataSeeder::class);
    }

    public function test_guest_and_non_admin_are_rejected_from_admin_routes(): void
    {
        $adminRoutes = [
            '/admin',
            '/admin/criteria',
            '/admin/careers',
            '/admin/careers/create',
            '/admin/feedbacks/export',
        ];

        foreach ($adminRoutes as $route) {
            $this->get($route)->assertRedirect(route('login'));
        }

        $nonAdmin = User::factory()->create();

        foreach ($adminRoutes as $route) {
            $this->actingAs($nonAdmin)->get($route)->assertForbidden();
        }
    }

    public function test_admin_can_see_accurate_dashboard_kpis(): void
    {
        $admin = $this->adminUser();

        $web = Career::query()->where('code', 'web_developer')->firstOrFail();
        $data = Career::query()->where('code', 'data_analyst')->firstOrFail();

        $this->createCompletedSession('Budi', $web, 90, 'Sangat membantu.', 'Tambah fitur simulasi.');
        $this->createCompletedSession('Ani', $web, 80, 'Menarik.', 'Perbanyak contoh karier.');
        $this->createCompletedSession(null, $data, null, null, null);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('admin/dashboard')
            ->where('kpi.total_sessions', 3)
            ->where('kpi.total_feedbacks', 2)
            ->where('kpi.average_feedback_score', '85.0')
            ->where('kpi.top_career.career_name', 'Web Developer')
            ->where('kpi.top_career.count', 2)
            ->has('career_distribution', 2)
            ->has('recent_feedbacks', 2)
        );
    }

    public function test_admin_can_update_criterion_weight_and_validation_rejects_out_of_range(): void
    {
        $admin = $this->adminUser();
        $criterion = Criterion::query()->firstOrFail();

        $payload = [
            'name' => $criterion->name,
            'description' => $criterion->description,
            'weight' => 1.5,
            'step_group' => (int) $criterion->step_group,
            'display_order' => (int) $criterion->display_order,
            'is_active' => (bool) $criterion->is_active,
        ];

        $this->actingAs($admin)
            ->put('/admin/criteria/'.$criterion->id, $payload)
            ->assertRedirect('/admin/criteria');

        $this->assertSame(1.5, (float) $criterion->refresh()->weight);

        $this->actingAs($admin)
            ->put('/admin/criteria/'.$criterion->id, [...$payload, 'weight' => 2.5])
            ->assertSessionHasErrors('weight');

        $this->actingAs($admin)
            ->put('/admin/criteria/'.$criterion->id, [...$payload, 'weight' => 0.25])
            ->assertSessionHasErrors('weight');
    }

    public function test_admin_can_create_career_and_update_profile_matrix(): void
    {
        $admin = $this->adminUser();
        $criteria = Criterion::query()->where('is_active', true)->orderBy('display_order')->get();

        $profiles = [];
        foreach ($criteria as $index => $criterion) {
            $profiles[$criterion->id] = $index === 0 ? 4 : 3;
        }

        $this->actingAs($admin)->post('/admin/careers', [
            'code' => 'game_developer',
            'name' => 'Game Developer',
            'summary' => 'Membangun game.',
            'description' => 'Mengembangkan game dan mekanik permainan.',
            'skills' => ['C#', 'Desain game'],
            'tools' => ['Unity', 'Blender'],
            'roadmap' => ['Dasar pemrograman', 'Mesin game', 'Rilis'],
            'sort_order' => 11,
            'is_active' => true,
            'is_validated' => false,
            'source_note' => 'data uji admin',
            'profiles' => $profiles,
        ])->assertRedirect('/admin/careers');

        $career = Career::query()->where('code', 'game_developer')->firstOrFail();
        $this->assertSame('Game Developer', $career->name);
        $this->assertDatabaseHas('career_profiles', [
            'career_id' => $career->id,
            'criterion_id' => $criteria->first()->id,
            'score' => 4,
        ]);

        $web = Career::query()->where('code', 'web_developer')->firstOrFail();
        $updateProfiles = [];
        foreach ($criteria as $criterion) {
            $updateProfiles[$criterion->id] = 4;
        }

        $this->actingAs($admin)->put('/admin/careers/'.$web->id, [
            'code' => $web->code,
            'name' => 'Web Developer Updated',
            'summary' => $web->summary,
            'description' => $web->description,
            'skills' => $web->skills,
            'tools' => $web->tools,
            'roadmap' => $web->roadmap,
            'sort_order' => $web->sort_order,
            'is_active' => true,
            'is_validated' => true,
            'source_note' => 'diperbarui admin',
            'profiles' => $updateProfiles,
        ])->assertRedirect('/admin/careers');

        $this->assertDatabaseHas('careers', [
            'id' => $web->id,
            'name' => 'Web Developer Updated',
            'is_validated' => true,
        ]);

        $this->assertDatabaseHas('career_profiles', [
            'career_id' => $web->id,
            'criterion_id' => $criteria->last()->id,
            'score' => 4,
        ]);
    }

    public function test_admin_can_export_feedback_as_csv(): void
    {
        $admin = $this->adminUser();
        $web = Career::query()->where('code', 'web_developer')->firstOrFail();

        $this->createCompletedSession(
            'Budi',
            $web,
            90,
            'Tanggapan uji CSV.',
            'Saran uji CSV.',
        );

        $response = $this->actingAs($admin)->get('/admin/feedbacks/export');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        $this->assertStringContainsString('Budi', $content);
        $this->assertStringContainsString('Web Developer', $content);
        $this->assertStringContainsString('Tanggapan uji CSV.', $content);
        $this->assertStringContainsString('Saran uji CSV.', $content);
    }

    private function adminUser(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function createCompletedSession(
        ?string $name,
        Career $career,
        ?int $score,
        ?string $comment,
        ?string $suggestion,
    ): TestSession {
        $session = TestSession::query()->create([
            'token' => (string) Str::uuid(),
            'visitor_name' => $name,
            'completed_at' => now(),
        ]);

        $session->results()->create([
            'career_id' => $career->id,
            'career_code' => $career->code,
            'career_name' => $career->name,
            'rank' => 1,
            'saw_score' => 0.85,
            'match_percentage' => 85.0,
            'contribution_breakdown' => [],
            'gap_summary' => null,
        ]);

        if ($score !== null) {
            $session->feedback()->create([
                'score' => $score,
                'comment' => $comment,
                'suggestion' => $suggestion,
            ]);
        }

        return $session;
    }
}
