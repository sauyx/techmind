<?php

namespace App\Contracts;

use App\Models\TestResult;
use App\Models\TestSession;

interface TestResultRepository
{
    public function save(TestSession $session, string $methodCode, array $ranking, string $topCode, string $topName): TestResult;

    public function findByToken(string $token): ?TestResult;
}
