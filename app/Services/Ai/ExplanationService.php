<?php

namespace App\Services\Ai;

use App\Contracts\AiExplanationRepository;
use App\Data\AiExplanationResult;
use App\Models\AiExplanation;
use App\Models\TestSession;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

final class ExplanationService
{
    public function __construct(
        private AiExplanationRepository $explanations,
        private TemplateAiExplainer $templateExplainer,
        private OpenAiCompatibleExplainer $openAiExplainer,
    ) {}

    public function getOrCreate(TestSession $session): AiExplanation
    {
        $existing = $this->explanations->findForSession($session);

        if ($existing) {
            return $existing;
        }

        $lock = Cache::lock('techmind:ai-explanation:'.$session->token, 10);

        try {
            $lock->block(5);
        } catch (LockTimeoutException) {
            $existing = $this->explanations->findForSession($session);
            if ($existing) {
                return $existing;
            }
        }

        try {
            $existing = $this->explanations->findForSession($session);
            if ($existing) {
                return $existing;
            }

            $driver = (string) config('techmind.ai.driver', 'template');

            if ($driver === 'template' || config('techmind.ai.api_key') === '' || config('techmind.ai.api_key') === null) {
                $result = $this->templateExplainer->explain($session);
                $stored = $this->explanations->store($session, $result);
                Log::channel('ai')->info('AI explanation generated', [
                    'token' => substr($session->token, 0, 8),
                    'driver' => $result->driverUsed,
                    'model' => $result->modelName,
                    'latency_ms' => $result->latencyMs,
                    'status' => $result->status,
                    'attempts' => $result->attempts,
                    'error' => $result->errorMessage,
                ]);

                return $stored;
            }

            try {
                $result = $this->openAiExplainer->explain($session);
            } catch (Throwable $e) {
                $result = $this->templateExplainer->explain($session);
                $result = new AiExplanationResult(
                    $result->content,
                    'fallback_template',
                    null,
                    true,
                    $result->latencyMs,
                    'fallback',
                    'json',
                    max(1, $this->openAiExplainer->lastAttempts),
                    $this->openAiExplainer->lastError ?? $e->getMessage(),
                );
            }

            $stored = $this->explanations->store($session, $result);
            Log::channel('ai')->info('AI explanation generated', [
                'token' => substr($session->token, 0, 8),
                'driver' => $result->driverUsed,
                'model' => $result->modelName,
                'latency_ms' => $result->latencyMs,
                'status' => $result->status,
                'attempts' => $result->attempts,
                'error' => $result->errorMessage,
            ]);

            return $stored;
        } finally {
            optional($lock)->release();
        }
    }
}
