<?php

namespace Tests\Feature;

use App\Models\Career;
use App\Models\CareerProfile;
use App\Models\Criterion;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('mysql')]
class ReferenceDataMysqlTest extends TestCase
{
    use RefreshDatabase;

    public function test_reference_seeder_is_idempotent_on_mysql(): void
    {
        $this->seed(ReferenceDataSeeder::class);
        $this->seed(ReferenceDataSeeder::class);

        $this->assertSame(12, Criterion::count());
        $this->assertSame(10, Career::count());
        $this->assertSame(120, CareerProfile::count());
    }

    public function test_career_profile_pair_must_be_unique_on_mysql(): void
    {
        $this->seed(ReferenceDataSeeder::class);
        $profile = CareerProfile::first();

        $this->expectException(QueryException::class);
        CareerProfile::query()->create([
            'career_id' => $profile->career_id,
            'criterion_id' => $profile->criterion_id,
            'score' => 3,
        ]);
    }

    public function test_career_profile_foreign_key_is_enforced_on_mysql(): void
    {
        $this->seed(ReferenceDataSeeder::class);
        $criterion = Criterion::first();

        $this->expectException(QueryException::class);
        CareerProfile::query()->create([
            'career_id' => 999999,
            'criterion_id' => $criterion->id,
            'score' => 3,
        ]);
    }

    public function test_career_profile_score_range_is_enforced_on_mysql(): void
    {
        $this->seed(ReferenceDataSeeder::class);
        $profile = CareerProfile::first();

        $this->expectException(QueryException::class);
        $profile->update(['score' => 0]);
    }
}
