<?php

namespace App\Services;

use App\Contracts\CareerRepository;
use App\Contracts\CriterionRepository;
use App\Contracts\VisitorFeedbackRepository;
use App\Models\TestSession;
use App\Services\Ai\ExplanationService;

class ResultPresentationService
{
    private const SHORT_LABELS = [
        'logic_problem_solving' => 'Logika',
        'data_analysis' => 'Data',
        'math_statistics' => 'Matematika',
        'attention_detail' => 'Ketelitian',
        'visual_design' => 'Desain',
        'creativity' => 'Kreativitas',
        'user_empathy' => 'Empati',
        'communication' => 'Komunikasi',
        'coding' => 'Coding',
        'network_infra' => 'Jaringan',
        'security_awareness' => 'Keamanan',
        'business_process' => 'Bisnis',
    ];

    public function __construct(
        private CriterionRepository $criteria,
        private CareerRepository $careers,
        private ExplanationService $explanations,
        private VisitorFeedbackRepository $feedbacks,
    ) {}

    public function formatSession(TestSession $session, bool $includeExplanation = true): array
    {
        $answers = $session->answers;
        $ratings = [];
        foreach ($answers as $answer) {
            $ratings[(string) $answer->criterion_code] = (int) $answer->rating;
        }

        $allCriteria = $this->criteria->activeOrdered();
        $criteriaNames = [];
        $criteriaList = [];

        foreach ($allCriteria as $criterion) {
            $code = (string) $criterion->code;
            $name = (string) $criterion->name;
            $criteriaNames[$code] = $name;
            $criteriaList[] = [
                'code' => $code,
                'name' => $name,
                'short_label' => self::SHORT_LABELS[$code] ?? $name,
            ];
        }

        $resultScores = $session->results->map(fn ($result) => (float) $result->saw_score)->all();

        $results = $session->results->map(function ($result) use ($criteriaNames, $resultScores) {
            $score = (float) $result->saw_score;
            $sameCount = 0;
            foreach ($resultScores as $otherScore) {
                if (abs($otherScore - $score) < 1e-9) {
                    $sameCount++;
                }
            }
            $isTied = $sameCount > 1;

            return [
                'id' => (int) $result->id,
                'rank' => (int) $result->rank,
                'rank_label' => $isTied ? '#'.(int) $result->rank.' (setara)' : '#'.(int) $result->rank,
                'is_tied' => $isTied,
                'career_code' => (string) $result->career_code,
                'career_name' => (string) $result->career_name,
                'saw_score' => $score,
                'match_percentage' => (string) $result->match_percentage,
                'contribution_breakdown' => $result->contribution_breakdown ?? [],
                'gap_summary' => $this->buildNaturalGapSummary($result->gap_summary, $criteriaNames),
            ];
        })->values()->all();

        $topResult = $results[0] ?? null;

        $careerDetails = $this->careers->activeWithProfiles()->map(function ($career) {
            $profiles = [];
            foreach ($career->careerProfiles as $profile) {
                if ($profile->criterion) {
                    $profiles[(string) $profile->criterion->code] = (int) $profile->score;
                }
            }

            return [
                'code' => (string) $career->code,
                'name' => (string) $career->name,
                'summary' => $career->summary,
                'description' => $career->description,
                'skills' => $career->skills ?? [],
                'tools' => $career->tools ?? [],
                'roadmap' => $career->roadmap ?? [],
                'is_validated' => (bool) $career->is_validated,
                'source_note' => $career->source_note,
                'profiles' => $profiles,
            ];
        })->values()->all();

        $feedback = $this->feedbacks->findForSession($session);

        $aiExplanation = null;
        if ($includeExplanation) {
            $aiExplanation = $this->explanations->getOrCreate($session);
        }

        $props = [
            'session' => [
                'token' => (string) $session->token,
                'visitor_name' => $session->visitor_name,
                'completed_at' => $session->completed_at?->toIso8601String(),
            ],
            'results' => $results,
            'ratings' => $ratings,
            'criteria' => $criteriaList,
            'careers' => $careerDetails,
            'ai_explanation' => $aiExplanation ? [
                'content' => (string) $aiExplanation->content,
                'format' => (string) $aiExplanation->format,
                'driver_used' => (string) $aiExplanation->driver_used,
                'is_fallback' => (bool) $aiExplanation->is_fallback,
                'source_label' => $aiExplanation->driver_used === 'openai_compatible' && ! $aiExplanation->is_fallback
                    ? 'Analisis AI'
                    : 'Analisis Ringkas',
            ] : null,
            'visitor_feedback' => $feedback ? [
                'score' => (int) $feedback->score,
                'comment' => $feedback->comment,
                'suggestion' => $feedback->suggestion,
            ] : null,
        ];

        return $props;
    }

    public function formatAiExplanation(TestSession $session): array
    {
        $aiExplanation = $this->explanations->getOrCreate($session);

        return [
            'content' => (string) $aiExplanation->content,
            'format' => (string) $aiExplanation->format,
            'driver_used' => (string) $aiExplanation->driver_used,
            'is_fallback' => (bool) $aiExplanation->is_fallback,
            'source_label' => $aiExplanation->driver_used === 'openai_compatible' && ! $aiExplanation->is_fallback
                ? 'Analisis AI'
                : 'Analisis Ringkas',
        ];
    }

    private function buildNaturalGapSummary(?array $gapSummary, array $criteriaNames): array
    {
        if (! $gapSummary) {
            return [
                'strengths' => [],
                'needs_strengthening' => [],
                'underused' => [],
                'narrative_strengths' => [],
                'narrative_needs' => [],
                'narrative_underused' => [],
            ];
        }

        $strengths = [];
        $narrativeStrengths = [];
        foreach ($gapSummary['strengths'] ?? [] as $item) {
            $code = (string) ($item['criterion_code'] ?? '');
            $name = $criteriaNames[$code] ?? $code;
            $rating = (int) ($item['rating'] ?? 0);
            $profile = (int) ($item['profile'] ?? 0);
            $sentence = "Kemampuan {$name} Anda ({$rating}/5) sangat selaras dengan tuntutan profil karier ini ({$profile}/5).";

            $strengths[] = [
                'criterion_code' => $code,
                'criterion_name' => $name,
                'rating' => $rating,
                'profile' => $profile,
                'sentence' => $sentence,
            ];
            $narrativeStrengths[] = $sentence;
        }

        $needs = [];
        $narrativeNeeds = [];
        foreach ($gapSummary['needs_strengthening'] ?? [] as $item) {
            $code = (string) ($item['criterion_code'] ?? '');
            $name = $criteriaNames[$code] ?? $code;
            $rating = (int) ($item['rating'] ?? 0);
            $profile = (int) ($item['profile'] ?? 0);
            $sentence = "Area {$name} disarankan untuk diperkuat (saat ini {$rating}/5, sedangkan profil ideal membutuhkan {$profile}/5).";

            $needs[] = [
                'criterion_code' => $code,
                'criterion_name' => $name,
                'rating' => $rating,
                'profile' => $profile,
                'sentence' => $sentence,
            ];
            $narrativeNeeds[] = $sentence;
        }

        $underused = [];
        $narrativeUnderused = [];
        foreach ($gapSummary['underused'] ?? [] as $item) {
            $code = (string) ($item['criterion_code'] ?? '');
            $name = $criteriaNames[$code] ?? $code;
            $rating = (int) ($item['rating'] ?? 0);
            $profile = (int) ($item['profile'] ?? 0);
            $sentence = "Potensi {$name} Anda ({$rating}/5) belum banyak dimanfaatkan pada jalur ini (profil ideal {$profile}/5).";

            $underused[] = [
                'criterion_code' => $code,
                'criterion_name' => $name,
                'rating' => $rating,
                'profile' => $profile,
                'sentence' => $sentence,
            ];
            $narrativeUnderused[] = $sentence;
        }

        return [
            'strengths' => $strengths,
            'needs_strengthening' => $needs,
            'underused' => $underused,
            'narrative_strengths' => $narrativeStrengths,
            'narrative_needs' => $narrativeNeeds,
            'narrative_underused' => $narrativeUnderused,
        ];
    }

    private function buildExplanation(?array $topResult): string
    {
        if (! $topResult) {
            return 'Hasil analisis belum tersedia.';
        }

        $parts = [];
        $parts[] = 'Berdasarkan jawaban Anda, jalur karier yang paling sesuai adalah '.$topResult['career_name'].' dengan skor kecocokan '.number_format($topResult['match_percentage'], 1).'%. ';

        $gap = $topResult['gap_summary'] ?? [];
        $strengths = array_column($gap['strengths'] ?? [], 'criterion_name');
        if ($strengths !== []) {
            $parts[] = 'Kekuatan utama Anda terlihat pada '.implode(', ', $strengths).'. ';
        }

        $needs = array_column($gap['needs_strengthening'] ?? [], 'criterion_name');
        if ($needs !== []) {
            $parts[] = 'Area yang disarankan untuk diperkuat meliputi '.implode(', ', $needs).'. ';
        }

        $parts[] = 'Hasil ini merupakan indeks kecocokan relatif berbasis metode SAW (Simple Additive Weighting), bukan jaminan atau probabilitas kelulusan absolut.';

        return implode('', $parts);
    }
}
