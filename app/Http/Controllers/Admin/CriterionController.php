<?php

namespace App\Http\Controllers\Admin;

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
    ) {}

    public function index(): Response
    {
        return Inertia::render('admin/criteria/index', [
            'criteria' => $this->criteria->list(),
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
