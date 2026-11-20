<?php

namespace Tests\Feature;

use App\Services\Spk\MatrixHealthAnalyzer;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('matrix-health')]
class MatrixHealthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ReferenceDataSeeder::class);
    }

    public function test_self_consistency_all_rank_one_with_margin(): void
    {
        $analyzer = app(MatrixHealthAnalyzer::class);
        $results = $analyzer->selfConsistency();

        foreach ($results as $row) {
            $this->assertSame(1, $row['rank'], $row['career_code'].' harus rank 1');
            $this->assertGreaterThanOrEqual(2.0, $row['margin'] * 100, $row['career_code'].' margin terlalu kecil');
        }
    }

    public function test_robustness_each_career_at_least_80_percent(): void
    {
        $analyzer = app(MatrixHealthAnalyzer::class);
        $results = $analyzer->robustness(2000, 42);

        foreach ($results as $row) {
            $this->assertGreaterThanOrEqual(80.0, $row['percent'], $row['career_code'].' robustness di bawah 80%');
        }
    }

    public function test_nearest_neighbors_distance_at_least_3(): void
    {
        $analyzer = app(MatrixHealthAnalyzer::class);
        $results = $analyzer->nearestNeighbors();

        foreach ($results as $row) {
            foreach ($row['nearest'] as $neighbor) {
                if ($neighbor === $row['nearest'][0]) {
                    $this->assertGreaterThanOrEqual(3.0, $neighbor['distance'], $row['career_code'].' terlalu dekat dengan '.$neighbor['career_code']);
                }
            }
        }
    }
}
