<?php

namespace App\Services\Ai;

use App\Models\Career;
use App\Models\Criterion;
use App\Models\TestSession;

class ExplanationPayloadBuilder
{
    public function build(TestSession $session): array
    {
        $topResults = $session->results->take(3)->values();
        $topResult = $topResults->first();
        $topScore = (float) ($topResult?->saw_score ?? 0.0);

        $tiedResults = $session->results
            ->filter(fn ($result) => abs((float) $result->saw_score - $topScore) < 1e-9)
            ->values();

        $isTiedTop = $tiedResults->count() > 1;

        $tiedCareers = $isTiedTop
            ? $tiedResults->pluck('career_name')->values()->all()
            : [];

        $ratings = [];
        foreach ($session->answers as $answer) {
            $ratings[(string) $answer->criterion_name] = (int) $answer->rating;
        }

        $strengths = [];
        if ($topResult) {
            foreach ($topResult->gap_summary['strengths'] ?? [] as $item) {
                $strengths[] = (string) ($item['criterion_code'] ?? '');
            }
        }

        // map strength codes to names using answers
        $codeToName = [];
        foreach ($session->answers as $answer) {
            $codeToName[(string) $answer->criterion_code] = (string) $answer->criterion_name;
        }
        $strengthNames = array_values(array_unique(array_map(
            fn (string $code) => $codeToName[$code] ?? $code,
            $strengths
        )));

        $careerModels = Career::query()
            ->with('careerProfiles.criterion')
            ->whereIn('code', $topResults->pluck('career_code')->all())
            ->get()
            ->keyBy('code');

        $development = [];
        if ($topResult) {
            foreach ($topResult->gap_summary['needs_strengthening'] ?? [] as $item) {
                $code = (string) ($item['criterion_code'] ?? '');
                $profileScore = 0;
                if ($topResult) {
                    $careerModel = $careerModels->get((string) $topResult->career_code);
                    if ($careerModel) {
                        foreach ($careerModel->careerProfiles as $profile) {
                            if ($profile->criterion && (string) $profile->criterion->code === $code) {
                                $profileScore = (int) $profile->score;
                            }
                        }
                    }
                }
                $hint = '';
                // fetch hint from criteria table
                $crit = Criterion::query()->where('code', $code)->first();
                if ($crit) {
                    $hint = (string) ($crit->development_hint ?? '');
                }

                $development[] = [
                    'kriteria' => $codeToName[$code] ?? $code,
                    'rating_anda' => (int) ($item['rating'] ?? 0),
                    'profil_karier' => $profileScore ?: (int) ($item['profile'] ?? 0), // profil dari career_profiles (fallback gap_summary)
                    'hint' => $hint,
                ];
            }
        }

        $topCareers = $topResults->map(function ($result) {
            $career = null;

            // find career detail from session? fallback minimal
            return [
                'rank' => (int) $result->rank,
                'name' => (string) $result->career_name,
                'match_percentage' => (float) $result->match_percentage,
                'summary' => '',
                'skills' => [],
                'roadmap_awal' => [],
            ];
        })->all();

        foreach ($topCareers as $index => $item) {
            $code = (string) $topResults[$index]->career_code;
            $model = $careerModels->get($code);
            if ($model) {
                $topCareers[$index]['summary'] = (string) ($model->summary ?? '');
                $topCareers[$index]['skills'] = array_slice($model->skills ?? [], 0, 6);
                $topCareers[$index]['roadmap_awal'] = array_slice($model->roadmap ?? [], 0, 3);
            }
        }

        $contributions = (array) ($topResult?->contribution_breakdown ?? []);
        arsort($contributions);
        $topContributionCodes = array_slice(array_keys($contributions), 0, 3);

        $topCareerModel = null;
        if ($topResult) {
            $topCareerModel = $careerModels->get((string) $topResult->career_code);
        }

        $kontribusiTeratas = [];
        foreach ($topContributionCodes as $code) {
            $profileScore = 0;
            if ($topCareerModel) {
                foreach ($topCareerModel->careerProfiles as $profile) {
                    if ($profile->criterion && (string) $profile->criterion->code === (string) $code) {
                        $profileScore = (int) $profile->score;
                    }
                }
            }
            $kontribusiTeratas[] = [
                'kriteria' => $codeToName[$code] ?? $code,
                'rating_anda' => (int) ($session->answers->firstWhere('criterion_code', $code)?->rating ?? 0),
                'profil_karier' => $profileScore,
            ];
        }

        return [
            'is_tied_top' => $isTiedTop,
            'tied_careers' => $tiedCareers,
            'top_careers' => $topCareers,
            'kekuatan_pengguna' => $strengthNames,
            'area_pengembangan' => $development,
            'kontribusi_teratas' => $kontribusiTeratas,
            'jawaban' => $ratings,
        ];
    }
}
