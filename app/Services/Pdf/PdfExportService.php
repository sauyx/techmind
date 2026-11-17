<?php

namespace App\Services\Pdf;

use App\Models\TestSession;
use App\Services\ResultPresentationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class PdfExportService
{
    public function __construct(
        private ResultPresentationService $presentation,
        private RadarSvgGenerator $radarSvg,
    ) {}

    public function download(TestSession $session): Response
    {
        $data = $this->presentation->formatSession($session);

        $ratings = $data['ratings'] ?? [];
        $criteria = $data['criteria'] ?? [];
        $results = $data['results'] ?? [];
        $topResult = $results[0] ?? null;

        $topCareerCode = $topResult['career_code'] ?? '';
        $topCareer = null;

        foreach ($data['careers'] ?? [] as $career) {
            if ($career['code'] === $topCareerCode) {
                $topCareer = $career;
                break;
            }
        }

        $profiles = $topCareer['profiles'] ?? [];

        $radarSvg = $this->radarSvg->generate($ratings, $profiles, $criteria);

        $completedDate = $session->completed_at
            ? $session->completed_at->format('d/m/Y H:i')
            : date('d/m/Y H:i');

        $viewData = array_merge($data, [
            'topResult' => $topResult,
            'topCareer' => $topCareer,
            'radar_svg' => $radarSvg,
            'completed_date' => $completedDate,
        ]);

        $shortToken = substr((string) $session->token, 0, 8);
        $fileName = "TechMind-Hasil-{$shortToken}.pdf";

        $pdf = Pdf::loadView('pdf.result', $viewData)
            ->setPaper('a4', 'portrait');

        return $pdf->download($fileName);
    }
}
