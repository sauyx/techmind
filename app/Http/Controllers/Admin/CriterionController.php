<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\CareerRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateCriterionRequest;
use App\Models\Criterion;
use App\Services\Admin\CriteriaManagementService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CriterionController extends Controller
{
    public function __construct(
        private CriteriaManagementService $criteria,
        private CareerRepository $careers,
    ) {}

    public function index(): Response
    {
        return Inertia::render('admin/criteria/index', [
            'criteria' => $this->criteria->list(),
            'careers' => $this->careers->allWithProfiles()->map(function ($career) {
                $scores = [];
                foreach ($career->careerProfiles as $profile) {
                    if ($profile->criterion) {
                        $scores[(string) $profile->criterion->code] = (int) $profile->score;
                    }
                }

                return [
                    'code' => (string) $career->code,
                    'name' => (string) $career->name,
                    'sort_order' => (int) $career->sort_order,
                    'scores' => $scores,
                ];
            })->values()->all(),
        ]);
    }

    public function update(UpdateCriterionRequest $request, Criterion $criterion): RedirectResponse
    {
        $this->criteria->update($criterion, $request->validated());

        return redirect()
            ->route('admin.criteria.index')
            ->with('success', 'Kriteria berhasil diperbarui.');
    }
}
