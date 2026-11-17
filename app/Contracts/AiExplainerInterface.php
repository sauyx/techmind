<?php

namespace App\Contracts;

use App\Data\AiExplanationResult;
use App\Models\TestSession;

interface AiExplainerInterface
{
    public function explain(TestSession $session): AiExplanationResult;
}
