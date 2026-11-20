<?php

namespace App\Services\Admin;

use App\Contracts\AiExplanationRepository;
use App\Contracts\TestSessionRepositoryInterface;
use App\Contracts\VisitorFeedbackRepository;
use App\Services\Spk\MatrixHealthAnalyzer;
use App\Models\VisitorFeedback;

class AdminAnalyticsService
{
    public function __construct(
        private TestSessionRepositoryInterface $sessions,
        private VisitorFeedbackRepository $feedbacks,
        private AiExplanationRepository $explanations,
        private MatrixHealthAnalyzer $matrixHealth,
    ) {}

    public function summary(): array
    {
        $distribution = $this->sessions->rankOneCareerCounts(10);
        $topCareer = $distribution[0] ?? null;

        $aiSummary = $this->explanations->summary(7);
        $matrixHealth = $this->matrixHealth->analyze(200, 42);
        $matrixHealthy = $this->matrixHealth->isHealthy($matrixHealth);

        return [
            'kpi' => [
                'total_sessions' => $this->sessions->countCompleted(),
                'total_feedbacks' => $this->feedbacks->count(),
                'average_feedback_score' => $this->feedbacks->averageScore(),
                'top_career' => $topCareer,
            ],
            'ai_summary' => $aiSummary,
            'matrix_health' => [
                'healthy' => $matrixHealthy,
                'label' => $matrixHealthy ? 'Matriks sehat' : 'Perlu perhatian',
            ],
            'career_distribution' => $distribution,
            'recent_feedbacks' => $this->feedbacks->recentWithSessionAndTopResult(10)
                ->map(fn (VisitorFeedback $feedback) => $this->mapFeedback($feedback))
                ->values()
                ->all(),
        ];
    }

    private function mapFeedback(VisitorFeedback $feedback): array
    {
        $topResult = $feedback->session?->results->first();

        return [
            'id' => (int) $feedback->id,
            'visitor_name' => $feedback->session?->visitor_name ?: 'Anonim',
            'career_name' => $topResult?->career_name,
            'match_percentage' => $topResult ? (string) $topResult->match_percentage : null,
            'score' => (int) $feedback->score,
            'comment' => $feedback->comment,
            'suggestion' => $feedback->suggestion,
            'created_at' => $feedback->created_at?->toIso8601String(),
        ];
    }
}
