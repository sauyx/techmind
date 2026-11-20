<?php

namespace App\Services\Ai;

use App\Contracts\AiExplainerInterface;
use App\Data\AiExplanationResult;
use App\Models\TestSession;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class OpenAiCompatibleExplainer implements AiExplainerInterface
{
    public int $lastAttempts = 1;

    public ?string $lastError = null;

    public ?int $lastHttpStatus = null;

    public function explain(TestSession $session): AiExplanationResult
    {
        $baseUrl = (string) config('techmind.ai.base_url');
        $apiKey = (string) config('techmind.ai.api_key');
        $model = (string) config('techmind.ai.model');
        $timeout = (int) config('techmind.ai.timeout', 6);

        if ($baseUrl === '' || $apiKey === '' || $model === '') {
            throw new RuntimeException('Konfigurasi driver openai_compatible belum lengkap.');
        }

        $startedAt = microtime(true);

        $payloadBuilder = new ExplanationPayloadBuilder;
        $payload = $payloadBuilder->build($session);
        $system = @file_get_contents(base_path('resources/prompts/explanation_system.txt')) ?: '';

        $user = "Data hasil tes:\n".json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        $requestBody = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $user],
            ],
            'max_tokens' => 700,
            'temperature' => 0.3,
        ];

        if ((bool) config('techmind.ai.json_mode', false)) {
            $requestBody['response_format'] = ['type' => 'json_object'];
        }

        $deadline = microtime(true) + $timeout + 2;
        $response = null;
        $this->lastAttempts = 0;
        $this->lastError = null;

        for ($attempt = 1; $attempt <= 2; $attempt++) {
            $this->lastAttempts = $attempt;
            $attemptTimeout = max(1, (int) floor($deadline - microtime(true)));
            if ($attemptTimeout < 1) {
                $attemptTimeout = 1;
            }

            try {
                $response = $this->client($attemptTimeout)->post(rtrim($baseUrl, '/').'/chat/completions', $requestBody);
            } catch (ConnectionException $e) {
                $this->lastError = $e->getMessage();
                if ($attempt < 2) {
                    usleep(500000);

                    continue;
                }
                throw new RuntimeException('Koneksi AI gagal: '.$this->lastError);
            }

            $this->lastHttpStatus = $response->status();

            if ($response->successful()) {
                break;
            }

            $status = $response->status();
            $retryable = $status === 429 || $status >= 500;

            if ($retryable && $attempt < 2) {
                $this->lastError = 'HTTP '.$status;
                usleep(500000);

                continue;
            }

            $this->lastError = 'HTTP '.$status;
            $response->throw();
            break;
        }

        $latencyMs = (int) round((microtime(true) - $startedAt) * 1000);

        $content = $this->extractContent($response);

        if ($content === null || trim($content) === '') {
            throw new RuntimeException('Respons AI kosong.');
        }

        try {
            $parsed = $this->parseAndValidateJson($content, $payload);
        } catch (RuntimeException $e) {
            $reason = $e->getMessage();
            $this->lastError = $reason;
            throw new RuntimeException($reason);
        }

        return new AiExplanationResult(
            json_encode($parsed, JSON_UNESCAPED_UNICODE),
            'openai_compatible',
            $model,
            false,
            $latencyMs,
            'success',
            'json',
            $this->lastAttempts,
            null,
        );
    }

    private function client(int $timeout): PendingRequest
    {
        return Http::timeout($timeout)->withToken((string) config('techmind.ai.api_key'))->acceptJson();
    }

    private function extractContent(Response $response): ?string
    {
        $response->throw();

        $data = $response->json();

        if (! is_array($data)) {
            return null;
        }

        $text = $data['choices'][0]['message']['content'] ?? null;

        return is_string($text) ? $text : null;
    }

    private function parseAndValidateJson(string $content, array $payload): array
    {
        $content = trim($content);
        $content = preg_replace('/^```(?:json)?\s*/i', '', $content) ?? $content;
        $content = preg_replace('/\s*```$/', '', $content) ?? $content;

        $data = json_decode(trim($content), true);
        if (! is_array($data)) {
            throw new RuntimeException('invalid_output: json tidak valid');
        }

        foreach (['ringkasan', 'alasan', 'area_pengembangan', 'langkah_awal', 'alternatif'] as $key) {
            if (! array_key_exists($key, $data)) {
                throw new RuntimeException('invalid_output: skema kurang field '.$key);
            }
        }

        if (! is_string($data['ringkasan']) || ! is_string($data['alternatif'])) {
            throw new RuntimeException('invalid_output: tipe field ringkasan/alternatif tidak valid');
        }
        foreach (['alasan', 'area_pengembangan', 'langkah_awal'] as $key) {
            if (! is_array($data[$key]) || array_filter($data[$key], fn ($item) => ! is_string($item))) {
                throw new RuntimeException('invalid_output: tipe array '.$key.' tidak valid');
            }
        }
        if (count($data['alasan']) < 2 || count($data['alasan']) > 3
            || count($data['area_pengembangan']) > 2
            || count($data['langkah_awal']) < 2 || count($data['langkah_awal']) > 3) {
            throw new RuntimeException('invalid_output: jumlah item tidak sesuai skema');
        }

        $ringkasanWords = str_word_count($data['ringkasan']);
        if ($ringkasanWords < 14 || $ringkasanWords > 78) {
            throw new RuntimeException('invalid_output: panjang ringkasan tidak wajar');
        }
        foreach ($data['alasan'] as $item) {
            if (str_word_count($item) > 33) {
                throw new RuntimeException('invalid_output: alasan terlalu panjang');
            }
        }
        foreach ($data['area_pengembangan'] as $item) {
            if (str_word_count($item) > 33) {
                throw new RuntimeException('invalid_output: area_pengembangan terlalu panjang');
            }
        }
        foreach ($data['langkah_awal'] as $item) {
            if (str_word_count($item) > 26) {
                throw new RuntimeException('invalid_output: langkah_awal terlalu panjang');
            }
        }

        if (($payload['is_tied_top'] ?? false)
            && str_contains(mb_strtolower($data['ringkasan'].' '.$data['alternatif']), 'paling cocok')) {
            throw new RuntimeException('invalid_output: paling cocok pada seri');
        }

        $allowedPercentages = [];
        $allowedRounded = [];
        foreach (($payload['top_careers'] ?? []) as $career) {
            $value = (float) ($career['match_percentage'] ?? 0);
            $allowedPercentages[(string) $value] = true;
            $allowedPercentages[str_replace('.', ',', (string) $value)] = true;
            $allowedRounded[(string) round($value)] = true;
        }

        $allText = json_encode($data, JSON_UNESCAPED_UNICODE);
        preg_match_all('/([0-9]+(?:[.,][0-9]+)?)(?:\s*)%/', (string) $allText, $matches);
        foreach ($matches[1] ?? [] as $percentage) {
            $normalized = str_replace(',', '.', $percentage);
            $rounded = (string) round((float) $normalized);
            if (isset($allowedPercentages[$normalized]) || isset($allowedPercentages[$percentage]) || isset($allowedRounded[$rounded])) {
                continue;
            }
            throw new RuntimeException('invalid_output: persentase karangan '.$percentage.'%');
        }

        return $data;
    }
}
