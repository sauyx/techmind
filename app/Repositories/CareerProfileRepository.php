<?php

namespace App\Repositories;

use App\Contracts\CareerProfileRepository as CareerProfileRepositoryContract;
use App\Models\CareerProfile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

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
        $version = $this->referenceVersion();

        return Cache::remember('techmind:ref:v'.$version.':career_profiles:matrix', 600, function () {
            return CareerProfile::query()
                ->with(['career', 'criterion'])
                ->get();
        });
    }

    public function flushCache(): void
    {
        $this->bumpReferenceVersion();
    }

    private function referenceVersion(): int
    {
        if (! Cache::has('techmind:ref:version')) {
            Cache::put('techmind:ref:version', 1, 86400 * 30);
        }

        return (int) Cache::get('techmind:ref:version', 1);
    }

    private function bumpReferenceVersion(): void
    {
        if (! Cache::has('techmind:ref:version')) {
            Cache::put('techmind:ref:version', 1, 86400 * 30);
        }

        Cache::increment('techmind:ref:version');
    }
}
