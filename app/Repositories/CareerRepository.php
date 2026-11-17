<?php

namespace App\Repositories;

use App\Contracts\CareerRepository as CareerRepositoryContract;
use App\Models\Career;
use Illuminate\Support\Collection;

class CareerRepository implements CareerRepositoryContract
{
    public function activeWithProfiles(): Collection
    {
        return Career::query()
            ->where('is_active', true)
            ->with(['careerProfiles.criterion'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
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
