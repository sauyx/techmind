<?php

namespace App\Services\Ai;

use App\Contracts\AiExplainerInterface;
use App\Data\AiExplanationResult;
use App\Models\TestSession;

final class TemplateAiExplainer implements AiExplainerInterface
{
    public function explain(TestSession $session): AiExplanationResult
    {
        $payloadBuilder = new \App\Services\Ai\ExplanationPayloadBuilder();
        $payload = $payloadBuilder->build($session);
        $topResults = $session->results->take(3);
        $ratings = [];
        $criterionNames = [];

        foreach ($session->answers as $answer) {
            $code = (string) $answer->criterion_code;
            $ratings[$code] = (int) $answer->rating;
            $criterionNames[$code] = (string) $answer->criterion_name;
        }

        $paragraphs = [];

        if ($topResults->isEmpty()) {
            return new AiExplanationResult(
                'Hasil analisis belum tersedia karena sesi tes ini belum memiliki data peringkat yang tersimpan.',
                'template',
                null,
                false,
                0,
            );
        }

        $top = $topResults->first();
        $topScore = (float) ($top?->saw_score ?? 0.0);
        $tiedTop = $session->results
            ->filter(fn ($result) => abs((float) $result->saw_score - $topScore) < 1e-9)
            ->values();
        $isTiedTop = $tiedTop->count() > 1;
        $tiedTopNames = $tiedTop->pluck('career_name')->all();

        $strengths = array_column($top->gap_summary['strengths'] ?? [], 'criterion_code');
        $needs = array_column($top->gap_summary['needs_strengthening'] ?? [], 'criterion_code');
        $underused = array_column($top->gap_summary['underused'] ?? [], 'criterion_code');

        $strengthNames = array_map(fn ($code) => $criterionNames[$code] ?? $code, $strengths);
        $needNames = array_map(fn ($code) => $criterionNames[$code] ?? $code, $needs);
        $underusedNames = array_map(fn ($code) => $criterionNames[$code] ?? $code, $underused);

        if ($isTiedTop) {
            $opening = 'Beberapa karier memiliki tingkat kecocokan yang sama, yaitu '.implode(', ', $tiedTopNames).'. ';
            $opening .= 'Jawaban Anda cenderung merata, sehingga sistem belum bisa membedakan dengan jelas. ';
        } else {
            $opening = $top->career_name.' memiliki skor kecocokan tertinggi, yaitu '.$top->match_percentage.'%. ';
        }
        if ($strengthNames !== []) {
            $opening .= 'Kekuatan utama Anda terlihat pada '.implode(', ', $strengthNames).', yang berarti pola jawaban Anda cukup selaras dengan profil karier tersebut. ';
        } else {
            $opening .= 'Belum ada kekuatan jawaban Anda yang menonjol pada kriteria utama karier ini, sehingga masih ada ruang untuk menjelajahi minat lebih dalam. ';
        }

        $development = 'Selanjutnya, perhatikan area yang perlu diperkuat. ';
        if ($needNames !== []) {
            $development .= 'Berdasarkan snapshot jawaban, '.implode(', ', $needNames).' masih berada di bawah kebutuhan profil ideal karier ini; Anda dapat memulainya dengan kursus dasar atau proyek kecil yang terukur. ';
        } else {
            $development .= 'Tidak ada gap besar yang terdeteksi antara jawaban Anda dan profil ideal karier ini, jadi fokuslah pada konsistensi belajar dan portofolio. ';
        }

        if ($underusedNames !== []) {
            $development .= 'Perlu diingat, potensi '.implode(', ', $underusedNames).' Anda mungkin belum banyak digunakan pada jalur ini, sehingga cocok dipertahankan sebagai keunggulan tambahan. ';
        }

        $alternatives = $topResults->skip(1)->map(fn ($item) => $item->career_name.' ('.$item->match_percentage.'%)')->implode(' dan ');
        if ($isTiedTop) {
            $closing = 'Karier-karier yang setara di atas layak Anda pertimbangkan bersama. Kesimpulan ini bersifat relatif; untuk hasil yang lebih tajam, ulangi tes dan berikan penilaian yang lebih tegas pada hal yang benar-benar Anda minati atau tidak minati.';
        } else {
            $closing = 'Peringkat kedua dan ketiga yang juga layak Anda pertimbangkan adalah '.$alternatives.'. Kesimpulan ini bersifat relatif dan sebaiknya digunakan sebagai titik awal eksplorasi, bukan keputusan akhir.';
        }

        $isTiedTop = (bool) ($payload['is_tied_top'] ?? false);
        $tiedNames = (array) ($payload['tied_careers'] ?? []);
        $topCareers = (array) ($payload['top_careers'] ?? []);
        $topName = (string) ($topCareers[0]['name'] ?? ($topResults->first()?->career_name ?? ''));
        $topPct = (string) ($topCareers[0]['match_percentage'] ?? ($topResults->first()?->match_percentage ?? ''));
        $strengths = (array) ($payload['kekuatan_pengguna'] ?? []);
        $development = (array) ($payload['area_pengembangan'] ?? []);

        $variantIndex = hexdec(substr(hash('sha256', (string) $session->token), 0, 8)) % 2;
        $openingVariants = [
            'memiliki skor kecocokan tertinggi',
            'menempati peringkat teratas dengan skor kecocokan',
        ];
        $ringkasan = $isTiedTop
            ? 'Beberapa karier memiliki tingkat kecocokan yang sama, yaitu '.implode(', ', $tiedNames).'. Jawaban Anda cenderung merata, sehingga sistem belum bisa membedakan dengan jelas.'
            : $topName.' '.$openingVariants[$variantIndex].' yaitu '.$topPct.'%. Hasil ini merupakan rekomendasi awal untuk eksplorasi, bukan keputusan akhir.';

        $alasan = [];
        if ($strengths !== []) {
            $strengthText = 'Kekuatan utama Anda terlihat pada '.implode(', ', array_slice($strengths, 0, 3)).', yang selaras dengan profil karier teratas.';
            $strengthTextVariants = [
                $strengthText,
                'Profil Anda kuat pada '.implode(', ', array_slice($strengths, 0, 3)).', sehingga berkaitan erat dengan kebutuhan karier teratas.',
            ];
            $alasan[] = $strengthTextVariants[$variantIndex];
        } else {
            $alasan[] = 'Belum ada kekuatan jawaban yang menonjol pada kriteria utama karier ini.';
        }

        $area = $development !== [] ? array_slice($development, 0, 2) : [];

        $langkah = [];
        $roadmap = $topCareers[0]['roadmap_awal'] ?? [];
        $skills = $topCareers[0]['skills'] ?? [];
        if ($skills !== []) {
            $langkah[] = 'Pelajari dasar '.implode(', ', array_slice($skills, 0, 3)).' melalui kursus singkat.';
        }
        if ($roadmap !== []) {
            $langkah[] = 'Ikuti langkah awal: '.implode(', ', array_slice($roadmap, 0, 2)).'.';
        }
        if ($langkah === []) {
            $langkah[] = 'Mulai dengan proyek kecil untuk menguji minat Anda.';
        }

        $alternatif = $topResults->skip(1)->count() > 0
            ? 'Peringkat berikutnya yang layak dipertimbangkan adalah '.$topResults->skip(1)->map(fn ($item) => $item->career_name.' ('.$item->match_percentage.'%)')->implode(' dan ').'.'
            : 'Kesimpulan ini bersifat relatif.';

        if ($isTiedTop) {
            $alternatif = 'Karier-karier yang setara di atas layak Anda pertimbangkan bersama. Untuk hasil yang lebih tajam, ulangi tes dengan penilaian yang lebih tegas.';
        }

        $json = [
            'ringkasan' => $ringkasan,
            'alasan' => $alasan,
            'area_pengembangan' => $area,
            'langkah_awal' => $langkah,
            'alternatif' => $alternatif,
        ];

        return new AiExplanationResult(
            json_encode($json, JSON_UNESCAPED_UNICODE),
            'template',
            null,
            false,
            0,
            'success',
            'json',
            1,
            null,
        );
    }
}
