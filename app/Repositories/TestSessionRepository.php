<?php

namespace App\Repositories;

use App\Contracts\TestSessionRepository as TestSessionRepositoryContract;
use App\Models\TestSession;
use Illuminate\Support\Str;

class TestSessionRepository implements TestSessionRepositoryContract
{
    public function create(?string $visitorName): TestSession
    {
        return TestSession::query()->create([
            'token' => (string) Str::uuid(),
            'visitor_name' => $visitorName,
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
    }

    public function findByToken(string $token): ?TestSession
    {
        return TestSession::query()->where('token', $token)->first();
    }

    public function markCompleted(TestSession $session): TestSession
    {
        $session->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return $session->refresh();
    }
}
