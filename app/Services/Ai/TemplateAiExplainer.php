<?php

namespace App\Services\Ai;

use App\Contracts\AiExplainerInterface;
use App\Data\AiExplanationResult;
use App\Models\TestSession;

final class TemplateAiExplainer implements AiExplainerInterface
{
    public function explain(TestSession $session): AiExplanationResult
    {
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

        $paragraphs[] = trim($opening);
        $paragraphs[] = trim($development);
        $paragraphs[] = trim($closing);

        return new AiExplanationResult(
            implode("\n\n", $paragraphs),
            'template',
            null,
            false,
            0,
        );
    }
}
