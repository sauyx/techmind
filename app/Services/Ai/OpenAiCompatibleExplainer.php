<?php

namespace App\Services\Ai;

use App\Contracts\AiExplainerInterface;
use App\Data\AiExplanationResult;
use App\Models\TestSession;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class OpenAiCompatibleExplainer implements AiExplainerInterface
{
    public function explain(TestSession $session): AiExplanationResult
    {
        $baseUrl = (string) config('techmind.ai.base_url');
        $apiKey = (string) config('techmind.ai.api_key');
        $model = (string) config('techmind.ai.model');
        $timeout = (int) config('techmind.ai.timeout', 6);

        if ($baseUrl === '' || $apiKey === '' || $model === '') {
            throw new RuntimeException('Konfigurasi driver openai_compatible belum lengkap.');
        }

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

        $startedAt = microtime(true);

        $response = $this->client($timeout)->post(rtrim($baseUrl, '/').'/chat/completions', [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $user],
            ],
            'max_tokens' => 350,
            'temperature' => 0.4,
        ]);

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
