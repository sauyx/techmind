<?php

namespace App\Services;

use App\Contracts\CareerRepository;
use App\Contracts\CriterionRepository;
use App\Contracts\TestSessionRepositoryInterface;
use App\Models\TestSession;
use App\Services\Spk\GapAnalyzer;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TestSubmissionService
{
    public function __construct(
        private CriterionRepository $criteria,
        private CareerRepository $careers,
        private TestSessionRepositoryInterface $sessions,
        private RecommendationService $recommendation,
        private GapAnalyzer $gapAnalyzer,
    ) {}

    /**
     * @param  array<string, mixed>  $ratings
     */
    public function submit(?string $visitorName, array $ratings): TestSession
    {
        $activeCriteria = $this->criteria->activeOrdered();
        $criterionCodes = $activeCriteria->pluck('code')->map(fn ($code) => (string) $code)->all();

        foreach ($criterionCodes as $code) {
            if (! array_key_exists($code, $ratings)) {
                throw ValidationException::withMessages(['ratings' => "Rating untuk kriteria {$code} wajib diisi."]);
            }
        }

        foreach (array_keys($ratings) as $code) {
            if (! in_array($code, $criterionCodes, true)) {
                throw ValidationException::withMessages(['ratings' => "Kode kriteria tidak dikenal: {$code}."]);
            }
        }

        $normalizedRatings = [];
        foreach ($criterionCodes as $code) {
            $rating = $ratings[$code];
            if (is_bool($rating) || ! is_numeric($rating)) {
                throw ValidationException::withMessages(['ratings' => "Rating {$code} harus berupa bilangan."]);
            }
            $numeric = (float) $rating;
            if (floor($numeric) !== $numeric || $numeric < 1 || $numeric > 5) {
                throw ValidationException::withMessages(['ratings' => "Rating {$code} harus bilangan bulat 1-5."]);
            }
            $normalizedRatings[$code] = (int) $numeric;
        }

        $recommendation = $this->recommendation->recommend($normalizedRatings);

        $careersByCode = $this->careers->allWithProfiles()->keyBy('code');

        $profilesByCareer = [];
        foreach ($careersByCode as $code => $career) {
            $scores = [];
            foreach ($career->careerProfiles as $profile) {
                $scores[(string) $profile->criterion->code] = (int) $profile->score;
            }
            $profilesByCareer[$code] = $scores;
        }

        $answersSnapshot = [];
        foreach ($activeCriteria as $criterion) {
            $code = (string) $criterion->code;
            $answersSnapshot[] = [
                'criterion_id' => $criterion->id,
                'criterion_code' => $code,
                'criterion_name' => (string) $criterion->name,
                'rating' => $normalizedRatings[$code],
                'multiplier_snapshot' => (float) $criterion->weight,
            ];
        }

        $resultsSnapshot = [];
        foreach ($recommendation['rankings'] as $ranking) {
            $careerCode = (string) $ranking['career_code'];
            $career = $careersByCode->get($careerCode);

            $gapForCareer = $this->gapAnalyzer->analyze(
                [$ranking],
                $normalizedRatings,
                $profilesByCareer
            );

            $gapSummary = $gapForCareer[$careerCode] ?? null;

            $resultsSnapshot[] = [
                'career_id' => $career?->id,
                'career_code' => $careerCode,
                'career_name' => (string) ($ranking['career_name'] ?? $careerCode),
                'rank' => (int) $ranking['rank'],
                'saw_score' => (float) $ranking['score'],
                'match_percentage' => (float) $ranking['display_score'],
                'contribution_breakdown' => $ranking['contributions'],
                'gap_summary' => $gapSummary,
            ];
        }

        $sessionData = [
            'token' => (string) Str::uuid(),
            'visitor_name' => $visitorName,
            'completed_at' => now(),
        ];

        return $this->sessions->createWithSnapshot($sessionData, $answersSnapshot, $resultsSnapshot);
    }
}
