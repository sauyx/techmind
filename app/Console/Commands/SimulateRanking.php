<?php

namespace App\Console\Commands;

use App\Services\Spk\SawCalculator;
use Illuminate\Console\Command;

class SimulateRanking extends Command
{
    protected $signature = 'techmind:simulate-ranking {--n=10000} {--seed=42}';

    protected $description = 'Simulasi ranking untuk skenario uniform, typical, dan peaky.';

    public function handle(): int
    {
        $n = (int) $this->option('n');
        $seed = (int) $this->option('seed');

        [$criteria, $careers] = $this->loadData();
        $codes = array_column($criteria, 'code');

        foreach (['uniform', 'typical', 'peaky'] as $scenario) {
            mt_srand($seed);
            $spare = null;

            $topCounts = array_fill_keys(array_column($careers, 'code'), 0);
            $rankSums = array_fill_keys(array_column($careers, 'code'), 0);
            $scoreSums = array_fill_keys(array_column($careers, 'code'), 0.0);

            for ($i = 0; $i < $n; $i++) {
                $ratings = $this->generateRatings($scenario, $codes, $spare);
                $result = (new SawCalculator)->calculate($criteria, $careers, $ratings);

                foreach ($result['rankings'] as $row) {
                    $rankSums[$row['career_code']] += $row['rank'];
                    $scoreSums[$row['career_code']] += $row['score'];
                    if ($row['rank'] === 1) {
                        $topCounts[$row['career_code']]++;
                    }
                }
            }

            $rows = [];
            foreach ($careers as $career) {
                $code = $career['code'];
                $rows[] = [
                    $code,
                    $topCounts[$code],
                    round(($topCounts[$code] / $n) * 100, 2).'%',
                    round($rankSums[$code] / $n, 3),
                    round($scoreSums[$code] / $n, 4),
                ];
            }

            $this->info("Skenario: {$scenario}");
            $this->table(['Karier', 'Rank 1', '% Rank 1', 'Rata-rata peringkat', 'Rata-rata skor'], $rows);
        }

        return self::SUCCESS;
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

        // peaky
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
