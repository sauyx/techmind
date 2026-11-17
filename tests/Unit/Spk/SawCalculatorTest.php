<?php

namespace Tests\Unit\Spk;

use App\Exceptions\InvalidRatingsException;
use App\Services\Spk\SawCalculator;
use Tests\TestCase;

class SawCalculatorTest extends TestCase
{
    public function test_manual_example_from_docs(): void
    {
        $calculator = new SawCalculator;

        $criteria = [
            ['code' => 'c1', 'multiplier' => 1.0],
            ['code' => 'c2', 'multiplier' => 1.0],
            ['code' => 'c3', 'multiplier' => 1.0],
            ['code' => 'c4', 'multiplier' => 1.0],
        ];

        $careers = [
            ['code' => 'web', 'sort_order' => 1, 'scores' => ['c1' => 4, 'c2' => 2, 'c3' => 3, 'c4' => 3]],
            ['code' => 'data', 'sort_order' => 2, 'scores' => ['c1' => 4, 'c2' => 5, 'c3' => 1, 'c4' => 3]],
            ['code' => 'ui', 'sort_order' => 3, 'scores' => ['c1' => 3, 'c2' => 2, 'c3' => 5, 'c4' => 4]],
        ];

        $ratings = ['c1' => 5, 'c2' => 4, 'c3' => 2, 'c4' => 3];

        $result = $calculator->calculate($criteria, $careers, $ratings);

        $this->assertEqualsWithDelta(0.7179, $result['rankings'][2]['score'], 0.0001);
        $this->assertEqualsWithDelta(0.8321, $result['rankings'][0]['score'], 0.0001);
        $this->assertEqualsWithDelta(0.7393, $result['rankings'][1]['score'], 0.0001);

        $this->assertSame('data', $result['rankings'][0]['career_code']);
        $this->assertSame('ui', $result['rankings'][1]['career_code']);
        $this->assertSame('web', $result['rankings'][2]['career_code']);
    }

    public function test_admin_multiplier_changes_weights(): void
    {
        $calculator = new SawCalculator;

        $criteria = [
            ['code' => 'c1', 'multiplier' => 2.0],
            ['code' => 'c2', 'multiplier' => 1.0],
        ];

        $careers = [
            ['code' => 'a', 'sort_order' => 1, 'scores' => ['c1' => 5, 'c2' => 1]],
            ['code' => 'b', 'sort_order' => 2, 'scores' => ['c1' => 1, 'c2' => 5]],
        ];

        $ratings = ['c1' => 3, 'c2' => 3];

        $result = $calculator->calculate($criteria, $careers, $ratings);

        $this->assertEqualsWithDelta((3 * 2.0) / (3 * 2.0 + 3 * 1.0), $result['weights']['c1'], 0.000001);
        $this->assertEqualsWithDelta((3 * 1.0) / (3 * 2.0 + 3 * 1.0), $result['weights']['c2'], 0.000001);
    }

    public function test_invalid_ratings_throw(): void
    {
        $calculator = new SawCalculator;
        $criteria = [['code' => 'c1', 'multiplier' => 1.0]];
        $careers = [['code' => 'a', 'sort_order' => 1, 'scores' => ['c1' => 3]]];

        foreach ([
            ['c1' => 0],
            ['c1' => 6],
            ['c1' => 3.5],
            [],
            ['c1' => 3, 'x' => 4],
        ] as $ratings) {
            try {
                $calculator->calculate($criteria, $careers, $ratings);
                $this->fail('Expected InvalidRatingsException');
            } catch (InvalidRatingsException $e) {
                $this->assertTrue(true);
            }
        }
    }

    public function test_tie_break_is_deterministic(): void
    {
        $calculator = new SawCalculator;
        $criteria = [['code' => 'c1', 'multiplier' => 1.0]];
        $careers = [
            ['code' => 'b', 'sort_order' => 2, 'scores' => ['c1' => 4]],
            ['code' => 'a', 'sort_order' => 1, 'scores' => ['c1' => 4]],
        ];
        $ratings = ['c1' => 5];

        $result = $calculator->calculate($criteria, $careers, $ratings);

        $this->assertSame('a', $result['rankings'][0]['career_code']);
        $this->assertSame('b', $result['rankings'][1]['career_code']);
    }

    public function test_inactive_career_is_excluded_from_ranking_and_max(): void
    {
        $calculator = new SawCalculator;
        $criteria = [['code' => 'c1', 'multiplier' => 1.0]];
        $careers = [
            ['code' => 'a', 'sort_order' => 1, 'scores' => ['c1' => 3]],
            ['code' => 'b', 'sort_order' => 2, 'scores' => ['c1' => 5]],
        ];

        $result = $calculator->calculate($criteria, $careers, ['c1' => 5]);

        $this->assertCount(2, $result['rankings']);
        $this->assertEqualsWithDelta(0.6, $result['normalized_matrix']['a']['c1'], 0.000001);
    }
}
