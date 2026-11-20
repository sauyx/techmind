<?php

namespace Tests\Feature;

use App\Models\Career;
use App\Models\CareerProfile;
use App\Models\Criterion;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferenceDataReseedTest extends TestCase
{
    use RefreshDatabase;

    public function test_reseed_updates_changed_profile_score(): void
    {
        $this->seed(ReferenceDataSeeder::class);

        $career = Career::query()->where('code', 'web_developer')->firstOrFail();
        $criterion = Criterion::query()->where('code', 'logic_problem_solving')->firstOrFail();
        $profile = CareerProfile::query()
            ->where('career_id', $career->id)
            ->where('criterion_id', $criterion->id)
            ->firstOrFail();

        $original = (int) $profile->score;
        $profile->update(['score' => $original === 5 ? 1 : 5]);

        $this->seed(ReferenceDataSeeder::class);

        $profile->refresh();
        $this->assertSame($original, (int) $profile->score);
    }

    public function test_reseed_preserves_admin_weight_change(): void
    {
        $this->seed(ReferenceDataSeeder::class);

        $criterion = Criterion::query()->where('code', 'logic_problem_solving')->firstOrFail();
        $criterion->update(['weight' => 1.75]);

        $this->seed(ReferenceDataSeeder::class);

        $this->assertSame(1.75, (float) $criterion->fresh()->weight);
    }
}
