<?php

namespace App\Contracts;

use App\Models\Criterion;
use Illuminate\Support\Collection;

interface CriterionRepository
{
    public function activeOrdered(): Collection;

    public function all(): Collection;

    public function find(int $id): ?Criterion;

    public function create(array $data): Criterion;

    public function update(Criterion $criterion, array $data): Criterion;

    public function delete(Criterion $criterion): void;

    public function flushCache(): void;
}
