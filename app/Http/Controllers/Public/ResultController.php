<?php

namespace App\Http\Controllers\Public;

use App\Contracts\TestSessionRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Services\ResultPresentationService;
use Inertia\Inertia;
use Inertia\Response;

class ResultController extends Controller
{
    public function __construct(
        private TestSessionRepositoryInterface $sessions,
        private ResultPresentationService $presentation,
    ) {}

    public function show(string $token): Response
    {
        $session = $this->sessions->findByTokenWithDetails($token);
        if (! $session) {
            abort(404);
        }

        $props = $this->presentation->formatSession($session);

        return Inertia::render('public/result', $props);
    }
}
