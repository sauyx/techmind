<?php

namespace App\Services\Ai;

use App\Contracts\AiExplainerInterface;
use App\Contracts\CriterionRepository;
use App\Data\AiExplanationResult;
use App\Models\TestSession;
use App\Support\IndonesianText;

final class TemplateAiExplainer implements AiExplainerInterface
{
    public function explain(TestSession $session): AiExplanationResult
    {
        $payloadBuilder = new ExplanationPayloadBuilder;
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
        $topPct = (float) ($topCareer['match_percentage'] ?? $topResults->first()?->match_percentage ?? 0);
        $summary = trim((string) ($topCareer['summary'] ?? ''));
        if ($summary !== '' && ! str_ends_with($summary, '.')) {
            $summary .= '.';
        }
        $roadmap = (array) ($topCareer['roadmap_awal'] ?? []);
        $skills = (array) ($topCareer['skills'] ?? []);

        $isTiedTop = (bool) ($payload['is_tied_top'] ?? false);
        $tiedNames = (array) ($payload['tied_careers'] ?? []);
        $allResultsCount = $session->results->count();
        $seriSemua = $isTiedTop && count($tiedNames) > 0 && count($tiedNames) === $allResultsCount;

        $kekuatan = (array) ($payload['kekuatan_pengguna'] ?? []);
        $area = (array) ($payload['area_pengembangan'] ?? []);
        $kontribusi = (array) ($payload['kontribusi_teratas'] ?? []);
        $jawaban = (array) ($payload['jawaban'] ?? []);

        $criteriaRepo = app(CriterionRepository::class);
        $criteriaByCode = [];
        foreach ($criteriaRepo->activeOrdered() as $criterion) {
            $criteriaByCode[(string) $criterion->code] = $criterion;
        }
        $nameToCode = [];
        foreach ($session->answers as $answer) {
            $nameToCode[(string) $answer->criterion_name] = (string) $answer->criterion_code;
        }

        $percent = IndonesianText::percent($topPct);

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
            $ringkasan = 'Beberapa karier memiliki tingkat kecocokan yang sama, yaitu '.IndonesianText::joinList($tiedNames).', dengan tingkat kecocokan '.$percent.'. Hasil ini adalah rekomendasi awal untuk eksplorasi, bukan keputusan akhir.';
            // common strengths across all tied careers
            $tiedResultModels = $topResults->filter(fn ($r) => in_array($r->career_name, $tiedNames, true));
            $commonStrengthCodes = null;
            foreach ($tiedResultModels as $tr) {
                $codes = array_column($tr->gap_summary['strengths'] ?? [], 'criterion_code');
                if ($commonStrengthCodes === null) {
                    $commonStrengthCodes = $codes;
                } else {
                    $commonStrengthCodes = array_values(array_intersect($commonStrengthCodes, $codes));
                }
            }
            $commonStrengthNames = array_map(fn ($code) => $codeToName[$code] ?? $code, $commonStrengthCodes ?? []);

            $alasan = [];
            if ($commonStrengthNames !== []) {
                foreach (array_slice($commonStrengthNames, 0, 3) as $name) {
                    $alasan[] = 'Anda menilai diri tinggi pada '.$name.' ('.($jawaban[$name] ?? '').'/5), dan kemampuan ini mendukung karier-karier yang setara di atas.';
                }
            } else {
                foreach ($tiedResultModels as $tr) {
                    foreach ($tr->gap_summary['strengths'] ?? [] as $st) {
                        $code = (string) ($st['criterion_code'] ?? '');
                        $name = $codeToName[$code] ?? $code;
                        $already = false;
                        foreach ($alasan as $a) {
                            if (str_contains($a, $name)) {
                                $already = true;
                            }
                        }
                        if (! $already) {
                            $alasan[] = $name.' ('.($st['rating'] ?? ($jawaban[$name] ?? '')).'/5) sejalan dengan kebutuhan '.$tr->career_name.'.';
                        }
                    }
                }
            }
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
                    $langkah[] = 'Untuk '.$tc['name'].', mulai dari '.IndonesianText::lcfirstSmart((string) $firstStep).'.';
                }
            }
            if (count($langkah) < 2) {
                $langkah[] = 'Bandingkan langkah pertama setiap karier yang setara dan pilih yang paling menarik.';
            }
            $langkah[] = 'Pilih karier yang paling menarik bagi Anda dan dalami langkah pertamanya.';
            $alternatif = 'Karier-karier yang setara di atas layak Anda pertimbangkan bersama. Untuk hasil yang lebih tajam, ulangi tes dengan penilaian yang lebih tegas.';
        } else {
            $ringkasan = $topName.' menjadi karier yang paling sesuai dengan profil Anda, dengan tingkat kecocokan '.$percent.'.';
            if ($summary !== '') {
                $ringkasan .= ' Secara singkat, peran ini '.IndonesianText::lcfirstSmart($summary);
            }
            $ringkasan .= ' Hasil ini adalah rekomendasi awal untuk eksplorasi, bukan keputusan akhir.';

            $alasan = [];
            $usedCriteria = [];
            // strengths
            foreach (array_slice($kekuatan, 0, 3) as $index => $name) {
                $rating = $jawaban[$name] ?? '';
                // determine profile >=4 (strength by definition) -> inti
                $code = $nameToCode[$name] ?? '';
                $profile = 4;
                // try get actual profile from top career
                $careerModel = null;
                foreach ($topCareers as $tc) {
                    if ($tc['name'] === $topName) {
                        $careerModel = $tc;
                    }
                }
                // profile >=4 assumed for strength
                $frasa = $profile >= 4 ? 'termasuk inti pekerjaan' : 'cukup dibutuhkan';
                if ($index === 0) {
                    $alasan[] = 'Anda menilai diri tinggi pada '.$name.' ('.$rating.'/5), dan kemampuan ini '.$frasa.' seorang '.$topName.'.';
                } elseif ($index === 1) {
                    $alasan[] = 'Nilai '.$rating.'/5 pada '.$name.' menunjukkan minat yang kuat, hal yang sangat dibutuhkan dalam peran ini.';
                } else {
                    $alasan[] = $name.' juga menjadi kekuatan Anda ('.$rating.'/5) dan sejalan dengan tuntutan sehari-hari seorang '.$topName.'.';
                }
                $usedCriteria[] = $name;
            }

            // if fewer than 2, supplement from kontribusi_teratas
            if (count($alasan) < 2) {
                $emptyStrengths = ($kekuatan === []);
                $idx = 0;
                foreach (array_slice($kontribusi, 0, 3) as $item) {
                    if (count($alasan) >= 3) {
                        break;
                    }
                    $kriteria = (string) ($item['kriteria'] ?? '');
                    if ($kriteria === '' || in_array($kriteria, $usedCriteria, true)) {
                        continue;
                    }
                    $ratingAnda = (string) ($item['rating_anda'] ?? '');
                    if ($emptyStrengths) {
                        if (count($alasan) === 0) {
                            $alasan[] = 'Nilai Anda pada '.$kriteria.' ('.$ratingAnda.'/5) sudah memberi dasar yang sejalan dengan kebutuhan utama seorang '.$topName.'.';
                        } elseif (count($alasan) === 1) {
                            $alasan[] = 'Selain itu, '.$kriteria.' ('.$ratingAnda.'/5) juga menjadi bekal awal yang berguna dalam peran ini.';
                        } else {
                            $alasan[] = 'Anda juga memiliki dasar pada '.$kriteria.' ('.$ratingAnda.'/5), yang dapat dikembangkan lebih jauh sebagai '.$topName.'.';
                        }
                    } else {
                        $profil = (int) ($item['profil_karier'] ?? 0);
                        if ($profil >= 4) {
                            $frasa = 'termasuk inti pekerjaan';
                        } elseif ($profil === 3) {
                            $frasa = 'cukup dibutuhkan';
                        } else {
                            $frasa = 'relevan';
                        }
                        if (count($alasan) === 0) {
                            $alasan[] = 'Anda menilai diri tinggi pada '.$kriteria.' ('.$ratingAnda.'/5), dan kemampuan ini '.$frasa.' seorang '.$topName.'.';
                        } elseif (count($alasan) === 1) {
                            $alasan[] = 'Nilai '.$ratingAnda.'/5 pada '.$kriteria.' menunjukkan minat yang kuat, hal yang sangat dibutuhkan dalam peran ini.';
                        } else {
                            $alasan[] = $kriteria.' juga menjadi kekuatan Anda ('.$ratingAnda.'/5) dan sejalan dengan tuntutan sehari-hari seorang '.$topName.'.';
                        }
                    }
                    $usedCriteria[] = $kriteria;
                }
            }
            if (count($alasan) < 2) {
                $alasan[] = 'Profil jawaban Anda cukup selaras dengan kebutuhan karier '.$topName.'.';
            }

            // area pengembangan (0-2), exclude used criteria
            $areaOut = [];
            foreach (array_slice($area, 0, 2) as $item) {
                $name = is_array($item) ? (string) ($item['kriteria'] ?? '') : (string) $item;
                if ($name === '' || in_array($name, $usedCriteria, true)) {
                    continue;
                }
                $hint = is_array($item) ? (string) ($item['hint'] ?? '') : '';
                if ($hint === '') {
                    $code = $nameToCode[$name] ?? '';
                    $crit = $criteriaByCode[$code] ?? null;
                    $hint = $crit ? (string) ($crit->development_hint ?? '') : '';
                }
                if ($hint === '') {
                    $hint = 'memperkuat area ini';
                }
                $areaOut[] = $name.' masih bisa ditingkatkan; sebagai '.$topName.', Anda perlu '.IndonesianText::lcfirstSmart($hint).'.';
                $usedCriteria[] = $name;
            }

            $patterns = [
                fn (string $item) => 'Mulai dengan mempelajari '.IndonesianText::lcfirstSmart($item).' melalui video atau artikel pengantar.',
                fn (string $item) => 'Setelah itu, latih '.IndonesianText::lcfirstSmart($item).' lewat latihan kecil atau proyek sederhana.',
                fn (string $item) => 'Lanjutkan ke '.IndonesianText::lcfirstSmart($item).' dan catat hal penting yang Anda pelajari.',
            ];
            $langkah = [];
            $items = $roadmap !== [] ? $roadmap : $skills;
            foreach (array_slice($items, 0, 3) as $index => $item) {
                $pattern = $patterns[$index % count($patterns)];
                $langkah[] = $pattern((string) $item);
            }
            if (count($langkah) < 2) {
                $langkah[] = 'Cari satu sumber belajar dasar dan pelajari secara konsisten setiap minggu.';
            }

            $alternatif = $topResults->skip(1)->count() > 0
                ? 'Peringkat berikutnya yang layak dipertimbangkan adalah '.$topResults->skip(1)->map(fn ($item) => $item->career_name.' ('.IndonesianText::percent((float) $item->match_percentage).')')->implode(' dan ').'.'
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
