<?php

namespace App\Console\Commands;

use App\Services\Spk\SawCalculator;
use Illuminate\Console\Command;

class RankRatings extends Command
{
    protected $signature = 'techmind:rank {ratings*}';

    protected $description = 'Hitung ranking untuk 12 rating (urutan kriteria sesuai seeder).';

    public function handle(): int
    {
        $ratingsInput = $this->argument('ratings');
        if (count($ratingsInput) !== 12) {
            $this->error('Dibutuhkan tepat 12 rating.');

            return self::FAILURE;
        }

        $ratingsValues = array_map('intval', $ratingsInput);

        [$criteria, $careers] = $this->loadData();
        $codes = array_column($criteria, 'code');
        $ratings = array_combine($codes, $ratingsValues);

        $calculator = new SawCalculator;
        $result = $calculator->calculate($criteria, $careers, $ratings);

        $rows = [];
        foreach ($result['rankings'] as $row) {
            $rows[] = [
                $row['rank'],
                $row['career_code'],
                $row['display_score'],
                implode(', ', array_map(fn ($v) => round($v, 4), array_values($row['contributions']))),
            ];
        }

        $this->table(['Peringkat', 'Karier', 'Skor kecocokan', 'Kontribusi per kriteria'], $rows);

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
