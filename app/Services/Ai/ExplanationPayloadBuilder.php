<?php

namespace App\Services\Ai;

use App\Models\TestSession;

class ExplanationPayloadBuilder
{
    public function build(TestSession $session): array
    {
        $topResults = $session->results->take(3)->values();
        $topResult = $topResults->first();
        $topScore = (float) ($topResult?->saw_score ?? 0.0);

        $tiedCareers = $session->results
            ->filter(fn ($result) => abs((float) $result->saw_score - $topScore) < 1e-9)
            ->pluck('career_name')
            ->values()
            ->all();

        $isTiedTop = count($tiedCareers) > 1;

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

        $development = [];
        if ($topResult) {
            foreach ($topResult->gap_summary['needs_strengthening'] ?? [] as $item) {
                $code = (string) ($item['criterion_code'] ?? '');
                $development[] = $codeToName[$code] ?? $code;
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

        // enrich from careers table if possible
        $careerModels = \App\Models\Career::query()
            ->whereIn('code', $topResults->pluck('career_code')->all())
            ->get()
            ->keyBy('code');

        foreach ($topCareers as $index => $item) {
            $code = (string) $topResults[$index]->career_code;
            $model = $careerModels->get($code);
            if ($model) {
                $topCareers[$index]['summary'] = (string) ($model->summary ?? '');
                $topCareers[$index]['skills'] = array_slice($model->skills ?? [], 0, 6);
                $topCareers[$index]['roadmap_awal'] = array_slice($model->roadmap ?? [], 0, 3);
            }
        }

        return [
            'is_tied_top' => $isTiedTop,
            'tied_careers' => $tiedCareers,
            'top_careers' => $topCareers,
            'kekuatan_pengguna' => $strengthNames,
            'area_pengembangan' => $development,
            'jawaban' => $ratings,
        ];
    }
}
