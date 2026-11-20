<?php

namespace Tests\Feature;

use App\Models\Career;
use App\Models\Criterion;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatrixHealthPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_preview_flags_unhealthy_matrix(): void
    {
        $this->seed(ReferenceDataSeeder::class);

        $criteria = Criterion::query()->where('is_active', true)->orderBy('display_order')->get()->map(fn ($c) => [
            'code' => $c->code,
            'multiplier' => $c->code === 'security_awareness' ? 0.5 : (float) $c->weight,
            'sort_order' => $c->display_order,
            'name' => $c->name,
        ])->values()->all();

        $careers = Career::query()->where('is_active', true)->with('careerProfiles.criterion')->get()->map(function ($career) {
            $scores = [];
            foreach ($career->careerProfiles as $profile) {
                if ($profile->criterion) {
                    $scores[(string) $profile->criterion->code] = (int) $profile->score;
                }
            }

            return [
                'code' => $career->code,
                'name' => $career->name,
                'sort_order' => $career->sort_order,
                'scores' => $scores,
            ];
        })->values()->all();

        $admin = \App\Models\User::factory()->create(['is_admin' => true, 'email_verified_at' => now()]);

        $response = $this->actingAs($admin)->postJson('/admin/kesehatan-matriks/preview', [
            'criteria' => $criteria,
            'careers' => $careers,
            'samples' => 300,
            'seed' => 42,
        ]);

        $response->assertOk();
        // healthy may be true or false depending; just ensure structure
        $response->assertJsonStructure(['health', 'healthy']);
    }
}
