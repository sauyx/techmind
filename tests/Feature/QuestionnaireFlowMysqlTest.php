<?php

namespace Tests\Feature;

use App\Models\Criterion;
use App\Models\TestResult;
use App\Models\TestSession;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('mysql')]
class QuestionnaireFlowMysqlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ReferenceDataSeeder::class);
    }

    public function test_transactional_snapshot_and_json_columns_on_mysql(): void
    {
        $ratings = [];
        foreach (Criterion::query()->pluck('code') as $index => $code) {
            $ratings[$code] = [5, 4, 3, 2, 4, 3, 2, 5, 3, 4, 2, 3][$index % 12];
        }

        $this->from('/tes')
            ->post('/tes', [
                'visitor_name' => 'MySQL User',
                'ratings' => $ratings,
            ])
            ->assertRedirect();

        $session = TestSession::first();

        $this->assertSame(12, $session->answers()->count());
        $this->assertSame(10, $session->results()->count());

        $result = TestResult::query()->first();
        $this->assertIsArray($result->contribution_breakdown);
        $this->assertArrayHasKey('coding', $result->contribution_breakdown);
        $this->assertSame(10, TestResult::query()->count());
    }
}
