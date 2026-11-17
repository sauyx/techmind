<?php

namespace App\Http\Controllers\Public;

use App\Contracts\TestSessionRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVisitorFeedbackRequest;
use App\Services\Pdf\PdfExportService;
use App\Services\ResultPresentationService;
use App\Services\VisitorFeedbackService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ResultController extends Controller
{
    public function __construct(
        private TestSessionRepositoryInterface $sessions,
        private ResultPresentationService $presentation,
        private PdfExportService $pdf,
        private VisitorFeedbackService $feedback,
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

    public function downloadPdf(string $token): SymfonyResponse
    {
        $session = $this->sessions->findByTokenWithDetails($token);
        if (! $session) {
            abort(404);
        }

        return $this->pdf->download($session);
    }

    public function storeFeedback(string $token, StoreVisitorFeedbackRequest $request): JsonResponse
    {
        $session = $this->sessions->findByTokenWithDetails($token);
        if (! $session) {
            abort(404);
        }

        $feedback = $this->feedback->store($session, $request->validated());

        return response()->json([
            'message' => 'Penilaian Anda tersimpan. Terima kasih atas masukannya.',
            'feedback' => [
                'score' => (int) $feedback->score,
                'comment' => $feedback->comment,
                'suggestion' => $feedback->suggestion,
            ],
        ]);
    }
}
