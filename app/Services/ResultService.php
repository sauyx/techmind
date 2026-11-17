<?php

namespace App\Services;

use App\Contracts\TestResultRepository;
use App\Contracts\TestSessionRepository;
use App\Models\TestResult;

class ResultService
{
    public function __construct(
        private TestSessionRepository $sessions,
        private TestResultRepository $results,
    ) {}

    public function findByToken(string $token): ?TestResult
    {
        return $this->results->findByToken($token);
    }
}
