<?php

namespace Tests\Feature;

use App\Services\Spk\SawCalculator;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('fairness')]
class FairnessTest extends TestCase
{
    public function test_self_profile_ranks_first(): void
    {
        [$criteria, $careers] = $this->loadData();
        $calculator = new SawCalculator;

        foreach ($careers as $career) {
            $ratings = $career['scores'];
            $result = $calculator->calculate($criteria, $careers, $ratings);
            $top = $result['rankings'][0]['career_code'];
            $this->assertSame($career['code'], $top, "Self-profile {$career['code']} ranked {$top}");
        }
    }

    public function test_personas_rank_expected_career_first(): void
    {
        [$criteria, $careers] = $this->loadData();
        $calculator = new SawCalculator;

        $personas = [
            'web_developer' => [4, 2, 2, 3, 3, 3, 3, 3, 5, 2, 3, 2],
            'mobile_developer' => [4, 2, 2, 4, 3, 3, 3, 3, 5, 2, 3, 2],
            'backend_developer' => [5, 3, 3, 4, 1, 2, 2, 3, 5, 4, 4, 2],
            'data_analyst' => [4, 5, 5, 4, 2, 2, 2, 4, 3, 1, 2, 3],
            'ui_ux_designer' => [3, 3, 1, 4, 5, 5, 5, 4, 2, 1, 2, 4],
            'qa_engineer' => [4, 3, 2, 5, 2, 2, 3, 3, 3, 2, 3, 2],
            'devops_engineer' => [4, 3, 2, 4, 1, 2, 2, 3, 4, 5, 4, 2],
            'network_engineer' => [4, 2, 2, 4, 1, 2, 2, 3, 3, 5, 4, 2],
            'cybersecurity_analyst' => [4, 3, 2, 5, 1, 2, 2, 4, 3, 4, 5, 3],
            'system_analyst' => [4, 3, 2, 4, 2, 3, 5, 5, 1, 1, 2, 5],
        ];

        $codes = array_column($criteria, 'code');

        foreach ($personas as $expected => $values) {
            $ratings = array_combine($codes, $values);
            $result = $calculator->calculate($criteria, $careers, $ratings);
            $top = $result['rankings'][0]['career_code'];
            $this->assertSame($expected, $top, "Persona {$expected} ranked {$top}");
        }
    }

    public function test_uniform_ratings_report_scores(): void
    {
        [$criteria, $careers] = $this->loadData();
        $calculator = new SawCalculator;
        $codes = array_column($criteria, 'code');

        foreach ([1, 3, 5] as $value) {
            $ratings = array_fill_keys($codes, $value);
            $result = $calculator->calculate($criteria, $careers, $ratings);
            // Tidak ada assertion ketat; hasil dibaca dari command simulasi.
            $this->assertNotEmpty($result['rankings']);
        }
    }

    public function test_random_simulation_thresholds(): void
    {
        [$criteria, $careers] = $this->loadData();
        $calculator = new SawCalculator;
        $codes = array_column($criteria, 'code');

        mt_srand(42);
        $topCounts = array_fill_keys(array_column($careers, 'code'), 0);
        $rankSums = array_fill_keys(array_column($careers, 'code'), 0);
        $scoreSums = array_fill_keys(array_column($careers, 'code'), 0.0);

        $n = 10000;
        for ($i = 0; $i < $n; $i++) {
            $ratings = [];
            foreach ($codes as $code) {
                $ratings[$code] = mt_rand(1, 5);
            }
            $result = $calculator->calculate($criteria, $careers, $ratings);
            foreach ($result['rankings'] as $row) {
                $rankSums[$row['career_code']] += $row['rank'];
                $scoreSums[$row['career_code']] += $row['score'];
                if ($row['rank'] === 1) {
                    $topCounts[$row['career_code']]++;
                }
            }
        }

        foreach ($careers as $career) {
            $code = $career['code'];
            $percentage = ($topCounts[$code] / $n) * 100;
            $this->assertLessThanOrEqual(25.0, $percentage, "{$code} has {$percentage}% rank 1");
            $this->assertGreaterThanOrEqual(3.0, $percentage, "{$code} has {$percentage}% rank 1");
        }
    }

    private function loadData(): array
    {
        $criteriaData = require base_path('database/seeders/data/criteria.php');
        $profilesData = require base_path('database/seeders/data/career_profiles.php');

        $criteria = array_map(fn ($row) => [
            'code' => $row['code'],
            'multiplier' => (float) $row['weight'],
            'sort_order' => (int) $row['display_order'],
        ], $criteriaData);

        $careers = [];
        foreach ($profilesData as $code => $scores) {
            $careers[] = [
                'code' => $code,
                'sort_order' => 0,
                'scores' => $scores,
            ];
        }

        // sort_order dari careers.php
        $careersData = require base_path('database/seeders/data/careers.php');
        foreach ($careers as &$career) {
            foreach ($careersData as $row) {
                if ($row['code'] === $career['code']) {
                    $career['sort_order'] = (int) $row['sort_order'];
                }
            }
        }
        unset($career);

        return [$criteria, $careers];
    }
}
