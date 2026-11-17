<?php

namespace App\Http\Controllers\Public;

use App\Contracts\CareerRepository;
use App\Contracts\CriterionRepository;
use App\Http\Controllers\Controller;
use App\Services\QuestionnaireService;
use App\Services\RecommendationService;
use App\Services\ResultService;
use Inertia\Inertia;

class ResultController extends Controller
{
    public function __construct(
        private ResultService $results,
        private QuestionnaireService $questionnaire,
        private RecommendationService $recommendation,
        private CriterionRepository $criteria,
        private CareerRepository $careers,
    ) {}

    public function show(string $token)
    {
        $result = $this->results->findByToken($token);
        if (! $result || ! $result->session) {
            abort(404);
        }

        $session = $result->session;
        $answers = $session->answers()->get();
        $ratings = [];
        foreach ($answers as $answer) {
            $ratings[(string) $answer->criterion_code] = (int) $answer->rating;
        }

        $recommendation = $this->recommendation->recommend($ratings);
        $topCode = (string) $result->top_career_code;

        $career = $this->careers->allWithProfiles()->firstWhere('code', $topCode);
        $topProfile = [];
        if ($career) {
            foreach ($career->careerProfiles as $profile) {
                $topProfile[(string) $profile->criterion->code] = (int) $profile->score;
            }
        }

        $criteria = $this->criteria->activeOrdered()->map(fn ($criterion) => [
            'code' => (string) $criterion->code,
            'name' => (string) $criterion->name,
        ])->values()->all();

        $gapAnalysis = $recommendation['gap_analysis'][$topCode] ?? [
            'strengths' => [],
            'needs_strengthening' => [],
            'underused' => [],
        ];

        $topScore = isset($result->ranking[0]['score']) ? (float) $result->ranking[0]['score'] : 0.0;
        $explanation = $this->buildExplanation((string) $result->top_career_name, $topScore, $gapAnalysis);

        return Inertia::render('result/show', [
            'session' => [
                'token' => $session->token,
                'visitor_name' => $session->visitor_name,
            ],
            'result' => [
                'method_code' => $result->method_code,
                'ranking' => $result->ranking,
                'top_career_code' => $result->top_career_code,
                'top_career_name' => $result->top_career_name,
            ],
            'criteria' => $criteria,
            'ratings' => $ratings,
            'top_profile' => $topProfile,
            'gap_analysis' => $gapAnalysis,
            'explanation' => $explanation,
            'career' => $career ? [
                'code' => $career->code,
                'name' => $career->name,
                'summary' => $career->summary,
                'description' => $career->description,
                'skills' => $career->skills,
                'tools' => $career->tools,
                'roadmap' => $career->roadmap,
            ] : null,
        ]);
    }

    private function buildExplanation(string $careerName, float $score, array $gap): string
    {
        $parts = [];
        $parts[] = "Berdasarkan jawaban Anda, jalur karier yang paling sesuai adalah {$careerName} dengan skor kecocokan ".number_format($score * 100, 1).'%. ';

        $strengths = array_column($gap['strengths'] ?? [], 'criterion_code');
        if ($strengths !== []) {
            $parts[] = 'Kekuatan Anda terlihat pada '.implode(', ', $strengths).'. ';
        }

        $needs = array_column($gap['needs_strengthening'] ?? [], 'criterion_code');
        if ($needs !== []) {
            $parts[] = 'Area yang dapat diperkuat: '.implode(', ', $needs).'. ';
        }

        $underused = array_column($gap['underused'] ?? [], 'criterion_code');
        if ($underused !== []) {
            $parts[] = 'Beberapa kemampuan Anda mungkin belum banyak digunakan pada jalur ini: '.implode(', ', $underused).'. ';
        }

        $parts[] = 'Hasil ini merupakan indeks kecocokan berdasarkan jawaban dan profil kriteria karier, bukan jaminan karier atau probabilitas keberhasilan.';

        return implode('', $parts);
    }
}
