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
