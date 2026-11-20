<?php

namespace App\Services\Ai;

use App\Contracts\AiExplainerInterface;
use App\Contracts\CriterionRepository;
use App\Data\AiExplanationResult;
use App\Models\TestSession;

final class TemplateAiExplainer implements AiExplainerInterface
{
    public function explain(TestSession $session): AiExplanationResult
    {
        $payloadBuilder = new ExplanationPayloadBuilder();
        $payload = $payloadBuilder->build($session);

        $topResults = $session->results->take(3)->values();
        if ($topResults->isEmpty()) {
            return new AiExplanationResult(
                'Hasil analisis belum tersedia karena sesi tes ini belum memiliki data peringkat yang tersimpan.',
                'template',
                null,
                false,
                0,
            );
        }

        $topCareers = (array) ($payload['top_careers'] ?? []);
        $topCareer = $topCareers[0] ?? [];
        $topName = (string) ($topCareer['name'] ?? $topResults->first()?->career_name ?? '');
        $topPct = (string) ($topCareer['match_percentage'] ?? $topResults->first()?->match_percentage ?? '');
        $summary = (string) ($topCareer['summary'] ?? '');
        $skills = (array) ($topCareer['skills'] ?? []);
        $roadmap = (array) ($topCareer['roadmap_awal'] ?? []);

        $isTiedTop = (bool) ($payload['is_tied_top'] ?? false);
        $tiedNames = (array) ($payload['tied_careers'] ?? []);
        $allResultsCount = $session->results->count();
        $tiedCount = count($tiedNames);
        $seriSemua = $isTiedTop && $tiedCount > 0 && $tiedCount === $allResultsCount;

        $kekuatan = (array) ($payload['kekuatan_pengguna'] ?? []);
        $area = (array) ($payload['area_pengembangan'] ?? []);
        $kontribusi = (array) ($payload['kontribusi_teratas'] ?? []);
        $jawaban = (array) ($payload['jawaban'] ?? []);

        $hash = hexdec(substr(hash('sha256', (string) $session->token), 0, 8));
        $variant = $hash % 3;

        $criteriaRepo = app(CriterionRepository::class);
        $criteriaByCode = [];
        foreach ($criteriaRepo->activeOrdered() as $criterion) {
            $criteriaByCode[(string) $criterion->code] = $criterion;
        }
        $nameToCode = [];
        foreach ($session->answers as $answer) {
            $nameToCode[(string) $answer->criterion_name] = (string) $answer->criterion_code;
        }

        if ($seriSemua) {
            $ringkasan = 'Semua karier memiliki tingkat kecocokan yang sama karena jawaban Anda bernilai merata, sehingga sistem belum bisa menentukan arah yang paling sesuai.';
            $alasan = [
                'Jawaban Anda cenderung merata di semua kriteria, sehingga tidak ada kriteria yang menonjol.',
                'Tanpa perbedaan yang jelas, sistem SAW memberi skor yang sama untuk setiap karier.',
            ];
            $areaOut = [];
            $langkah = [
                'Ulangi tes dan beri nilai 4–5 hanya pada aktivitas yang benar-benar Anda nikmati.',
                'Beri nilai 1–2 pada aktivitas yang kurang Anda minati, agar perbedaannya terlihat.',
                'Baca deskripsi setiap karier di halaman ini dan tandai yang paling membuat Anda penasaran.',
            ];
            $alternatif = 'Karier-karier yang setara di atas layak Anda pertimbangkan bersama. Untuk hasil yang lebih tajam, ulangi tes dengan penilaian yang lebih tegas.';
        } elseif ($isTiedTop) {
            $ringkasan = 'Beberapa karier memiliki tingkat kecocokan yang sama, yaitu '.implode(', ', $tiedNames).', dengan tingkat kecocokan '.$topPct.'%. Hasil ini adalah rekomendasi awal untuk eksplorasi, bukan keputusan akhir.';
            $alasan = $kekuatan !== []
                ? array_map(fn ($name) => 'Anda menilai diri tinggi pada '.$name.' ('.($jawaban[$name] ?? '').'/5), dan kemampuan ini mendukung karier-karier yang setara di atas.', array_slice($kekuatan, 0, 3))
                : [
                    'Jawaban Anda cenderung merata pada beberapa kriteria utama, sehingga beberapa karier memperoleh skor yang sama.',
                    'Perbedaan kecil antar kriteria membuat sistem belum bisa menentukan satu karier unggulan.',
                ];
            if (count($alasan) < 2) {
                $alasan[] = 'Perbedaan kecil antar kriteria membuat sistem belum bisa menentukan satu karier unggulan.';
            }
            $areaOut = [];
            $langkah = [];
            $tiedCareerModels = [];
            foreach ($topCareers as $tc) {
                if (in_array($tc['name'], $tiedNames, true)) {
                    $tiedCareerModels[] = $tc;
                }
            }
            foreach (array_slice($tiedCareerModels, 0, 3) as $tc) {
                $firstStep = $tc['roadmap_awal'][0] ?? '';
                if ($firstStep !== '') {
                    $langkah[] = 'Untuk '.$tc['name'].', mulai dari '.$firstStep.'.';
                }
            }
            if (count($langkah) < 2) {
                $langkah[] = 'Bandingkan langkah pertama setiap karier yang setara dan pilih yang paling menarik.';
            }
            $langkah[] = 'Pilih karier yang paling menarik bagi Anda dan dalami langkah pertamanya.';
            $alternatif = 'Karier-karier yang setara di atas layak Anda pertimbangkan bersama. Untuk hasil yang lebih tajam, ulangi tes dengan penilaian yang lebih tegas.';
        } else {
            $ringkasan = $topName.' menjadi karier yang paling sesuai dengan profil Anda, dengan tingkat kecocokan '.$topPct.'%.';
            if ($summary !== '') {
                $ringkasan .= ' '.$summary;
            }
            $ringkasan .= ' Hasil ini adalah rekomendasi awal untuk eksplorasi, bukan keputusan akhir.';

            $alasan = [];
            foreach (array_slice($kekuatan, 0, 3) as $name) {
                $rating = $jawaban[$name] ?? '';
                $alasan[] = 'Anda menilai diri tinggi pada '.$name.' ('.$rating.'/5), dan kemampuan ini termasuk inti pekerjaan seorang '.$topName.'.';
            }
            if (count($alasan) < 2) {
                foreach (array_slice($kontribusi, 0, 3) as $item) {
                    if (count($alasan) >= 3) {
                        break;
                    }
                    $kriteria = (string) ($item['kriteria'] ?? '');
                    $ratingAnda = (string) ($item['rating_anda'] ?? '');
                    $profil = (string) ($item['profil_karier'] ?? '');
                    if ($kriteria !== '') {
                        $alasan[] = 'Kontribusi terbesar pada skor karier ini berasal dari '.$kriteria.' (rating Anda '.$ratingAnda.'/5, profil karier '.$profil.'/5).';
                    }
                }
            }
            if (count($alasan) < 2) {
                $alasan[] = 'Profil jawaban Anda cukup selaras dengan kebutuhan karier '.$topName.'.';
            }

            $areaOut = [];
            foreach (array_slice($area, 0, 2) as $name) {
                $code = $nameToCode[$name] ?? '';
                $criterion = $criteriaByCode[$code] ?? null;
                $context = $criterion && ! empty($criterion->description)
                    ? (string) $criterion->description
                    : 'memperkuat area ini';
                $areaOut[] = $name.' masih bisa ditingkatkan; seorang '.$topName.' perlu '.$context.'.';
            }

            $patterns = [
                fn (string $item) => 'Mulai dengan memahami '.$item.': konsep dasar dan contoh penerapannya.',
                fn (string $item) => 'Luangkan 30 menit sehari untuk berlatih '.$item.' secara bertahap.',
                fn (string $item) => 'Buat catatan singkat dari materi '.$item.' dan ulangi tiap minggu.',
            ];
            $langkah = [];
            $items = $roadmap !== [] ? $roadmap : $skills;
            foreach (array_slice($items, 0, 3) as $index => $item) {
                $pattern = $patterns[($variant + $index) % count($patterns)];
                $langkah[] = $pattern((string) $item);
            }
            if (count($langkah) < 2) {
                $langkah[] = 'Cari satu sumber belajar dasar dan pelajari secara konsisten setiap minggu.';
            }

            $alternatif = $topResults->skip(1)->count() > 0
                ? 'Peringkat berikutnya yang layak dipertimbangkan adalah '.$topResults->skip(1)->map(fn ($item) => $item->career_name.' ('.$item->match_percentage.'%)')->implode(' dan ').'.'
                : 'Kesimpulan ini bersifat relatif.';
        }

        $json = [
            'ringkasan' => $ringkasan,
            'alasan' => array_slice($alasan, 0, 3),
            'area_pengembangan' => array_slice($areaOut, 0, 2),
            'langkah_awal' => array_slice($langkah, 0, 3),
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
