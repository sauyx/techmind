<?php

namespace Tests\Feature;

use App\Contracts\CareerProfileRepository;
use App\Contracts\CareerRepository;
use App\Contracts\CriterionRepository;
use App\Models\Career;
use App\Models\CareerProfile;
use App\Models\Criterion;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferenceDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_reference_seeder_creates_expected_counts(): void
    {
        $this->seed(ReferenceDataSeeder::class);

        $this->assertSame(12, Criterion::count());
        $this->assertSame(10, Career::count());
        $this->assertSame(120, CareerProfile::count());
    }

    public function test_reference_seeder_is_idempotent(): void
    {
        $this->seed(ReferenceDataSeeder::class);
        $this->seed(ReferenceDataSeeder::class);

        $this->assertSame(12, Criterion::count());
        $this->assertSame(10, Career::count());
        $this->assertSame(120, CareerProfile::count());
    }

    public function test_career_profile_score_must_be_between_1_and_5(): void
    {
        $this->seed(ReferenceDataSeeder::class);
        $career = Career::first();
        $criterion = Criterion::first();

        $this->expectException(QueryException::class);
        CareerProfile::query()->create([
            'career_id' => $career->id,
            'criterion_id' => $criterion->id,
            'score' => 0,
        ]);
    }

    public function test_career_profile_pair_must_be_unique(): void
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

    public function test_career_repository_returns_complete_matrix(): void
    {
        $this->seed(ReferenceDataSeeder::class);

        $careers = app(CareerRepository::class)->activeWithProfiles();
        $criteriaCodes = app(CriterionRepository::class)->activeOrdered()->pluck('code')->all();

        $this->assertCount(10, $careers);

        foreach ($careers as $career) {
            $profileCodes = $career->careerProfiles
                ->sortBy(fn ($profile) => $profile->criterion->display_order)
                ->map(fn ($profile) => $profile->criterion->code)
                ->values()
                ->all();

            $this->assertCount(12, $career->careerProfiles);
            $this->assertSame($criteriaCodes, $profileCodes);

            foreach ($career->careerProfiles as $profile) {
                $this->assertGreaterThanOrEqual(1, $profile->score);
                $this->assertLessThanOrEqual(5, $profile->score);
            }
        }
    }

    public function test_career_profile_repository_can_upsert_score(): void
    {
        $this->seed(ReferenceDataSeeder::class);
        $profile = CareerProfile::first();

        app(CareerProfileRepository::class)->upsertScore(
            $profile->career_id,
            $profile->criterion_id,
            5
        );

        $this->assertDatabaseHas('career_profiles', [
            'career_id' => $profile->career_id,
            'criterion_id' => $profile->criterion_id,
            'score' => 5,
        ]);
    }
}
