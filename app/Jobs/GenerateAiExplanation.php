<?php

namespace App\Jobs;

use App\Contracts\TestSessionRepositoryInterface;
use App\Services\Ai\ExplanationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateAiExplanation implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $token,
    ) {}

    public function handle(ExplanationService $explanations, TestSessionRepositoryInterface $sessions): void
    {
        $session = $sessions->findByTokenWithDetails($this->token);

        if (! $session) {
            return;
        }

        $explanations->getOrCreate($session);
    }
}
