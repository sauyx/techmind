<?php

namespace Tests\Feature;

use App\Models\Criterion;
use App\Models\TestSession;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiExplanationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ReferenceDataSeeder::class);
    }

    public function test_template_driver_generates_and_persists_explanation(): void
    {
        config([
            'techmind.ai.driver' => 'template',
            'techmind.ai.api_key' => null,
        ]);

        $session = $this->createSession('Budi');

        $response = $this->get('/hasil/'.$session->token);

        $response->assertOk();
        $this->assertDatabaseCount('ai_explanations', 1);

        $explanation = $session->refresh()->aiExplanation;
        $this->assertNotNull($explanation);
        $this->assertSame('template', $explanation->driver_used);
        $this->assertFalse($explanation->is_fallback);
        $this->assertStringContainsString('skor kecocokan', $explanation->content);

        $firstContent = $explanation->content;

        $response2 = $this->get('/hasil/'.$session->token);
        $response2->assertOk();
        $this->assertDatabaseCount('ai_explanations', 1);
        $this->assertSame($firstContent, $session->refresh()->aiExplanation->content);
    }

    public function test_openai_compatible_driver_calls_endpoint_without_visitor_name(): void
    {
        config([
            'techmind.ai.driver' => 'openai_compatible',
            'techmind.ai.base_url' => 'https://api.openai.test/v1',
            'techmind.ai.api_key' => 'test-secret-key',
            'techmind.ai.model' => 'gpt-4o-mini',
            'techmind.ai.timeout' => 2,
        ]);

        Http::fake([
            'https://api.openai.test/v1/chat/completions' => Http::response([
                'choices' => [
                    ['message' => ['content' => 'Analisis AI khusus: Anda sangat cocok sebagai developer.']],
                ],
            ], 200),
        ]);

        $session = $this->createSession('RahasiaUser');

        $response = $this->get('/hasil/'.$session->token);

        $response->assertOk();
        $this->assertDatabaseCount('ai_explanations', 1);

        $explanation = $session->refresh()->aiExplanation;
        $this->assertSame('openai_compatible', $explanation->driver_used);
        $this->assertSame('gpt-4o-mini', $explanation->model_name);
        $this->assertFalse($explanation->is_fallback);
        $this->assertStringContainsString('Analisis AI khusus', $explanation->content);

        Http::assertSent(function ($request) {
            if ($request->url() !== 'https://api.openai.test/v1/chat/completions') {
                return false;
            }

            if (! $request->hasHeader('Authorization', 'Bearer test-secret-key')) {
                return false;
            }

            $body = json_encode($request->data());

            return ! str_contains((string) $body, 'RahasiaUser')
                && str_contains((string) $body, 'skor kriteria 1-5');
        });
    }

    public function test_openai_compatible_falls_back_to_template_on_error(): void
    {
        config([
            'techmind.ai.driver' => 'openai_compatible',
            'techmind.ai.base_url' => 'https://api.openai.test/v1',
            'techmind.ai.api_key' => 'test-secret-key',
            'techmind.ai.model' => 'gpt-4o-mini',
            'techmind.ai.timeout' => 2,
        ]);

        Http::fake([
            'https://api.openai.test/v1/chat/completions' => Http::response('Server Error', 500),
        ]);

        $session = $this->createSession('Budi');

        $response = $this->get('/hasil/'.$session->token);

        $response->assertOk();
        $this->assertDatabaseCount('ai_explanations', 1);

        $explanation = $session->refresh()->aiExplanation;
        $this->assertSame('fallback_template', $explanation->driver_used);
        $this->assertTrue($explanation->is_fallback);
        $this->assertNotEmpty($explanation->content);
    }

    public function test_openai_compatible_falls_back_on_timeout(): void
    {
        config([
            'techmind.ai.driver' => 'openai_compatible',
            'techmind.ai.base_url' => 'https://api.openai.test/v1',
            'techmind.ai.api_key' => 'test-secret-key',
            'techmind.ai.model' => 'gpt-4o-mini',
            'techmind.ai.timeout' => 2,
        ]);

        Http::fake([
            'https://api.openai.test/v1/chat/completions' => fn () => throw new ConnectionException('Timeout'),
        ]);

        $session = $this->createSession('Budi');

        $response = $this->get('/hasil/'.$session->token);

        $response->assertOk();

        $explanation = $session->refresh()->aiExplanation;
        $this->assertSame('fallback_template', $explanation->driver_used);
        $this->assertTrue($explanation->is_fallback);
    }

    public function test_template_driver_handles_tied_top(): void
    {
        config([
            'techmind.ai.driver' => 'template',
            'techmind.ai.api_key' => null,
        ]);

        $ratings = [];
        foreach (Criterion::query()->pluck('code') as $code) {
            $ratings[$code] = 3;
        }

        $this->post('/tes', [
            'visitor_name' => 'Budi',
            'ratings' => $ratings,
        ]);

        $session = TestSession::first();

        $this->get('/hasil/'.$session->token)->assertOk();
        $this->assertDatabaseCount('ai_explanations', 1);
        $explanation = $session->refresh()->aiExplanation;
        $this->assertStringContainsString('setara', strtolower($explanation->content));
    }

    public function test_result_page_does_not_call_ai_on_initial_request(): void
    {
        config([
            'techmind.ai.driver' => 'openai_compatible',
            'techmind.ai.base_url' => 'https://api.openai.test/v1',
            'techmind.ai.api_key' => 'test-secret-key',
            'techmind.ai.model' => 'gpt-4o-mini',
            'techmind.ai.timeout' => 2,
        ]);

        Http::fake([
            'https://api.openai.test/v1/*' => Http::response([
                'choices' => [['message' => ['content' => 'Penjelasan AI.']]],
            ], 200),
        ]);

        $this->post('/tes', [
            'visitor_name' => 'Budi',
            'ratings' => $this->validRatingsForAi(),
        ]);

        $session = TestSession::first();
        $before = count(Http::recorded());

        $this->get('/hasil/'.$session->token)->assertOk();

        $this->assertSame($before, count(Http::recorded()));
    }

    public function test_deferred_prop_returns_explanation(): void
    {
        config([
            'techmind.ai.driver' => 'template',
            'techmind.ai.api_key' => null,
        ]);

        $this->post('/tes', [
            'visitor_name' => 'Budi',
            'ratings' => $this->validRatingsForAi(),
        ]);

        $session = TestSession::first();

        $this->get('/hasil/'.$session->token)
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
                ->component('public/result')
                ->loadDeferredProps(function (\Inertia\Testing\AssertableInertia $page) {
                    $page->has('ai_explanation')
                        ->where('ai_explanation.content', fn ($value) => str_contains((string) $value, 'skor kecocokan'));
                })
            );

        $this->assertDatabaseCount('ai_explanations', 1);
    }

    public function test_no_duplicate_explanation(): void
    {
        config([
            'techmind.ai.driver' => 'template',
            'techmind.ai.api_key' => null,
        ]);

        $this->post('/tes', [
            'visitor_name' => 'Budi',
            'ratings' => $this->validRatingsForAi(),
        ]);

        $session = TestSession::first();

        $this->get('/hasil/'.$session->token)
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
                ->component('public/result')
                ->loadDeferredProps(function (\Inertia\Testing\AssertableInertia $page) {
                    $page->has('ai_explanation');
                })
            );

        $this->get('/hasil/'.$session->token)
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
                ->component('public/result')
                ->loadDeferredProps(function (\Inertia\Testing\AssertableInertia $page) {
                    $page->has('ai_explanation');
                })
            );

        $this->assertDatabaseCount('ai_explanations', 1);
    }

    public function test_retries_on_500_then_succeeds(): void
    {
        config([
            'techmind.ai.driver' => 'openai_compatible',
            'techmind.ai.base_url' => 'https://api.openai.test/v1',
            'techmind.ai.api_key' => 'test-secret-key',
            'techmind.ai.model' => 'gpt-4o-mini',
            'techmind.ai.timeout' => 2,
        ]);

        Http::fakeSequence()
            ->push(['error' => 'server error'], 500)
            ->push(['choices' => [['message' => ['content' => 'OK setelah retry.']]]], 200);

        $this->post('/tes', [
            'visitor_name' => 'Budi',
            'ratings' => $this->validRatingsForAi(),
        ]);

        $session = TestSession::first();

        $this->get('/hasil/'.$session->token)->assertOk();

        Http::assertSentCount(2);

        $explanation = $session->refresh()->aiExplanation;
        $this->assertSame('success', $explanation->status);
        $this->assertSame(2, (int) $explanation->attempts);
    }

    public function test_401_directly_falls_back_without_retry(): void
    {
        config([
            'techmind.ai.driver' => 'openai_compatible',
            'techmind.ai.base_url' => 'https://api.openai.test/v1',
            'techmind.ai.api_key' => 'test-secret-key',
            'techmind.ai.model' => 'gpt-4o-mini',
            'techmind.ai.timeout' => 2,
        ]);

        Http::fake([
            'https://api.openai.test/v1/*' => Http::response(['error' => 'unauthorized'], 401),
        ]);

        $this->post('/tes', [
            'visitor_name' => 'Budi',
            'ratings' => $this->validRatingsForAi(),
        ]);

        $session = TestSession::first();

        $this->get('/hasil/'.$session->token)->assertOk();

        Http::assertSentCount(1);

        $explanation = $session->refresh()->aiExplanation;
        $this->assertSame('fallback', $explanation->status);
        $this->assertSame(1, (int) $explanation->attempts);
        $this->assertNotNull($explanation->error_message);
    }

    public function test_timeout_twice_falls_back_with_error_message(): void
    {
        config([
            'techmind.ai.driver' => 'openai_compatible',
            'techmind.ai.base_url' => 'https://api.openai.test/v1',
            'techmind.ai.api_key' => 'test-secret-key',
            'techmind.ai.model' => 'gpt-4o-mini',
            'techmind.ai.timeout' => 1,
        ]);

        Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException('Connection timed out');
        });

        $this->post('/tes', [
            'visitor_name' => 'Budi',
            'ratings' => $this->validRatingsForAi(),
        ]);

        $session = TestSession::first();

        $this->get('/hasil/'.$session->token)->assertOk();

        $explanation = $session->refresh()->aiExplanation;
        $this->assertSame('fallback', $explanation->status);
        $this->assertSame(2, (int) $explanation->attempts);
        $this->assertNotNull($explanation->error_message);
    }

    private function validRatingsForAi(): array
    {
        $ratings = [];
        $index = 0;
        foreach (Criterion::query()->pluck('code') as $code) {
            $ratings[$code] = [5, 4, 3, 2, 4, 3, 2, 5, 3, 4, 2, 3][$index % 12];
            $index++;
        }

        return $ratings;
    }

    private function createSession(?string $name = 'Budi'): TestSession
    {
        $ratings = [];
        $index = 0;
        foreach (Criterion::query()->pluck('code') as $code) {
            $ratings[$code] = [5, 4, 3, 2, 4, 3, 2, 5, 3, 4, 2, 3][$index % 12];
            $index++;
        }

        $this->post('/tes', [
            'visitor_name' => $name,
            'ratings' => $ratings,
        ]);

        return TestSession::first();
    }
}
