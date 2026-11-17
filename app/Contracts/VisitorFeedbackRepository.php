<?php

namespace App\Contracts;

use App\Models\TestSession;
use App\Models\VisitorFeedback;
use Illuminate\Support\Collection;

interface VisitorFeedbackRepository
{
    public function findForSession(TestSession $session): ?VisitorFeedback;

    public function updateOrCreate(TestSession $session, array $data): VisitorFeedback;

    public function count(): int;

    public function averageScore(): ?string;

    public function recentWithSessionAndTopResult(int $limit = 10): Collection;

    public function allWithSessionAndTopResult(): Collection;
}
