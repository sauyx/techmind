<?php

namespace App\Contracts;

use App\Models\TestSession;
use Illuminate\Support\Collection;

interface TestAnswerRepository
{
    public function replaceForSession(TestSession $session, array $answers): void;

    public function forSession(TestSession $session): Collection;
}
