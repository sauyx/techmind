<?php

namespace App\Services\Admin;

use App\Contracts\CareerProfileRepository;
use App\Contracts\CareerRepository;
use App\Contracts\CriterionRepository;
use App\Models\Career;
use App\Models\Criterion;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CareerManagementService
{
    private const STANDARD_TOTAL_SCORE = 37;

    public function __construct(
        private CareerRepository $careers,
        private CareerProfileRepository $profiles,
        private CriterionRepository $criteria,
    ) {}

    public function list(): array
    {
        return $this->careers->allWithProfiles()
            ->map(fn (Career $career) => $this->mapCareer($career))
            ->values()
            ->all();
    }

    public function criteria(): array
    {
        return $this->criteria->activeOrdered()
            ->map(fn (Criterion $criterion) => [
                'id' => (int) $criterion->id,
                'code' => (string) $criterion->code,
                'name' => (string) $criterion->name,
                'step_group' => (int) $criterion->step_group,
                'display_order' => (int) $criterion->display_order,
            ])
            ->values()
            ->all();
    }

    public function find(int $id): ?array
    {
        $career = $this->careers->findWithProfiles($id);

        return $career ? $this->mapCareer($career, true) : null;
    }

    public function create(array $data): Career
    {
        return $this->save(null, $data);
    }

    public function update(Career $career, array $data): Career
    {
        return $this->save($career, $data);
    }

    public function standardTotalScore(): int
    {
        return self::STANDARD_TOTAL_SCORE;
    }

    public function maxTotalScore(): int
    {
        return $this->criteria->activeOrdered()->count() * 5;
    }

    private function save(?Career $career, array $data): Career
    {
        return DB::transaction(function () use ($career, $data): Career {
            $payload = [
                'code' => (string) $data['code'],
                'name' => (string) $data['name'],
                'summary' => $data['summary'] ?? null,
                'description' => $data['description'] ?? null,
                'skills' => $this->normalizeList($data['skills'] ?? []),
                'tools' => $this->normalizeList($data['tools'] ?? []),
                'roadmap' => $this->normalizeList($data['roadmap'] ?? []),
                'sort_order' => (int) ($data['sort_order'] ?? 0),
                'is_active' => (bool) ($data['is_active'] ?? true),
                'is_validated' => (bool) ($data['is_validated'] ?? false),
                'source_note' => $data['source_note'] ?? null,
            ];

            $career = $career
                ? $this->careers->update($career, $payload)
                : $this->careers->create($payload);

            $scores = [];
            foreach ($this->criteria->activeOrdered() as $criterion) {
                $id = (int) $criterion->id;
                $profiles = $data['profiles'] ?? [];

                if (! array_key_exists($id, $profiles) && ! array_key_exists((string) $id, $profiles)) {
                    throw ValidationException::withMessages([
                        'profiles' => 'Semua kriteria aktif wajib memiliki skor profil.',
                    ]);
                }

                $scores[$id] = (int) ($profiles[$id] ?? $profiles[(string) $id]);
            }

            $this->profiles->replaceForCareer((int) $career->id, $scores);

            $this->careers->flushCache();
            $this->profiles->flushCache();

            return $career;
        });
    }

    private function mapCareer(Career $career, bool $withProfiles = true): array
    {
        $profileMap = [];
        $total = 0;

        if ($withProfiles) {
            foreach ($career->careerProfiles as $profile) {
                $profileMap[(string) $profile->criterion_id] = (int) $profile->score;
                $total += (int) $profile->score;
            }
        }

        return [
            'id' => (int) $career->id,
            'code' => (string) $career->code,
            'name' => (string) $career->name,
            'summary' => $career->summary,
            'description' => $career->description,
            'skills' => $career->skills ?? [],
            'tools' => $career->tools ?? [],
            'roadmap' => $career->roadmap ?? [],
            'sort_order' => (int) $career->sort_order,
            'is_active' => (bool) $career->is_active,
            'is_validated' => (bool) $career->is_validated,
            'source_note' => $career->source_note,
            'profiles' => $profileMap,
            'total_score' => $total,
        ];
    }

    private function normalizeList(array $items): array
    {
        return array_values(array_filter(array_map(
            fn ($item) => trim((string) $item),
            $items
        ), fn ($item) => $item !== ''));
    }
}
