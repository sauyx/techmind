<?php

namespace App\Http\Controllers\Public;

use App\Contracts\CriterionRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTestRequest;
use App\Jobs\GenerateAiExplanation;
use App\Services\TestSubmissionService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TestController extends Controller
{
    public function __construct(
        private CriterionRepository $criteria,
        private TestSubmissionService $submission,
    ) {}

    public function create(): Response
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

    public function store(StoreTestRequest $request): RedirectResponse
    {
        $session = $this->submission->submit(
            $request->validated('visitor_name'),
            $request->validated('ratings')
        );

        dispatch(new GenerateAiExplanation($session->token))->afterResponse();

        return redirect()->route('results.show', $session->token);
    }
}
