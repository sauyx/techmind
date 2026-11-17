<?php

namespace App\Contracts;

use App\Models\TestSession;

interface TestSessionRepositoryInterface
{
    public function createWithSnapshot(array $sessionData, array $answersSnapshot, array $resultsSnapshot): TestSession;

    public function findByTokenWithDetails(string $token): ?TestSession;
}
