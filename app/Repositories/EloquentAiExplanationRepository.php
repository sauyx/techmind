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

    public function summary(int $days = 7): array
    {
        $since = now()->subDays($days);
        $base = AiExplanation::query()->where('created_at', '>=', $since);

        $total = (clone $base)->count();
        $success = (clone $base)->where('status', 'success')->count();
        $fallback = (clone $base)->where('status', 'fallback')->count();
        $average = (clone $base)->avg('latency_ms');

        return [
            'total' => $total,
            'success' => $success,
            'fallback' => $fallback,
            'average_latency_ms' => $average === null ? null : round((float) $average, 1),
        ];
    }

    public function store(TestSession $session, AiExplanationResult $result): AiExplanation
    {
        return AiExplanation::query()->create([
            'test_session_id' => $session->id,
            'driver_used' => $result->driverUsed,
            'status' => $result->status,
            'format' => $result->format,
            'attempts' => $result->attempts,
            'model_name' => $result->modelName,
            'content' => $result->content,
            'error_message' => $result->errorMessage,
            'is_fallback' => $result->isFallback,
            'latency_ms' => $result->latencyMs,
        ]);
    }
}
