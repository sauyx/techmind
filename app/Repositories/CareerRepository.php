<?php

namespace App\Repositories;

use App\Contracts\CareerRepository as CareerRepositoryContract;
use App\Models\Career;
use App\Models\CareerProfile;
use App\Models\Criterion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CareerRepository implements CareerRepositoryContract
{
    public function activeWithProfiles(): Collection
    {
        $version = $this->referenceVersion();

        $cached = Cache::remember('techmind:ref:v'.$version.':careers:active', 600, function () {
            $careers = Career::query()
                ->where('is_active', true)
                ->with(['careerProfiles.criterion'])
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            return $careers->map(function (Career $career) {
                return [
                    'career' => $career->getAttributes(),
                    'profiles' => $career->careerProfiles->map(function (CareerProfile $profile) {
                        return [
                            'profile' => $profile->getAttributes(),
                            'criterion' => $profile->criterion?->getAttributes(),
                        ];
                    })->all(),
                ];
            })->all();
        });

        $collection = new Collection;

        foreach ($cached as $item) {
            $career = new Career;
            $career->setRawAttributes($item['career'], true);
            $profiles = new Collection;

            foreach ($item['profiles'] as $profileData) {
                $profile = new CareerProfile;
                $profile->setRawAttributes($profileData['profile'], true);
                if ($profileData['criterion'] !== null) {
                    $profile->setRelation('criterion', new Criterion($profileData['criterion']));
                }
                $profiles->push($profile);
            }

            $career->setRelation('careerProfiles', $profiles);

            $collection->push($career);
        }

        return $collection;
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

    public function allWithProfiles(): Collection
    {
        return Career::query()
            ->with(['careerProfiles.criterion'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function find(int $id): ?Career
    {
        return Career::query()->find($id);
    }

    public function findWithProfiles(int $id): ?Career
    {
        return Career::query()
            ->with(['careerProfiles.criterion'])
            ->find($id);
    }

    public function create(array $data): Career
    {
        return Career::query()->create($data);
    }

    public function update(Career $career, array $data): Career
    {
        $career->update($data);

        return $career->refresh();
    }

    public function delete(Career $career): void
    {
        $career->delete();
    }
}
