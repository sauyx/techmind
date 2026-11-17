<?php

namespace App\Repositories;

use App\Contracts\TestSessionRepositoryInterface;
use App\Models\TestResult;
use App\Models\TestSession;
use Illuminate\Support\Facades\DB;

class EloquentTestSessionRepository implements TestSessionRepositoryInterface
{
    public function createWithSnapshot(array $sessionData, array $answersSnapshot, array $resultsSnapshot): TestSession
    {
        return DB::transaction(function () use ($sessionData, $answersSnapshot, $resultsSnapshot): TestSession {
            $session = TestSession::query()->create($sessionData);

            $session->answers()->createMany($answersSnapshot);
            $session->results()->createMany($resultsSnapshot);

            return $session->load(['answers', 'results']);
        });
    }

    public function findByTokenWithDetails(string $token): ?TestSession
    {
        return TestSession::query()
            ->where('token', $token)
            ->with(['answers', 'results'])
            ->first();
    }

    public function countCompleted(): int
    {
        return TestSession::query()->whereNotNull('completed_at')->count();
    }

    public function rankOneCareerCounts(int $limit = 10): array
    {
        return TestResult::query()
            ->where('rank', 1)
            ->selectRaw('career_name, COUNT(*) as aggregate')
            ->groupBy('career_name')
            ->orderByDesc('aggregate')
            ->orderBy('career_name')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'career_name' => (string) $row->career_name,
                'count' => (int) $row->aggregate,
            ])
            ->all();
    }
}
