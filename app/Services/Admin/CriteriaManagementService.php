<?php

namespace App\Services\Admin;

use App\Contracts\CriterionRepository;
use App\Models\Criterion;

class CriteriaManagementService
{
    public function __construct(
        private CriterionRepository $criteria,
    ) {}

    public function list(): array
    {
        return $this->criteria->all()
            ->map(fn (Criterion $criterion) => $this->mapCriterion($criterion))
            ->values()
            ->all();
    }

    public function update(Criterion $criterion, array $data): Criterion
    {
        return $this->criteria->update($criterion, [
            'name' => (string) $data['name'],
            'description' => $data['description'] ?? null,
            'weight' => (float) $data['weight'],
            'step_group' => (int) $data['step_group'],
            'display_order' => (int) $data['display_order'],
            'is_active' => (bool) $data['is_active'],
        ]);
    }

    private function mapCriterion(Criterion $criterion): array
    {
        return [
            'id' => (int) $criterion->id,
            'code' => (string) $criterion->code,
            'name' => (string) $criterion->name,
            'description' => $criterion->description,
            'weight' => (float) $criterion->weight,
            'step_group' => (int) $criterion->step_group,
            'display_order' => (int) $criterion->display_order,
            'is_active' => (bool) $criterion->is_active,
        ];
    }
}
