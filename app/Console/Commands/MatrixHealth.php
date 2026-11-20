<?php

namespace App\Console\Commands;

use App\Services\Spk\MatrixHealthAnalyzer;
use Illuminate\Console\Command;

class MatrixHealth extends Command
{
    protected $signature = 'techmind:matrix-health {--samples=2000} {--seed=42} {--json}';

    protected $description = 'Ukur kesehatan matriks profil karier (self-consistency, neighbors, robustness, tie cases).';

    public function handle(MatrixHealthAnalyzer $analyzer): int
    {
        $samples = max(1, (int) $this->option('samples'));
        $seed = (int) $this->option('seed');
        $result = $analyzer->analyze($samples, $seed);

        if ($this->option('json')) {
            $this->line((string) json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return self::SUCCESS;
        }

        $this->info('Self-consistency (rating = profil karier sendiri):');
        $rows = [];
        foreach ($result['self_consistency'] as $row) {
            $rows[] = [
                $row['career_code'],
                $row['rank'],
                round($row['score'] * 100, 2),
                $row['runner_up'],
                round($row['runner_up_score'] * 100, 2),
                round($row['margin'] * 100, 2),
            ];
        }
        $this->table(['Karier', 'Rank', 'Skor', 'Runner-up', 'Skor RU', 'Margin'], $rows);

        $this->info('Nearest neighbors (Euclidean):');
        $rows = [];
        foreach ($result['nearest_neighbors'] as $row) {
            foreach ($row['nearest'] as $neighbor) {
                $rows[] = [$row['career_code'], $neighbor['career_code'], round($neighbor['distance'], 3)];
            }
        }
        $this->table(['Karier', 'Tetangga', 'Jarak'], $rows);

        $this->info("Robustness (noise ±1, {$samples} sampel, seed {$seed}):");
        $rows = [];
        foreach ($result['robustness'] as $row) {
            $rows[] = [
                $row['career_code'],
                $row['wins'],
                $row['samples'],
                round($row['percent'], 2).'%',
            ];
        }
        $this->table(['Karier', 'Menang', 'Sampel', 'Persen'], $rows);

        $this->info('Tie cases (rating seragam + satu kriteria tinggi):');
        $this->line('Jumlah kasus: '.$result['tie_cases']['total_cases']);
        $this->line('Maks karier seri di rank #1: '.(string) ($result['tie_cases']['max_top_tie_count'] ?? 0));
        $rows = [];
        foreach ($result['tie_cases']['cases'] as $case) {
            $rows[] = [
                $case['case'],
                $case['top_tie_count'],
                implode(', ', $case['top_careers']),
            ];
        }
        $this->table(['Kasus', 'Seri #1', 'Karier'], $rows);

        return self::SUCCESS;
    }
}
