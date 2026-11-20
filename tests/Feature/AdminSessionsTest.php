<?php

namespace Tests\Feature;

use App\Models\TestSession;
use App\Models\User;
use App\Models\VisitorFeedback;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSessionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ReferenceDataSeeder::class);
    }

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true, 'email_verified_at' => now()]);
    }

    public function test_non_admin_cannot_access_sessions(): void
    {
        $this->get('/admin/sesi')->assertRedirect(route('login'));

        $nonAdmin = User::factory()->create();
        $this->actingAs($nonAdmin)->get('/admin/sesi')->assertForbidden();
    }

    public function test_delete_session_cascades_relations(): void
    {
        $admin = $this->admin();

        $this->post('/tes', [
            'visitor_name' => 'Budi',
            'ratings' => $this->validRatings(),
        ]);

        $session = TestSession::first();
        VisitorFeedback::query()->create(['test_session_id' => $session->id, 'score' => 90]);

        $this->actingAs($admin)
            ->delete('/admin/sesi/'.$session->id)
            ->assertRedirect('/admin/sesi');

        $this->assertDatabaseMissing('test_sessions', ['id' => $session->id]);
        $this->assertDatabaseMissing('test_answers', ['test_session_id' => $session->id]);
        $this->assertDatabaseMissing('test_results', ['test_session_id' => $session->id]);
    }

    private function validRatings(): array
    {
        $ratings = [];
        foreach (\App\Models\Criterion::query()->pluck('code') as $index => $code) {
            $ratings[$code] = [5, 4, 3, 2, 4, 3, 2, 5, 3, 4, 2, 3][$index % 12];
        }

        return $ratings;
    }
}
