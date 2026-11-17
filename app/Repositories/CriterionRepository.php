<?php

namespace App\Repositories;

use App\Contracts\CriterionRepository as CriterionRepositoryContract;
use App\Models\Criterion;
use Illuminate\Support\Collection;

class CriterionRepository implements CriterionRepositoryContract
{
    public function activeOrdered(): Collection
    {
        return Criterion::query()
            ->where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('id')
            ->get();
    }

    public function all(): Collection
    {
        return Criterion::query()->orderBy('display_order')->orderBy('id')->get();
    }

    public function find(int $id): ?Criterion
    {
        return Criterion::query()->find($id);
    }

    public function create(array $data): Criterion
    {
        return Criterion::query()->create($data);
    }

    public function update(Criterion $criterion, array $data): Criterion
    {
        $criterion->update($data);

        return $criterion->refresh();
    }

    public function delete(Criterion $criterion): void
    {
        $criterion->delete();
    }
}
