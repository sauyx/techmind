<?php

namespace App\Repositories;

use App\Contracts\VisitorFeedbackRepository as VisitorFeedbackRepositoryContract;
use App\Models\TestSession;
use App\Models\VisitorFeedback;
use Illuminate\Support\Collection;

class EloquentVisitorFeedbackRepository implements VisitorFeedbackRepositoryContract
{
    public function findForSession(TestSession $session): ?VisitorFeedback
    {
        return VisitorFeedback::query()
            ->where('test_session_id', $session->id)
            ->first();
    }

    public function updateOrCreate(TestSession $session, array $data): VisitorFeedback
    {
        return VisitorFeedback::query()->updateOrCreate(
            ['test_session_id' => $session->id],
            $data,
        );
    }

    public function count(): int
    {
        return VisitorFeedback::query()->count();
    }

    public function averageScore(): ?string
    {
        $average = VisitorFeedback::query()->avg('score');

        if ($average === null) {
            return null;
        }

        return number_format(round((float) $average, 1), 1, '.', '');
    }

    public function recentWithSessionAndTopResult(int $limit = 10): Collection
    {
        return VisitorFeedback::query()
            ->with(['session.results'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function allWithSessionAndTopResult(): Collection
    {
        return VisitorFeedback::query()
            ->with(['session.results'])
            ->orderByDesc('created_at')
            ->get();
    }
}
