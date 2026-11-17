<?php

namespace App\Contracts;

use App\Models\TestSession;

interface TestSessionRepository
{
    public function create(?string $visitorName): TestSession;

    public function findByToken(string $token): ?TestSession;

    public function markCompleted(TestSession $session): TestSession;
}
