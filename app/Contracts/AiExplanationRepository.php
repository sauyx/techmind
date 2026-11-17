<?php

namespace App\Contracts;

use App\Data\AiExplanationResult;
use App\Models\AiExplanation;
use App\Models\TestSession;

interface AiExplanationRepository
{
    public function findForSession(TestSession $session): ?AiExplanation;

    public function store(TestSession $session, AiExplanationResult $result): AiExplanation;
}
