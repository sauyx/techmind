<?php

namespace App\Console\Commands;

use App\Data\AiExplanationResult;
use App\Models\AiExplanation;
use App\Models\TestSession;
use App\Services\Ai\OpenAiCompatibleExplainer;
use App\Services\Ai\TemplateAiExplainer;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class AiCheck extends Command
{
    protected $signature = 'techmind:ai-check {--token=}';

    protected $description = 'Cek koneksi ke penyedia AI (tanpa menyimpan ke DB).';

    public function handle(): int
    {
        $token = $this->option('token');

        if ($token) {
            $session = \App\Models\TestSession::query()
                ->where('token', $token)
                ->with(['answers', 'results'])
                ->first();

            if (! $session) {
                $this->error('Token sesi tidak ditemukan.');

                return self::FAILURE;
            }
        } else {
            $session = $this->makeExampleSession();
        }

        $driver = (string) config('techmind.ai.driver', 'template');
        $baseUrl = (string) config('techmind.ai.base_url', '');
        $model = (string) config('techmind.ai.model', '');
        $host = $baseUrl !== '' ? ((string) (parse_url($baseUrl, PHP_URL_HOST) ?: $baseUrl)) : '-';

        $this->info('Driver: '.$driver);
        $this->info('Base URL host: '.$host);
        $this->info('Model: '.$model);

        if ($driver === 'template' || config('techmind.ai.api_key') === '' || config('techmind.ai.api_key') === null) {
            $explainer = app(TemplateAiExplainer::class);
            $startedAt = microtime(true);
            $result = $explainer->explain($session);
            $latency = (int) round((microtime(true) - $startedAt) * 1000);

            $this->info('Status HTTP: n/a (template)');
            $this->info('Latensi: '.$latency.' ms');
            $this->info('Attempts: '.$result->attempts);
            $this->line('Output (300 char): '.substr($result->content, 0, 300));

            return self::SUCCESS;
        }

        $explainer = app(OpenAiCompatibleExplainer::class);
        $startedAt = microtime(true);

        try {
            $result = $explainer->explain($session);
        } catch (\Throwable $e) {
            $latency = (int) round((microtime(true) - $startedAt) * 1000);
            $this->error('Gagal: '.$e->getMessage());
            $this->info('Status HTTP: '.($explainer->lastHttpStatus ?? 'n/a'));
            $this->info('Latensi: '.$latency.' ms');
            $this->info('Attempts: '.$explainer->lastAttempts);

            return self::FAILURE;
        }

        $latency = (int) round((microtime(true) - $startedAt) * 1000);

        $this->info('Status HTTP: '.($explainer->lastHttpStatus ?? 'n/a'));
        $this->info('Latensi: '.$latency.' ms');
        $this->info('Attempts: '.$result->attempts);
        $this->line('Output (300 char): '.substr($result->content, 0, 300));

        return self::SUCCESS;
    }

    private function makeExampleSession(): TestSession
    {
        $session = new TestSession(['token' => 'ai-check-example', 'visitor_name' => 'AI Check']);

        $criteria = app(\App\Contracts\CriterionRepository::class)->activeOrdered();
        $careers = app(\App\Contracts\CareerRepository::class)->activeWithProfiles();
        $career = $careers->first();

        $answers = new Collection();
        $results = new Collection();

        if ($career) {
            $scores = [];
            foreach ($career->careerProfiles as $profile) {
                if ($profile->criterion) {
                    $scores[(string) $profile->criterion->code] = (int) $profile->score;
                }
            }

            foreach ($criteria as $criterion) {
                $code = (string) $criterion->code;
                $answer = new \App\Models\TestAnswer([
                    'criterion_code' => $code,
                    'criterion_name' => (string) $criterion->name,
                    'rating' => (int) ($scores[$code] ?? 3),
                ]);
                $answers->push($answer);
            }

            $results->push(new \App\Models\TestResult([
                'career_code' => (string) $career->code,
                'career_name' => (string) $career->name,
                'rank' => 1,
                'saw_score' => 0.75,
                'match_percentage' => 75.0,
            ]));
        }

        $session->setRelation('answers', $answers);
        $session->setRelation('results', $results);

        return $session;
    }
}
