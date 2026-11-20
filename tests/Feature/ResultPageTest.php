<?php

namespace Tests\Feature;

use App\Models\CareerProfile;
use App\Models\Criterion;
use App\Models\TestResult;
use App\Models\TestSession;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ResultPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ReferenceDataSeeder::class);
    }

    public function test_result_page_returns_200_with_full_props(): void
    {
        $ratings = $this->validRatings();

        $this->post('/tes', [
            'visitor_name' => 'Budi',
            'ratings' => $ratings,
        ]);

        $session = TestSession::first();

        $response = $this->get('/hasil/'.$session->token);

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('public/result')
            ->where('session.visitor_name', 'Budi')
            ->has('results', 10)
            ->has('criteria', 12)
            ->has('careers', 10)
            ->has('ratings')
            ->has('results.0.gap_summary')
            ->loadDeferredProps(function (AssertableInertia $page) {
                $page->has('ai_explanation');
            })
        );
    }

    public function test_invalid_token_returns_404(): void
    {
        $this->get('/hasil/00000000-0000-0000-0000-000000000000')->assertNotFound();
    }

    public function test_all_equal_ratings_marks_results_as_tied(): void
    {
        $ratings = [];
        foreach (Criterion::query()->pluck('code') as $code) {
            $ratings[$code] = 3;
        }

        $this->post('/tes', [
            'visitor_name' => 'Budi',
            'ratings' => $ratings,
        ]);

        $session = TestSession::first();

        $this->get('/hasil/'.$session->token)
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('public/result')
                ->where('results.0.is_tied', true)
            );
    }

    public function test_result_uses_snapshot_and_ignores_later_weight_changes(): void
    {
        $ratings = $this->validRatings();

        $this->post('/tes', [
            'visitor_name' => 'Ani',
            'ratings' => $ratings,
        ]);

        $session = TestSession::first();
        $topBefore = TestResult::query()
            ->where('test_session_id', $session->id)
            ->orderBy('rank')
            ->first();

        Criterion::query()->first()->update(['weight' => 2.00]);
        CareerProfile::query()->first()->update(['score' => 1]);

        $response = $this->get('/hasil/'.$session->token);

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->where('results.0.saw_score', (float) $topBefore->saw_score)
            ->where('results.0.match_percentage', (string) $topBefore->match_percentage)
        );
    }

    private function validRatings(): array
    {
        $ratings = [];
        foreach (Criterion::query()->pluck('code') as $index => $code) {
            $ratings[$code] = [5, 4, 3, 2, 4, 3, 2, 5, 3, 4, 2, 3][$index % 12];
        }

        return $ratings;
    }
}
