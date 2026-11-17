<?php

namespace App\Services\Admin;

use App\Contracts\VisitorFeedbackRepository;
use App\Models\VisitorFeedback;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FeedbackExportService
{
    public function __construct(
        private VisitorFeedbackRepository $feedbacks,
    ) {}

    public function export(): StreamedResponse
    {
        $feedbacks = $this->feedbacks->allWithSessionAndTopResult();
        $fileName = 'techmind-feedbacks-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($feedbacks) {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Waktu Tes',
                'Nama Pengunjung',
                'Karier Rekomendasi #1',
                'Skor Kecocokan #1 (%)',
                'Nilai Penilaian (0-100)',
                'Tanggapan',
                'Saran',
            ]);

            foreach ($feedbacks as $feedback) {
                $row = $this->mapRow($feedback);
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function mapRow(VisitorFeedback $feedback): array
    {
        $session = $feedback->session;
        $topResult = $session?->results->first();

        return [
            $session?->completed_at?->format('Y-m-d H:i:s'),
            $session?->visitor_name ?: 'Anonim',
            $topResult?->career_name,
            $topResult?->match_percentage,
            (int) $feedback->score,
            $feedback->comment,
            $feedback->suggestion,
        ];
    }
}
