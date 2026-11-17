<?php

namespace App\Http\Controllers\Public;

use App\Contracts\TestSessionRepositoryInterface;
use App\Http\Controllers\Controller;
use Inertia\Inertia;

class ResultController extends Controller
{
    public function __construct(
        private TestSessionRepositoryInterface $sessions,
    ) {}

    public function show(string $token)
    {
        $session = $this->sessions->findByTokenWithDetails($token);
        if (! $session) {
            abort(404);
        }

        $topResults = $session->results
            ->take(3)
            ->map(fn ($result) => [
                'rank' => (int) $result->rank,
                'career_code' => $result->career_code,
                'career_name' => $result->career_name,
                'match_percentage' => (float) $result->match_percentage,
            ])
            ->values()
            ->all();

        return Inertia::render('public/result-placeholder', [
            'session' => [
                'token' => $session->token,
                'visitor_name' => $session->visitor_name,
                'completed_at' => $session->completed_at?->toIso8601String(),
            ],
            'top_results' => $topResults,
        ]);
    }
}
