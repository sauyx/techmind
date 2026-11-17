<?php

namespace App\Repositories;

use App\Contracts\TestSessionRepositoryInterface;
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
}
