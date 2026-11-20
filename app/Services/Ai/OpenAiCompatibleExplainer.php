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

        $ratings = [];
        foreach ($session->answers as $answer) {
            $ratings[(string) $answer->criterion_code] = (int) $answer->rating;
        }

        $topCareers = $session->results->take(3)->map(fn ($result) => [
            'career_name' => (string) $result->career_name,
            'match_percentage' => (float) $result->match_percentage,
        ])->values()->all();

        $topScore = (float) ($session->results->first()?->saw_score ?? 0.0);
        $tiedTopNames = $session->results
            ->filter(fn ($result) => abs((float) $result->saw_score - $topScore) < 1e-9)
            ->pluck('career_name')
            ->all();
        $isTiedTop = count($tiedTopNames) > 1;

        $system = 'Anda adalah asisten karier yang ramah. Tulis penjelasan ringkas dalam Bahasa Indonesia, maksimal 180 kata, tanpa markdown berlebihan, tanpa emoji, dan tanpa mengidentifikasi identitas pengguna.';
        $tieNote = $isTiedTop
            ? ' Beberapa karier memiliki skor tertinggi yang setara: '.implode(', ', $tiedTopNames).'. Jangan menyebut satu karier sebagai paling cocok; sebutkan karier-karier yang setara dan sarankan ulangi tes.'
            : '';
        $user = 'Jawab hanya dengan paragraf penjelasan. Data anonim: skor kriteria 1-5 '.json_encode($ratings, JSON_THROW_ON_ERROR).' dan tiga karier teratas '.json_encode($topCareers, JSON_THROW_ON_ERROR).'.'.$tieNote;

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
                $response = $this->client($attemptTimeout)->post(rtrim($baseUrl, '/').'/chat/completions', [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => $system],
                        ['role' => 'user', 'content' => $user],
                    ],
                    'max_tokens' => 350,
                    'temperature' => 0.4,
                ]);
            } catch (ConnectionException $e) {
                $this->lastError = $e->getMessage();
                if ($attempt < 2) {
                    usleep(500000);

                    continue;
                }
                throw new RuntimeException('Koneksi AI gagal: '.$this->lastError);
            }

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

        return new AiExplanationResult(
            trim($content),
            'openai_compatible',
            $model,
            false,
            $latencyMs,
            'success',
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
}
