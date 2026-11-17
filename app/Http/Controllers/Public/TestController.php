<?php

namespace App\Http\Controllers\Public;

use App\Contracts\CriterionRepository;
use App\Http\Controllers\Controller;
use App\Models\TestSession;
use App\Services\QuestionnaireService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TestController extends Controller
{
    public function __construct(
        private QuestionnaireService $questionnaire,
        private CriterionRepository $criteria,
    ) {}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'visitor_name' => ['nullable', 'string', 'max:128'],
        ]);

        $session = $this->questionnaire->start($validated['visitor_name'] ?? null);

        return redirect()->route('tests.show', $session->token);
    }

    public function show(string $token)
    {
        $session = TestSession::query()->where('token', $token)->firstOrFail();

        $criteria = $this->criteria->activeOrdered()->map(fn ($criterion) => [
            'code' => (string) $criterion->code,
            'name' => (string) $criterion->name,
            'description' => (string) $criterion->description,
            'step_group' => (int) $criterion->step_group,
            'display_order' => (int) $criterion->display_order,
        ])->values()->all();

        return Inertia::render('test/show', [
            'session' => [
                'token' => $session->token,
                'visitor_name' => $session->visitor_name,
                'status' => $session->status,
            ],
            'criteria' => $criteria,
        ]);
    }

    public function submit(Request $request, string $token)
    {
        $validated = $request->validate([
            'ratings' => ['required', 'array'],
        ]);

        $outcome = $this->questionnaire->submit($token, $validated['ratings']);

        return redirect()->route('results.show', $outcome['session']->token);
    }
}
