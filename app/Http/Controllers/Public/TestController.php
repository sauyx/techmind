<?php

namespace App\Http\Controllers\Public;

use App\Contracts\CriterionRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTestRequest;
use App\Services\TestSubmissionService;
use Inertia\Inertia;

class TestController extends Controller
{
    public function __construct(
        private CriterionRepository $criteria,
        private TestSubmissionService $submission,
    ) {}

    public function create()
    {
        $criteria = $this->criteria->activeOrdered()->map(fn ($criterion) => [
            'code' => (string) $criterion->code,
            'name' => (string) $criterion->name,
            'description' => 'Seberapa sesuai pernyataan ini dengan dirimu? '.(string) $criterion->description,
            'step_group' => (int) $criterion->step_group,
            'display_order' => (int) $criterion->display_order,
        ])->values()->all();

        return Inertia::render('public/test', [
            'criteria' => $criteria,
        ]);
    }

    public function store(StoreTestRequest $request)
    {
        $session = $this->submission->submit(
            $request->validated('visitor_name'),
            $request->validated('ratings')
        );

        return redirect()->route('results.show', $session->token);
    }
}
