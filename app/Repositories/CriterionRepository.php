<?php

namespace App\Repositories;

use App\Contracts\CriterionRepository as CriterionRepositoryContract;
use App\Models\Criterion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CriterionRepository implements CriterionRepositoryContract
{
    public function activeOrdered(): Collection
    {
        $version = $this->referenceVersion();

        $cached = Cache::remember('techmind:ref:v'.$version.':criteria:active', 600, function () {
            return Criterion::query()
                ->where('is_active', true)
                ->orderBy('display_order')
                ->orderBy('id')
                ->get()
                ->map(fn (Criterion $criterion) => $criterion->getAttributes())
                ->all();
        });

        return new Collection(array_map(
            function (array $attributes) {
                $model = new Criterion;
                $model->forceFill($attributes);

                return $model;
            },
            $cached
        ));
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
