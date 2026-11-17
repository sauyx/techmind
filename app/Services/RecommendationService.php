<?php

namespace App\Services;

use App\Contracts\CareerRepository;
use App\Contracts\CriterionRepository;
use App\Services\Spk\GapAnalyzer;
use App\Services\Spk\SawCalculator;

class RecommendationService
{
    public function __construct(
        private CriterionRepository $criteria,
        private CareerRepository $careers,
        private SawCalculator $calculator = new SawCalculator(),
        private GapAnalyzer $gapAnalyzer = new GapAnalyzer(),
    ) {
    }

    /**
     * @param array<string, int|float|string> $ratings
     * @return array<string, mixed>
     */
    public function recommend(array $ratings): array
    {
        $criteria = $this->criteria->activeOrdered()->map(fn ($criterion) => [
            'code' => (string) $criterion->code,
            'multiplier' => (float) $criterion->weight,
            'sort_order' => (int) $criterion->display_order,
        ])->values()->all();

        $careers = $this->careers->activeWithProfiles()->map(function ($career) {
            $scores = [];
            foreach ($career->careerProfiles as $profile) {
                $scores[(string) $profile->criterion->code] = (int) $profile->score;
            }

            return [
                'code' => (string) $career->code,
                'sort_order' => (int) $career->sort_order,
                'scores' => $scores,
            ];
        })->values()->all();

        $profilesByCareer = [];
        foreach ($careers as $career) {
            $profilesByCareer[$career['code']] = $career['scores'];
        }

        $result = $this->calculator->calculate($criteria, $careers, $ratings);
        $result['gap_analysis'] = $this->gapAnalyzer->analyze(
            $result['rankings'],
            $ratings,
            $profilesByCareer
        );

        return $result;
    }
}
