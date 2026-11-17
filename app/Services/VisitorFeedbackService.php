<?php

namespace App\Services;

use App\Contracts\VisitorFeedbackRepository;
use App\Models\TestSession;
use App\Models\VisitorFeedback;

class VisitorFeedbackService
{
    public function __construct(
        private VisitorFeedbackRepository $feedbacks,
    ) {}

    public function store(TestSession $session, array $data): VisitorFeedback
    {
        return $this->feedbacks->updateOrCreate($session, [
            'score' => (int) $data['score'],
            'comment' => isset($data['comment']) && trim((string) $data['comment']) !== '' ? trim((string) $data['comment']) : null,
            'suggestion' => isset($data['suggestion']) && trim((string) $data['suggestion']) !== '' ? trim((string) $data['suggestion']) : null,
        ]);
    }
}
