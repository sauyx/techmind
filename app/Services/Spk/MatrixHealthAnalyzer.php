<?php

namespace App\Services\Spk;

use App\Contracts\CareerRepository;
use App\Contracts\CriterionRepository;

class MatrixHealthAnalyzer
{
    private const TIE_EPSILON = 1e-9;

    public function __construct(
        private CriterionRepository $criteria,
        private CareerRepository $careers,
        private SawCalculator $calculator = new SawCalculator,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function selfConsistency(?array $criteria = null, ?array $careers = null): array
    {
        if ($criteria === null || $careers === null) {
            [$criteria, $careers] = $this->loadData();
        }
        $codes = array_column($criteria, 'code');
        $result = [];

        foreach ($careers as $career) {
            $ratings = [];
            foreach ($codes as $code) {
                $ratings[$code] = (int) ($career['scores'][$code] ?? 0);
            }

            $calculation = $this->calculator->calculate($criteria, $careers, $ratings);
            $rankings = $calculation['rankings'];

            $own = null;
            $bestOther = null;
            foreach ($rankings as $row) {
                if ($row['career_code'] === $career['code']) {
                    $own = $row;
                } elseif ($bestOther === null || $row['score'] > $bestOther['score']) {
                    $bestOther = $row;
                }
            }

            $result[] = [
                'career_code' => $career['code'],
                'career_name' => $career['name'],
                'rank' => (int) ($own['rank'] ?? 0),
                'score' => (float) ($own['score'] ?? 0.0),
                'display_score' => (float) ($own['display_score'] ?? 0.0),
                'runner_up' => $bestOther['career_code'] ?? null,
                'runner_up_score' => (float) ($bestOther['score'] ?? 0.0),
                'margin' => (float) (($own['score'] ?? 0.0) - ($bestOther['score'] ?? 0.0)),
            ];
        }

        return $result;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function nearestNeighbors(?array $criteria = null, ?array $careers = null): array
    {
        if ($criteria === null || $careers === null) {
            [$criteria, $careers] = $this->loadData();
        }
        $codes = array_column($criteria, 'code');
        $result = [];

        foreach ($careers as $career) {
            $distances = [];
            foreach ($careers as $other) {
                if ($other['code'] === $career['code']) {
                    continue;
                }

                $sum = 0.0;
                foreach ($codes as $code) {
                    $a = (int) ($career['scores'][$code] ?? 0);
                    $b = (int) ($other['scores'][$code] ?? 0);
                    $sum += ($a - $b) ** 2;
                }
                $distances[] = [
                    'career_code' => $other['code'],
                    'career_name' => $other['name'],
                    'distance' => sqrt($sum),
                ];
            }

            usort($distances, fn (array $a, array $b): int => $a['distance'] <=> $b['distance']);

            $result[] = [
                'career_code' => $career['code'],
                'career_name' => $career['name'],
                'nearest' => array_slice($distances, 0, 2),
            ];
        }

        return $result;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function robustness(int $samples = 2000, int $seed = 42, ?array $criteria = null, ?array $careers = null): array
    {
        if ($criteria === null || $careers === null) {
            [$criteria, $careers] = $this->loadData();
        }
        $codes = array_column($criteria, 'code');
        mt_srand($seed);

        $counts = [];
        foreach ($careers as $career) {
            $counts[$career['code']] = 0;
        }

        foreach ($careers as $career) {
            for ($i = 0; $i < $samples; $i++) {
                $ratings = [];
                foreach ($codes as $code) {
                    $base = (int) ($career['scores'][$code] ?? 0);
                    $noise = mt_rand(0, 2) - 1;
                    $value = $base + $noise;
                    if ($value < 1) {
                        $value = 1;
                    }
                    if ($value > 5) {
                        $value = 5;
                    }
                    $ratings[$code] = $value;
                }

                $calculation = $this->calculator->calculate($criteria, $careers, $ratings);
                $rankings = $calculation['rankings'];

                $top = $rankings[0] ?? null;
                $second = $rankings[1] ?? null;
                if (
                    $top !== null
                    && $top['career_code'] === $career['code']
                    && $second !== null
                    && ($top['score'] - $second['score']) > self::TIE_EPSILON
                ) {
                    $counts[$career['code']]++;
                }
            }
        }

        $result = [];
        foreach ($careers as $career) {
            $result[] = [
                'career_code' => $career['code'],
                'career_name' => $career['name'],
                'samples' => $samples,
                'wins' => $counts[$career['code']],
                'percent' => $samples > 0 ? ($counts[$career['code']] / $samples) * 100 : 0.0,
            ];
        }

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    public function tieCases(?array $criteria = null, ?array $careers = null): array
    {
        if ($criteria === null || $careers === null) {
            [$criteria, $careers] = $this->loadData();
        }
        $codes = array_column($criteria, 'code');
        $cases = [];

        $uniformValues = [1, 3, 5];
        foreach ($uniformValues as $value) {
            $ratings = array_fill_keys($codes, $value);
            $calculation = $this->calculator->calculate($criteria, $careers, $ratings);
            $cases[] = $this->summarizeTieCase("uniform_$value", $ratings, $calculation);
        }

        foreach ($codes as $code) {
            $ratings = array_fill_keys($codes, 1);
            $ratings[$code] = 5;
            $calculation = $this->calculator->calculate($criteria, $careers, $ratings);
            $cases[] = $this->summarizeTieCase("high_$code", $ratings, $calculation);
        }

        $maxTie = 0;
        foreach ($cases as $case) {
            if ($case['top_tie_count'] > $maxTie) {
                $maxTie = $case['top_tie_count'];
            }
        }

        return [
            'cases' => $cases,
            'max_top_tie_count' => $maxTie,
            'total_cases' => count($cases),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function analyze(int $samples = 2000, int $seed = 42, ?array $criteria = null, ?array $careers = null): array
    {
        if ($criteria === null || $careers === null) {
            [$criteria, $careers] = $this->loadData();
        }

        if (count($careers) === 0) {
            return [
                'self_consistency' => [],
                'nearest_neighbors' => [],
                'robustness' => [],
                'tie_cases' => ['cases' => [], 'max_top_tie_count' => 0, 'total_cases' => 0],
            ];
        }

        return [
            'self_consistency' => $this->selfConsistency($criteria, $careers),
            'nearest_neighbors' => $this->nearestNeighbors($criteria, $careers),
            'robustness' => $this->robustness($samples, $seed, $criteria, $careers),
            'tie_cases' => $this->tieCases($criteria, $careers),
        ];
    }

    public function isHealthy(array $health): bool
    {
        foreach ($health['self_consistency'] as $row) {
            if ($row['rank'] !== 1 || ($row['margin'] * 100) < 2.0) {
                return false;
            }
        }

        foreach ($health['robustness'] as $row) {
            if ($row['percent'] < 80.0) {
                return false;
            }
        }

        foreach ($health['nearest_neighbors'] as $row) {
            foreach ($row['nearest'] as $index => $neighbor) {
                if ($index === 0 && $neighbor['distance'] < 3.0) {
                    return false;
                }
            }
        }

        return true;
    }

    public function analyzeMatrix(array $criteria, array $careers, int $samples = 500, int $seed = 42): array
    {
        return $this->analyze($samples, $seed, $criteria, $careers);
    }

    /**
     * @return array{0: array<int, array<string, mixed>>, 1: array<int, array<string, mixed>>}
     */
    private function loadData(): array
    {
        $criteria = $this->criteria->activeOrdered()->map(fn ($criterion) => [
            'code' => (string) $criterion->code,
            'multiplier' => (float) $criterion->weight,
            'sort_order' => (int) $criterion->display_order,
            'name' => (string) $criterion->name,
        ])->values()->all();

        $careers = $this->careers->activeWithProfiles()->map(function ($career) {
            $scores = [];
            foreach ($career->careerProfiles as $profile) {
                if ($profile->criterion) {
                    $scores[(string) $profile->criterion->code] = (int) $profile->score;
                }
            }

            return [
                'code' => (string) $career->code,
                'name' => (string) $career->name,
                'sort_order' => (int) $career->sort_order,
                'scores' => $scores,
            ];
        })->values()->all();

        return [$criteria, $careers];
    }

    /**
     * @return array<string, mixed>
     */
    private function summarizeTieCase(string $label, array $ratings, array $calculation): array
    {
        $rankings = $calculation['rankings'];
        $topScore = (float) ($rankings[0]['score'] ?? 0.0);
        $topCareers = [];
        foreach ($rankings as $row) {
            if (abs((float) $row['score'] - $topScore) < self::TIE_EPSILON) {
                $topCareers[] = (string) $row['career_code'];
            }
        }

        return [
            'case' => $label,
            'ratings' => $ratings,
            'top_score' => $topScore,
            'top_tie_count' => count($topCareers),
            'top_careers' => $topCareers,
        ];
    }
}
