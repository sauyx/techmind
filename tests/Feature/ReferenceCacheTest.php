<?php

namespace Tests\Feature;

use App\Contracts\CriterionRepository;
use App\Models\Criterion;
use App\Services\Admin\CriteriaManagementService;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferenceCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_cache_invalidated_after_admin_weight_update(): void
    {
        $this->seed(ReferenceDataSeeder::class);

        $criterion = Criterion::query()->where('code', 'logic_problem_solving')->firstOrFail();
        $service = app(CriteriaManagementService::class);

        // prime cache
        $before = app(CriterionRepository::class)->activeOrdered()
            ->firstWhere('code', 'logic_problem_solving');
        $this->assertSame(1.0, (float) $before->weight);

        $service->update($criterion, [
            'name' => $criterion->name,
            'description' => $criterion->description,
            'weight' => 1.5,
            'step_group' => $criterion->step_group,
            'display_order' => $criterion->display_order,
            'is_active' => $criterion->is_active,
        ]);

        $after = app(CriterionRepository::class)->activeOrdered()
            ->firstWhere('code', 'logic_problem_solving');

        $this->assertSame(1.5, (float) $after->weight);
    }
}
