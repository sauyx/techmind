<?php

namespace App\Contracts;

use App\Models\TestSession;
use App\Models\VisitorFeedback;

interface VisitorFeedbackRepository
{
    public function findForSession(TestSession $session): ?VisitorFeedback;

    public function updateOrCreate(TestSession $session, array $data): VisitorFeedback;
}
