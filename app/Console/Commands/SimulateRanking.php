<?php

namespace App\Console\Commands;

use App\Services\Spk\SawCalculator;
use Illuminate\Console\Command;

class SimulateRanking extends Command
{
    protected $signature = 'techmind:simulate-ranking {--n=10000} {--seed=42}';
    protected $description = 'Simulasi ranking untuk n vektor rating acak (seed tetap).';

    public function handle(): int
    {
        $n = (int) $this->option('n');
        $seed = (int) $this->option('seed');

        [$criteria, $careers] = $this->loadData();
        $codes = array_column($criteria, 'code');
        $calculator = new SawCalculator();

        mt_srand($seed);

        $topCounts = array_fill_keys(array_column($careers, 'code'), 0);
        $rankSums = array_fill_keys(array_column($careers, 'code'), 0);
        $scoreSums = array_fill_keys(array_column($careers, 'code'), 0.0);

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

        $this->table(['Karier', 'Rank 1', '% Rank 1', 'Rata-rata peringkat', 'Rata-rata skor'], $rows);

        return self::SUCCESS;
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
