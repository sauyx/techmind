<?php

namespace App\Repositories;

use App\Contracts\CareerProfileRepository as CareerProfileRepositoryContract;
use App\Models\CareerProfile;
use Illuminate\Support\Collection;

class CareerProfileRepository implements CareerProfileRepositoryContract
{
    public function forCareer(int $careerId): Collection
    {
        return CareerProfile::query()
            ->where('career_id', $careerId)
            ->with('criterion')
            ->get();
    }

    public function upsertScore(int $careerId, int $criterionId, int $score): CareerProfile
    {
        return CareerProfile::query()->updateOrCreate(
            ['career_id' => $careerId, 'criterion_id' => $criterionId],
            ['score' => $score]
        );
    }

    public function replaceForCareer(int $careerId, array $scores): void
    {
        foreach ($scores as $criterionId => $score) {
            $this->upsertScore($careerId, (int) $criterionId, (int) $score);
        }
    }

    public function matrix(): Collection
    {
        return CareerProfile::query()
            ->with(['career', 'criterion'])
            ->get();
    }
}
