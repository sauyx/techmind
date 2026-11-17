<?php

namespace App\Repositories;

use App\Contracts\VisitorFeedbackRepository;
use App\Models\TestSession;
use App\Models\VisitorFeedback;

class EloquentVisitorFeedbackRepository implements VisitorFeedbackRepository
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
}
