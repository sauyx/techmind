<?php

namespace Tests\Feature;

use App\Models\Career;
use App\Models\Criterion;
use App\Services\Spk\SawCalculator;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('fairness')]
class FairnessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ReferenceDataSeeder::class);
    }

    public function test_profile_exact_ranks_first(): void
    {
        [$criteria, $careers] = $this->loadData();
        $calculator = new SawCalculator;

        foreach ($careers as $career) {
            $ratings = $career['scores'];
            $result = $calculator->calculate($criteria, $careers, $ratings);
            $this->assertSame(
                $career['code'],
                $result['rankings'][0]['career_code'],
                'Profil persis '.$career['code'].' tidak rank 1'
            );
        }
    }

    public function test_imperfect_user_ranks_first(): void
    {
        [$criteria, $careers] = $this->loadData();
        $calculator = new SawCalculator;

        foreach ($careers as $career) {
            $ratings = $career['scores'];

            // (a) kriteria dengan nilai terendah (pertama jika seri) +1
            $lowestCode = null;
            $lowestValue = 6;
            foreach ($criteria as $criterion) {
                $code = (string) $criterion['code'];
                $value = (int) ($ratings[$code] ?? 0);
                if ($value < $lowestValue) {
                    $lowestValue = $value;
                    $lowestCode = $code;
                }
            }
            if ($lowestCode !== null) {
                $ratings[$lowestCode] = min(5, $ratings[$lowestCode] + 1);
            }

            // (b) kriteria pertama yang bernilai 4 (setelah langkah a) -1
            $targetCode = null;
            foreach ($criteria as $criterion) {
                $code = (string) $criterion['code'];
                if (($ratings[$code] ?? 0) === 4) {
                    $targetCode = $code;
                    break;
                }
            }
            if ($targetCode !== null) {
                $ratings[$targetCode] = max(1, $ratings[$targetCode] - 1);
            }

            $result = $calculator->calculate($criteria, $careers, $ratings);
            $top = $result['rankings'][0];
            $second = $result['rankings'][1] ?? null;

            $this->assertSame(
                $career['code'],
                $top['career_code'],
                'Pengguna tidak sempurna '.$career['code'].' tidak rank 1'
            );

            if ($second !== null) {
                $this->assertGreaterThan(
                    0.0,
                    $top['score'] - $second['score'],
                    'Margin '.$career['code'].' tidak > 0'
                );
            }
        }
    }

    /**
     * @return array{0: array<int, array<string, mixed>>, 1: array<int, array<string, mixed>>}
     */
    private function loadData(): array
    {
        $criteria = Criterion::query()
            ->where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('id')
            ->get()
            ->map(fn (Criterion $criterion) => [
                'code' => (string) $criterion->code,
                'multiplier' => (float) $criterion->weight,
                'sort_order' => (int) $criterion->display_order,
            ])
            ->values()
            ->all();

        $careers = Career::query()
            ->where('is_active', true)
            ->with(['careerProfiles.criterion'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function (Career $career) {
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
            })
            ->values()
            ->all();

        return [$criteria, $careers];
    }
}
