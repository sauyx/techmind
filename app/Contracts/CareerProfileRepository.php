<?php

namespace App\Contracts;

use App\Models\CareerProfile;
use Illuminate\Support\Collection;

interface CareerProfileRepository
{
    public function forCareer(int $careerId): Collection;

    public function upsertScore(int $careerId, int $criterionId, int $score): CareerProfile;

    public function replaceForCareer(int $careerId, array $scores): void;

    public function matrix(): Collection;

    public function flushCache(): void;
}
