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
        $codes = array_column($criteria, 'code');

        $personas = [
            // web_developer: suka ngoding, logika, desain secukupnya
            'web_developer' => [4, 2, 2, 3, 3, 3, 3, 3, 5, 2, 3, 2],
            // mobile_developer: ngoding mobile, UI, ketelitian
            'mobile_developer' => [4, 2, 2, 4, 3, 3, 3, 3, 5, 2, 3, 2],
            // backend_developer: logika, coding, infra, keamanan
            'backend_developer' => [5, 3, 3, 4, 1, 2, 2, 3, 5, 4, 4, 2],
            // data_analyst: data, matematika, komunikasi
            'data_analyst' => [4, 5, 5, 4, 2, 2, 2, 4, 3, 1, 2, 3],
            // ui_ux_designer: desain, kreativitas, empati, komunikasi
            'ui_ux_designer' => [3, 3, 1, 4, 5, 5, 5, 4, 2, 1, 2, 4],
            // qa_engineer: ketelitian, logika, pengujian
            'qa_engineer' => [4, 3, 2, 5, 2, 2, 3, 3, 3, 2, 3, 2],
            // devops_engineer: coding, infra, keamanan, ketelitian
            'devops_engineer' => [4, 3, 2, 4, 1, 2, 2, 3, 4, 5, 4, 2],
            // network_engineer: jaringan, keamanan, ketelitian
            'network_engineer' => [4, 2, 2, 4, 1, 2, 2, 3, 3, 5, 4, 2],
            // cybersecurity_analyst: keamanan, ketelitian, jaringan
            'cybersecurity_analyst' => [4, 3, 2, 5, 1, 2, 2, 4, 3, 4, 5, 3],
            // system_analyst: komunikasi, empati, bisnis, logika
            'system_analyst' => [4, 3, 2, 4, 2, 3, 5, 5, 1, 1, 2, 5],
        ];

        foreach ($personas as $expected => $values) {
            $ratings = array_combine($codes, $values);
            $result = $calculator->calculate($criteria, $careers, $ratings);
            $top = $result['rankings'][0]['career_code'];
            $this->assertSame($expected, $top, "Persona {$expected} ranked {$top}");
        }
    }

    public function test_second_personas_rank_expected_career_first(): void
    {
        [$criteria, $careers] = $this->loadData();
        $calculator = new SawCalculator;
        $codes = array_column($criteria, 'code');

        $personas = [
            // web_developer: fokus UI dan coding, kurang infra
            'web_developer' => [3, 2, 1, 3, 4, 4, 3, 4, 4, 2, 2, 3],
            // mobile_developer: fokus mobile UI dan ketelitian
            'mobile_developer' => [3, 2, 1, 4, 4, 3, 3, 3, 4, 1, 3, 3],
            // backend_developer: fokus server, data, keamanan
            'backend_developer' => [4, 3, 2, 4, 1, 2, 2, 3, 5, 4, 4, 3],
            // data_analyst: fokus data, matematika, bisnis
            'data_analyst' => [3, 4, 4, 3, 2, 3, 2, 4, 2, 1, 2, 4],
            // ui_ux_designer: fokus visual, kreativitas, empati
            'ui_ux_designer' => [2, 2, 1, 3, 5, 5, 4, 4, 1, 1, 1, 4],
            // qa_engineer: fokus ketelitian dan pengujian
            'qa_engineer' => [4, 3, 2, 5, 2, 2, 3, 4, 2, 2, 3, 3],
            // devops_engineer: fokus infra, coding, keamanan
            'devops_engineer' => [4, 3, 2, 4, 1, 2, 2, 3, 4, 5, 4, 3],
            // network_engineer: fokus jaringan dan keamanan
            'network_engineer' => [3, 2, 2, 4, 1, 2, 2, 3, 2, 5, 4, 2],
            // cybersecurity_analyst: fokus keamanan dan analisis
            'cybersecurity_analyst' => [3, 3, 2, 5, 1, 2, 2, 4, 2, 4, 5, 3],
            // system_analyst: fokus bisnis, komunikasi, empati
            'system_analyst' => [3, 3, 2, 4, 2, 3, 4, 5, 1, 1, 2, 5],
        ];

        foreach ($personas as $expected => $values) {
            $ratings = array_combine($codes, $values);
            $result = $calculator->calculate($criteria, $careers, $ratings);
            $top = $result['rankings'][0]['career_code'];
            $this->assertSame($expected, $top, "Persona kedua {$expected} ranked {$top}");
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
            $this->assertNotEmpty($result['rankings']);
        }
    }

    public function test_random_simulation_thresholds(): void
    {
        [$criteria, $careers] = $this->loadData();
        $calculator = new SawCalculator;
        $codes = array_column($criteria, 'code');

        foreach (['uniform', 'typical', 'peaky'] as $scenario) {
            mt_srand(42);
            $spare = null;

            $topCounts = array_fill_keys(array_column($careers, 'code'), 0);

            $n = 10000;
            for ($i = 0; $i < $n; $i++) {
                $ratings = $this->generateRatings($scenario, $codes, $spare);
                $result = $calculator->calculate($criteria, $careers, $ratings);
                if ($result['rankings'][0]['career_code'] !== null) {
                    $topCounts[$result['rankings'][0]['career_code']]++;
                }
            }

            foreach ($careers as $career) {
                $code = $career['code'];
                $percentage = ($topCounts[$code] / $n) * 100;
                $this->assertLessThanOrEqual(25.0, $percentage, "{$scenario}: {$code} has {$percentage}% rank 1");
                $this->assertGreaterThanOrEqual(3.0, $percentage, "{$scenario}: {$code} has {$percentage}% rank 1");
            }
        }
    }

    private function generateRatings(string $scenario, array $codes, ?float &$spare): array
    {
        $ratings = [];

        if ($scenario === 'uniform') {
            foreach ($codes as $code) {
                $ratings[$code] = mt_rand(1, 5);
            }

            return $ratings;
        }

        if ($scenario === 'typical') {
            foreach ($codes as $code) {
                $ratings[$code] = $this->normalRating($spare);
            }

            return $ratings;
        }

        $k = [2, 3, 4][mt_rand(0, 2)];
        $indices = range(0, count($codes) - 1);
        for ($i = count($indices) - 1; $i > 0; $i--) {
            $j = mt_rand(0, $i);
            [$indices[$i], $indices[$j]] = [$indices[$j], $indices[$i]];
        }
        $high = array_flip(array_slice($indices, 0, $k));

        foreach (array_values($codes) as $index => $code) {
            $ratings[$code] = isset($high[$index]) ? 5 : mt_rand(2, 3);
        }

        return $ratings;
    }

    private function normalRating(?float &$spare): int
    {
        if ($spare !== null) {
            $value = $spare;
            $spare = null;
        } else {
            do {
                $u1 = mt_rand() / mt_getrandmax();
                $u2 = mt_rand() / mt_getrandmax();
            } while ($u1 <= 0.0);

            $magnitude = sqrt(-2.0 * log($u1));
            $z0 = $magnitude * cos(2.0 * M_PI * $u2);
            $z1 = $magnitude * sin(2.0 * M_PI * $u2);

            $value = 3.5 + 1.1 * $z0;
            $spare = 3.5 + 1.1 * $z1;
        }

        return max(1, min(5, (int) round($value)));
    }

    private function loadData(): array
    {
        $criteriaData = require base_path('database/seeders/data/criteria.php');
        $profilesData = require base_path('database/seeders/data/career_profiles.php');
        $careersData = require base_path('database/seeders/data/careers.php');

        $criteria = array_map(fn ($row) => [
            'code' => $row['code'],
            'multiplier' => (float) $row['weight'],
            'sort_order' => (int) $row['display_order'],
        ], $criteriaData);

        $careers = [];
        foreach ($profilesData as $code => $scores) {
            $sortOrder = 0;
            foreach ($careersData as $row) {
                if ($row['code'] === $code) {
                    $sortOrder = (int) $row['sort_order'];
                }
            }
            $careers[] = [
                'code' => $code,
                'sort_order' => $sortOrder,
                'scores' => $scores,
            ];
        }

        return [$criteria, $careers];
    }
}
