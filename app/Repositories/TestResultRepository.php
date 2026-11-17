<?php

namespace App\Repositories;

use App\Contracts\TestResultRepository as TestResultRepositoryContract;
use App\Models\TestResult;
use App\Models\TestSession;

class TestResultRepository implements TestResultRepositoryContract
{
    public function save(TestSession $session, string $methodCode, array $ranking, string $topCode, string $topName): TestResult
    {
        return TestResult::query()->updateOrCreate(
            ['test_session_id' => $session->id],
            [
                'method_code' => $methodCode,
                'ranking' => $ranking,
                'top_career_code' => $topCode,
                'top_career_name' => $topName,
            ]
        );
    }

    public function findByToken(string $token): ?TestResult
    {
        return TestResult::query()
            ->whereHas('session', fn ($q) => $q->where('token', $token))
            ->with('session')
            ->first();
    }
}
