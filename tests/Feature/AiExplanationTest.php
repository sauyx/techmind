<?php

namespace Tests\Feature;

use App\Models\Criterion;
use App\Models\TestSession;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia;
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
        $this->assertStringContainsString('ringkasan', $explanation->content);

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

        $json = json_encode([
            'ringkasan' => 'Hasil rekomendasi ini menunjukkan kecocokan yang layak dan dapat menjadi titik awal eksplorasi karier bagi Anda.',
            'alasan' => ['Anda memiliki kekuatan pada pemecahan masalah.', 'Profil ini juga selaras dengan kebutuhan karier teratas.'],
            'area_pengembangan' => [],
            'langkah_awal' => ['Mulai mempelajari dasar yang relevan.', 'Latih keterampilan melalui proyek kecil.'],
            'alternatif' => 'Karier peringkat kedua dan ketiga dapat Anda pertimbangkan sebagai pilihan alternatif.',
        ]);

        Http::fake([
            'https://api.openai.test/v1/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => $json]],
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
        $this->assertStringContainsString('ringkasan', $explanation->content);

        Http::assertSentCount(1);

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
            'https://api.openai.test/v1/*' => Http::response('Server Error', 500),
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
            'https://api.openai.test/v1/*' => fn () => throw new ConnectionException('Timeout'),
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

        $this->assertLessThanOrEqual(2, count(Http::recorded()));
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
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('public/result')
                ->loadDeferredProps(function (AssertableInertia $page) {
                    $page->has('ai_explanation')
                        ->where('ai_explanation.content', fn ($value) => str_contains((string) $value, 'kecocokan'));
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
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('public/result')
                ->loadDeferredProps(function (AssertableInertia $page) {
                    $page->has('ai_explanation');
                })
            );

        $this->get('/hasil/'.$session->token)
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('public/result')
                ->loadDeferredProps(function (AssertableInertia $page) {
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

        $json = json_encode([
            'ringkasan' => 'Hasil ini menunjukkan kecocokan yang dapat dipertimbangkan sebagai rekomendasi awal eksplorasi karier bagi Anda.',
            'alasan' => ['Kekuatan Anda selaras dengan profil karier teratas.', 'Kemampuan ini mendukung jalur karier yang direkomendasikan.'],
            'area_pengembangan' => [],
            'langkah_awal' => ['Mulai mempelajari keterampilan dasar.', 'Buat satu proyek kecil minggu ini.'],
            'alternatif' => 'Karier peringkat kedua dan ketiga layak dipertimbangkan bersama.',
        ]);

        Http::fakeSequence()
            ->push(['error' => 'server error'], 500)
            ->push(['choices' => [['message' => ['content' => $json]]]], 200);

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
            throw new ConnectionException('Connection timed out');
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

    public function test_valid_json_output_is_stored_as_json(): void
    {
        config([
            'techmind.ai.driver' => 'openai_compatible',
            'techmind.ai.base_url' => 'https://api.openai.test/v1',
            'techmind.ai.api_key' => 'test-secret-key',
            'techmind.ai.model' => 'gpt-4o-mini',
            'techmind.ai.timeout' => 2,
            'techmind.ai.json_mode' => true,
        ]);

        $json = json_encode([
            'ringkasan' => 'Ringkasan singkat ini menjelaskan kecocokan hasil tes sebagai rekomendasi awal untuk eksplorasi karier Anda.',
            'alasan' => ['Kekuatan Anda selaras dengan profil karier teratas.', 'Kecocokan ini muncul dari jawaban pada kriteria utama.'],
            'area_pengembangan' => [],
            'langkah_awal' => ['Pelajari keterampilan dasar karier.', 'Mulai proyek kecil yang terukur.'],
            'alternatif' => 'Karier peringkat kedua dan ketiga layak dipertimbangkan bersama.',
        ]);

        Http::fake([
            'https://api.openai.test/v1/*' => Http::response([
                'choices' => [['message' => ['content' => $json]]],
            ], 200),
        ]);

        $this->post('/tes', [
            'visitor_name' => 'Budi',
            'ratings' => $this->validRatingsForAi(),
        ]);

        $session = TestSession::first();
        $this->get('/hasil/'.$session->token)->assertOk();

        $explanation = $session->refresh()->aiExplanation;
        $this->assertSame('json', $explanation->format);
        $this->assertStringContainsString('ringkasan', $explanation->content);
    }

    public function test_fenced_json_output_is_parsed(): void
    {
        config([
            'techmind.ai.driver' => 'openai_compatible',
            'techmind.ai.base_url' => 'https://api.openai.test/v1',
            'techmind.ai.api_key' => 'test-secret-key',
            'techmind.ai.model' => 'gpt-4o-mini',
            'techmind.ai.timeout' => 2,
            'techmind.ai.json_mode' => true,
        ]);

        $json = json_encode([
            'ringkasan' => 'Ringkasan.',
            'alasan' => ['A.'],
            'area_pengembangan' => [],
            'langkah_awal' => ['L.'],
            'alternatif' => 'Alt.',
        ]);

        Http::fake([
            'https://api.openai.test/v1/*' => Http::response([
                'choices' => [['message' => ['content' => "```json\n".$json."\n```"]]],
            ], 200),
        ]);

        $this->post('/tes', [
            'visitor_name' => 'Budi',
            'ratings' => $this->validRatingsForAi(),
        ]);

        $session = TestSession::first();
        $this->get('/hasil/'.$session->token)->assertOk();

        $explanation = $session->refresh()->aiExplanation;
        $this->assertSame('json', $explanation->format);
    }

    public function test_forged_percentage_falls_back(): void
    {
        config([
            'techmind.ai.driver' => 'openai_compatible',
            'techmind.ai.base_url' => 'https://api.openai.test/v1',
            'techmind.ai.api_key' => 'test-secret-key',
            'techmind.ai.model' => 'gpt-4o-mini',
            'techmind.ai.timeout' => 2,
        ]);

        $json = json_encode([
            'ringkasan' => 'Ringkasan hasil ini menjelaskan kecocokan rekomendasi sebagai titik awal eksplorasi karier bagi Anda.',
            'alasan' => ['Kekuatan Anda mendukung profil karier teratas.', 'Jawaban tes selaras dengan kriteria utama.'],
            'area_pengembangan' => [],
            'langkah_awal' => ['Pelajari dasar keterampilan.', 'Buat proyek kecil minggu ini.'],
            'alternatif' => 'Karier lain memiliki kecocokan 99.9%.',
        ]);

        Http::fake(['https://api.openai.test/v1/*' => Http::response(['choices' => [['message' => ['content' => $json]]]], 200)]);
        $this->post('/tes', ['visitor_name' => 'Budi', 'ratings' => $this->validRatingsForAi()]);
        $session = TestSession::first();
        $this->get('/hasil/'.$session->token)->assertOk();
        $this->assertSame('fallback', $session->refresh()->aiExplanation->status);
    }

    public function test_tied_top_must_not_say_paling_cocok(): void
    {
        config([
            'techmind.ai.driver' => 'openai_compatible',
            'techmind.ai.base_url' => 'https://api.openai.test/v1',
            'techmind.ai.api_key' => 'test-secret-key',
            'techmind.ai.model' => 'gpt-4o-mini',
            'techmind.ai.timeout' => 2,
        ]);

        $json = json_encode([
            'ringkasan' => 'Karier A adalah paling cocok untuk Anda, meskipun hasil tes menunjukkan tingkat kecocokan yang setara dengan pilihan lain.',
            'alasan' => ['Kekuatan Anda mendukung profil karier teratas.', 'Jawaban tes menunjukkan pola yang cukup merata.'],
            'area_pengembangan' => [],
            'langkah_awal' => ['Pelajari dasar keterampilan.', 'Buat proyek kecil minggu ini.'],
            'alternatif' => 'Karier peringkat kedua dan ketiga layak dipertimbangkan.',
        ]);

        Http::fake(['https://api.openai.test/v1/*' => Http::response(['choices' => [['message' => ['content' => $json]]]], 200)]);
        $ratings = array_fill_keys(Criterion::query()->pluck('code')->all(), 3);
        $this->post('/tes', ['visitor_name' => 'Budi', 'ratings' => $ratings]);
        $session = TestSession::first();
        $this->get('/hasil/'.$session->token)->assertOk();
        $this->assertSame('fallback', $session->refresh()->aiExplanation->status);
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
