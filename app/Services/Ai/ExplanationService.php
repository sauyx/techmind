<?php

namespace App\Services\Ai;

use App\Contracts\AiExplanationRepository;
use App\Data\AiExplanationResult;
use App\Models\AiExplanation;
use App\Models\TestSession;
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

        $driver = (string) config('techmind.ai.driver', 'template');

        if ($driver === 'template' || config('techmind.ai.api_key') === '' || config('techmind.ai.api_key') === null) {
            $result = $this->templateExplainer->explain($session);

            return $this->explanations->store($session, $result);
        }

        try {
            $result = $this->openAiExplainer->explain($session);
        } catch (Throwable) {
            $result = $this->templateExplainer->explain($session);
            $result = new AiExplanationResult(
                $result->content,
                'fallback_template',
                null,
                true,
                $result->latencyMs,
            );
        }

        return $this->explanations->store($session, $result);
    }
}
