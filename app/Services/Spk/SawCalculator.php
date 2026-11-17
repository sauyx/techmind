<?php

namespace App\Services\Spk;

use App\Exceptions\InvalidRatingsException;

class SawCalculator
{
    private const TIE_EPSILON = 1e-9;

    /**
     * @param array<int, array{code: string, multiplier: float|int}> $criteria
     * @param array<int, array{code: string, sort_order: int, scores: array<string, int>}> $careers
     * @param array<string, int|float|string> $ratings
     * @return array<string, mixed>
     */
    public function calculate(array $criteria, array $careers, array $ratings): array
    {
        $activeCriteria = array_values($criteria);
        $activeCareers = array_values($careers);

        $criterionCodes = array_column($activeCriteria, 'code');

        // Validasi multiplier admin.
        foreach ($activeCriteria as $criterion) {
            $multiplier = (float) $criterion['multiplier'];
            if ($multiplier < 0.5 || $multiplier > 2.0) {
                throw new InvalidRatingsException('Pengali admin harus berada pada rentang 0.50-2.00.');
            }
        }

        // Validasi rating: semua kriteria aktif wajib ada, tidak ada kode asing.
        foreach ($criterionCodes as $code) {
            if (! array_key_exists($code, $ratings)) {
                throw new InvalidRatingsException("Rating untuk kriteria {$code} wajib diisi.");
            }
        }

        foreach (array_keys($ratings) as $code) {
            if (! in_array($code, $criterionCodes, true)) {
                throw new InvalidRatingsException("Kode kriteria tidak dikenal: {$code}.");
            }
        }

        foreach ($ratings as $code => $rating) {
            if (is_bool($rating) || ! is_numeric($rating)) {
                throw new InvalidRatingsException("Rating {$code} harus berupa bilangan.");
            }

            $numeric = (float) $rating;
            if (floor($numeric) !== $numeric) {
                throw new InvalidRatingsException("Rating {$code} harus bilangan bulat.");
            }

            if ($numeric < 1 || $numeric > 5) {
                throw new InvalidRatingsException("Rating {$code} harus berada pada rentang 1-5.");
            }
        }

        if (count($activeCareers) === 0) {
            throw new InvalidRatingsException('Tidak ada karier aktif untuk diranking.');
        }

        // Max per kriteria di antara karier aktif.
        $maxByCriterion = [];
        foreach ($criterionCodes as $code) {
            $values = array_map(
                fn (array $career): int => (int) ($career['scores'][$code] ?? 0),
                $activeCareers
            );
            $max = max($values);
            $maxByCriterion[$code] = $max > 0 ? $max : 1;
        }

        // Bobot ternormalisasi dengan pengali admin.
        $weightedRatings = [];
        foreach ($criterionCodes as $code) {
            $weightedRatings[$code] = ((float) $ratings[$code]) * (float) $activeCriteria[array_search($code, $criterionCodes, true)]['multiplier'];
        }
        $denominator = array_sum($weightedRatings);
        if ($denominator <= 0) {
            throw new InvalidRatingsException('Total bobot tidak boleh nol.');
        }

        $weights = [];
        foreach ($criterionCodes as $code) {
            $weights[$code] = $weightedRatings[$code] / $denominator;
        }

        // Normalisasi matriks dan kontribusi.
        $normalized = [];
        $contributions = [];
        $scores = [];

        foreach ($activeCareers as $career) {
            $careerCode = (string) $career['code'];
            $normalized[$careerCode] = [];
            $contributions[$careerCode] = [];
            $total = 0.0;

            foreach ($criterionCodes as $code) {
                $raw = (int) ($career['scores'][$code] ?? 0);
                $norm = $raw / $maxByCriterion[$code];
                $contribution = $weights[$code] * $norm;

                $normalized[$careerCode][$code] = $norm;
                $contributions[$careerCode][$code] = $contribution;
                $total += $contribution;
            }

            $scores[$careerCode] = $total;
        }

        // Ranking dengan tie-break deterministik.
        $rankingItems = [];
        foreach ($activeCareers as $career) {
            $careerCode = (string) $career['code'];
            $rankingItems[] = [
                'career_code' => $careerCode,
                'sort_order' => (int) ($career['sort_order'] ?? 0),
                'score' => $scores[$careerCode],
                'display_score' => round($scores[$careerCode] * 100, 1),
                'contributions' => $contributions[$careerCode],
            ];
        }

        usort($rankingItems, function (array $a, array $b): int {
            if (abs($a['score'] - $b['score']) >= self::TIE_EPSILON) {
                return $a['score'] < $b['score'] ? 1 : -1;
            }

            return $a['sort_order'] <=> $b['sort_order'];
        });

        foreach ($rankingItems as $index => $item) {
            $rankingItems[$index]['rank'] = $index + 1;
        }

        return [
            'criteria' => array_map(
                fn (array $criterion, string $code): array => [
                    'code' => $code,
                    'multiplier' => (float) $criterion['multiplier'],
                    'weight' => $weights[$code],
                ],
                $activeCriteria,
                $criterionCodes
            ),
            'weights' => $weights,
            'normalized_matrix' => $normalized,
            'contributions' => $contributions,
            'rankings' => $rankingItems,
        ];
    }
}
