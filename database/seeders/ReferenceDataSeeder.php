<?php

namespace Database\Seeders;

use App\Contracts\CareerProfileRepository;
use App\Contracts\CareerRepository;
use App\Contracts\CriterionRepository;
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

        // Sinkronisasi data referensi. Aman selama belum ada tabel hasil tes.
        // Setelah tabel hasil ada, penghapusan diganti is_active=false.
        $validCriterionCodes = array_column($criteriaData, 'code');
        $validCareerCodes = array_column($careersData, 'code');

        CareerProfile::query()
            ->whereHas('criterion', fn ($q) => $q->whereNotIn('code', $validCriterionCodes))
            ->orWhereHas('career', fn ($q) => $q->whereNotIn('code', $validCareerCodes))
            ->delete();

        Criterion::query()->whereNotIn('code', $validCriterionCodes)->delete();
        Career::query()->whereNotIn('code', $validCareerCodes)->delete();

        foreach ($criteriaData as $data) {
            // Jangan timpa bobot (weight) yang sudah diubah admin; hanya sinkronkan
            // metadata. Bobot default hanya berlaku saat kriteria baru dibuat.
            $attributes = $data;
            unset($attributes['weight']);

            Criterion::query()->updateOrCreate(
                ['code' => $data['code']],
                $attributes
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
        // Invalidate reference cache setelah data referensi berubah.
        if (app()->bound(CriterionRepository::class)) {
            app(CriterionRepository::class)->flushCache();
            app(CareerRepository::class)->flushCache();
            app(CareerProfileRepository::class)->flushCache();
        }

    }
}
