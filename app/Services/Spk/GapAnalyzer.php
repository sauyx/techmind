<?php

namespace App\Services\Spk;

class GapAnalyzer
{
    public function analyze(array $rankings, array $ratings, array $careerProfiles): array
    {
        $topCareers = array_slice($rankings, 0, 3);
        $result = [];

        foreach ($topCareers as $ranking) {
            $careerCode = (string) $ranking['career_code'];
            $profile = $careerProfiles[$careerCode] ?? [];

            $strengths = [];
            $needs = [];
            $underused = [];

            foreach ($profile as $criterionCode => $profileScore) {
                $rating = (int) ($ratings[$criterionCode] ?? 0);
                $profileScore = (int) $profileScore;

                if ($rating >= 4 && $profileScore >= 4) {
                    $strengths[] = [
                        'criterion_code' => $criterionCode,
                        'rating' => $rating,
                        'profile' => $profileScore,
                    ];
                }

                if ($profileScore - $rating >= 2) {
                    $needs[] = [
                        'criterion_code' => $criterionCode,
                        'rating' => $rating,
                        'profile' => $profileScore,
                    ];
                }

                if ($rating - $profileScore >= 2) {
                    $underused[] = [
                        'criterion_code' => $criterionCode,
                        'rating' => $rating,
                        'profile' => $profileScore,
                    ];
                }
            }

            $result[$careerCode] = [
                'strengths' => $strengths,
                'needs_strengthening' => $needs,
                'underused' => $underused,
            ];
        }

        return $result;
    }
}
