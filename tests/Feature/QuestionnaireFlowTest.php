<?php

namespace Tests\Feature;

use App\Models\CareerProfile;
use App\Models\Criterion;
use App\Models\TestSession;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class QuestionnaireFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ReferenceDataSeeder::class);
    }

    public function test_tes_page_returns_200_with_12_criteria_in_3_groups(): void
    {
        $response = $this->get('/tes');

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('public/test')
            ->has('criteria', 12)
        );
    }

    public function test_valid_submission_stores_session_answers_and_results(): void
    {
        $ratings = $this->validRatings();

        $response = $this->post('/tes', [
            'visitor_name' => 'Budi',
            'ratings' => $ratings,
        ]);

        $session = TestSession::first();

        $this->assertNotNull($session);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $session->token);
        $this->assertSame('Budi', $session->visitor_name);
        $this->assertNotNull($session->completed_at);

        $this->assertSame(12, $session->answers()->count());
        $this->assertSame(10, $session->results()->count());

        $response->assertRedirect(route('results.show', $session->token));
    }

    public function test_snapshot_is_isolated_from_later_admin_changes(): void
    {
        $ratings = $this->validRatings();

        $this->post('/tes', [
            'visitor_name' => 'Ani',
            'ratings' => $ratings,
        ]);

        $session = TestSession::first();
        $storedScore = (float) $session->results()->first()->saw_score;

        Criterion::query()->first()->update(['weight' => 2.00]);
        CareerProfile::query()->first()->update(['score' => 1]);

        $freshSession = TestSession::query()->where('token', $session->token)->first();
        $freshScore = (float) $freshSession->results()->first()->saw_score;

        $this->assertSame($storedScore, $freshScore);
    }

    public function test_submission_rejected_when_a_criterion_is_missing(): void
    {
        $ratings = $this->validRatings();
        unset($ratings['coding']);

        $this->from('/tes')
            ->post('/tes', [
                'visitor_name' => 'Budi',
                'ratings' => $ratings,
            ])
            ->assertRedirect('/tes')
            ->assertSessionHasErrors('ratings');
    }

    public function test_submission_rejected_when_rating_is_out_of_range(): void
    {
        $ratings = $this->validRatings();
        $ratings['coding'] = 6;

        $this->from('/tes')
            ->post('/tes', [
                'visitor_name' => 'Budi',
                'ratings' => $ratings,
            ])
            ->assertRedirect('/tes')
            ->assertSessionHasErrors('ratings.coding');
    }

    public function test_submission_rejected_when_all_ratings_are_identical(): void
    {
        $ratings = [];
        foreach (Criterion::query()->pluck('code') as $code) {
            $ratings[$code] = 3;
        }

        $this->from('/tes')
            ->post('/tes', [
                'visitor_name' => 'Budi',
                'ratings' => $ratings,
            ])
            ->assertRedirect('/tes')
            ->assertSessionHasErrors('ratings');
    }

    public function test_result_page_renders_full_results(): void
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
            ->has('results', 10)
            ->has('criteria', 12)
            ->has('careers', 10)
            ->has('explanation')
        );
    }

    private function validRatings(): array
    {
        $ratings = [];
        $index = 0;
        foreach (Criterion::query()->pluck('code') as $code) {
            $ratings[$code] = [5, 4, 3, 2, 4, 3, 2, 5, 3, 4, 2, 3][$index % 12];
            $index++;
        }

        return $ratings;
    }
}
