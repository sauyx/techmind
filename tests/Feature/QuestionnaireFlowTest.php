<?php

namespace Tests\Feature;

use App\Models\TestResult;
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

    public function test_can_start_anonymous_test_session(): void
    {
        $response = $this->post('/tests', [
            'visitor_name' => 'Budi',
        ]);

        $session = TestSession::first();

        $this->assertNotNull($session);
        $this->assertSame('Budi', $session->visitor_name);
        $this->assertSame('in_progress', $session->status);
        $response->assertRedirect(route('tests.show', $session->token));
    }

    public function test_can_view_questionnaire_step(): void
    {
        $session = TestSession::factory()->create([
            'visitor_name' => 'Ani',
            'token' => 'test-token-123',
        ]);

        $response = $this->get('/tests/test-token-123');

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('test/show')
            ->where('session.token', 'test-token-123')
            ->has('criteria', 12)
        );
    }

    public function test_submitting_answers_calculates_and_redirects_to_results(): void
    {
        $session = TestSession::factory()->create([
            'token' => 'test-token-456',
        ]);

        $ratings = [
            'logic_problem_solving' => 5,
            'data_analysis' => 3,
            'math_statistics' => 3,
            'attention_detail' => 4,
            'visual_design' => 1,
            'creativity' => 2,
            'user_empathy' => 1,
            'communication' => 2,
            'coding' => 5,
            'network_infra' => 4,
            'security_awareness' => 4,
            'business_process' => 3,
        ];

        $response = $this->post('/tests/test-token-456/answers', [
            'ratings' => $ratings,
        ]);

        $response->assertRedirect(route('results.show', 'test-token-456'));

        $this->assertDatabaseHas('test_sessions', [
            'token' => 'test-token-456',
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('test_answers', [
            'test_session_id' => $session->id,
            'criterion_code' => 'coding',
            'rating' => 5,
        ]);

        $result = TestResult::where('test_session_id', $session->id)->first();
        $this->assertNotNull($result);
        $this->assertSame('backend_developer', $result->top_career_code);
    }

    public function test_can_view_result_page(): void
    {
        $session = TestSession::factory()->create([
            'token' => 'test-token-789',
        ]);

        $ratings = [
            'logic_problem_solving' => 4,
            'data_analysis' => 5,
            'math_statistics' => 5,
            'attention_detail' => 4,
            'visual_design' => 2,
            'creativity' => 2,
            'user_empathy' => 2,
            'communication' => 4,
            'coding' => 3,
            'network_infra' => 1,
            'security_awareness' => 2,
            'business_process' => 3,
        ];

        $this->post('/tests/test-token-789/answers', [
            'ratings' => $ratings,
        ]);

        $response = $this->get('/results/test-token-789');

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('result/show')
            ->where('result.top_career_code', 'data_analyst')
            ->has('result.ranking', 10)
            ->has('gap_analysis')
            ->has('explanation')
        );
    }
}
