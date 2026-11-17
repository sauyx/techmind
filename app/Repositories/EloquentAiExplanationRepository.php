<?php

namespace App\Repositories;

use App\Contracts\AiExplanationRepository;
use App\Data\AiExplanationResult;
use App\Models\AiExplanation;
use App\Models\TestSession;

class EloquentAiExplanationRepository implements AiExplanationRepository
{
    public function findForSession(TestSession $session): ?AiExplanation
    {
        return AiExplanation::query()
            ->where('test_session_id', $session->id)
            ->first();
    }

    public function store(TestSession $session, AiExplanationResult $result): AiExplanation
    {
        return AiExplanation::query()->create([
            'test_session_id' => $session->id,
            'driver_used' => $result->driverUsed,
            'model_name' => $result->modelName,
            'content' => $result->content,
            'is_fallback' => $result->isFallback,
            'latency_ms' => $result->latencyMs,
        ]);
    }
}
