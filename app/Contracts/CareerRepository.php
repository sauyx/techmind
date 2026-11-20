<?php

namespace App\Contracts;

use App\Models\Career;
use Illuminate\Support\Collection;

interface CareerRepository
{
    public function activeWithProfiles(): Collection;

    public function allWithProfiles(): Collection;

    public function find(int $id): ?Career;

    public function findWithProfiles(int $id): ?Career;

    public function create(array $data): Career;

    public function update(Career $career, array $data): Career;

    public function delete(Career $career): void;
    public function flushCache(): void;
}
