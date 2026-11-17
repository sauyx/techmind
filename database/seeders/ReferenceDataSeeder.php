<?php

namespace Database\Seeders;

use App\Models\Career;
use App\Models\CareerProfile;
use App\Models\Criterion;
use Illuminate\Database\Seeder;

class ReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        $criteriaData = require database_path('seeders/data/criteria.php');
        $careersData = require database_path('seeders/data/careers.php');
        $profilesData = require database_path('seeders/data/career_profiles.php');

        foreach ($criteriaData as $data) {
            Criterion::query()->updateOrCreate(
                ['code' => $data['code']],
                $data
            );
        }

        foreach ($careersData as $data) {
            Career::query()->updateOrCreate(
                ['code' => $data['code']],
                $data
            );
        }

        $criteriaByCode = Criterion::query()->pluck('id', 'code');
        $careersByCode = Career::query()->pluck('id', 'code');

        foreach ($profilesData as $careerCode => $scores) {
            $careerId = $careersByCode->get($careerCode);
            if (! $careerId) {
                continue;
            }

            foreach ($scores as $criterionCode => $score) {
                $criterionId = $criteriaByCode->get($criterionCode);
                if (! $criterionId) {
                    continue;
                }

                CareerProfile::query()->updateOrCreate(
                    ['career_id' => $careerId, 'criterion_id' => $criterionId],
                    ['score' => $score]
                );
            }
        }
    }
}
