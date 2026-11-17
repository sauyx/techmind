<?php

namespace App\Repositories;

use App\Contracts\TestAnswerRepository as TestAnswerRepositoryContract;
use App\Models\TestAnswer;
use App\Models\TestSession;
use Illuminate\Support\Collection;

class TestAnswerRepository implements TestAnswerRepositoryContract
{
    public function replaceForSession(TestSession $session, array $answers): void
    {
        TestAnswer::query()->where('test_session_id', $session->id)->delete();

        foreach ($answers as $answer) {
            TestAnswer::query()->create([
                'test_session_id' => $session->id,
                'criterion_id' => $answer['criterion_id'] ?? null,
                'criterion_code' => $answer['criterion_code'],
                'criterion_name' => $answer['criterion_name'],
                'rating' => $answer['rating'],
                'weight_snapshot' => $answer['weight_snapshot'],
                'created_at' => now(),
            ]);
        }
    }

    public function forSession(TestSession $session): Collection
    {
        return TestAnswer::query()->where('test_session_id', $session->id)->get();
    }
}
