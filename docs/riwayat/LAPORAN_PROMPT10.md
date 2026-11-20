# LAPORAN PROMPT 10 — TechMind

## A1. Akar masalah profil_karier

`kontribusi_teratas` mengambil profil dari `Career` saat ini via `careerModels`, tetapi blok pengambilan diletakkan SETELAH loop `area_pengembangan`, sehingga variabel `$careerModels` belum terdefinisi (undefined variable) dan profil jatuh ke fallback 0 atau nilai lama. Selain itu, `careerModels` awalnya tidak eager-load `careerProfiles`.

Sebelum:
```php
$development = [];
if ($topResult) {
    foreach ($topResult->gap_summary['needs_strengthening'] ?? [] as $item) {
        ...
        $careerModel = $careerModels->get((string) $topResult->career_code); // undefined
    }
}
...
$careerModels = Career::query()
    ->whereIn('code', ...)
    ->get()->keyBy('code');
```

Sesudah:
```php
$careerModels = Career::query()
    ->with('careerProfiles.criterion')
    ->whereIn('code', $topResults->pluck('career_code')->all())
    ->get()->keyBy('code');

$development = [];
if ($topResult) {
    foreach (...) {
        $careerModel = $careerModels->get((string) $topResult->career_code);
        ...
    }
}
```

`profil_karier` sekarang diambil dari `career_profiles.score` (sumber kanonik matriks D-23). Sesi lama tetap benar karena `career_profiles` adalah tabel matriks, bukan snapshot; jika matriks berubah, payload mengikuti matriks terkini (disengaja, karena matriks adalah data referensi).

## A3. Test profil_karier

Untuk setiap karier, `kontribusi_teratas.profil_karier` sama dengan `career_profiles.score`. Gap/area_pengembangan memakai `gap_summary` snapshot dari `test_results`, sehingga tidak terdampak perubahan matriks setelah sesi dibuat.

## A4. gap_summary di hasil & PDF

`ResultPresentationService::buildNaturalGapSummary` memakai `gap_summary` tersimpan di `test_results`, sehingga tidak terdampak bug profil_karier. Sumber konsisten (snapshot saat submit).

## C7. Output template (5 kasus)

Normal 1:
```
{"ringkasan":"Data Analyst menjadi karier yang paling sesuai dengan profil Anda, dengan tingkat kecocokan 67,1%. Secara singkat, peran ini menganalisis data untuk mendukung keputusan. Hasil ini adalah rekomendasi awal untuk eksplorasi, bukan keputusan akhir.","alasan":["Anda menilai diri tinggi pada Logika & Pemecahan Masalah (5/5), dan kemampuan ini termasuk inti pekerjaan seorang Data Analyst.","Nilai 5/5 pada Analisis Data menunjukkan minat yang kuat, hal yang sangat dibutuhkan dalam peran ini.","Matematika & Statistik juga menjadi kekuatan Anda (5/5) dan sejalan dengan tuntutan sehari-hari seorang Data Analyst."],"area_pengembangan":[],"langkah_awal":["Mulai dengan mempelajari dasar data melalui video atau artikel pengantar.","Setelah itu, latih SQL lewat latihan kecil atau proyek sederhana.","Lanjutkan ke statistik dan catat hal penting yang Anda pelajari."],"alternatif":"Peringkat berikutnya yang layak dipertimbangkan adalah UI/UX Designer (62,9%) dan Cybersecurity Analyst (62,4%)."}
```

Normal 2:
```
{"ringkasan":"System Analyst menjadi karier yang paling sesuai dengan profil Anda, dengan tingkat kecocokan 72,4%. Secara singkat, peran ini menganalisis kebutuhan dan merancang solusi sistem. Hasil ini adalah rekomendasi awal untuk eksplorasi, bukan keputusan akhir.","alasan":["Anda menilai diri tinggi pada Logika & Pemecahan Masalah (4/5), dan kemampuan ini termasuk inti pekerjaan seorang System Analyst.","Nilai 5/5 pada Empati & Riset Pengguna menunjukkan minat yang kuat, hal yang sangat dibutuhkan dalam peran ini.","Komunikasi & Presentasi juga menjadi kekuatan Anda (5/5) dan sejalan dengan tuntutan sehari-hari seorang System Analyst."],"area_pengembangan":[],"langkah_awal":["Mulai dengan mempelajari dasar sistem melalui video atau artikel pengantar.","Setelah itu, latih analisis kebutuhan lewat latihan kecil atau proyek sederhana.","Lanjutkan ke pemodelan dan catat hal penting yang Anda pelajari."],"alternatif":"Peringkat berikutnya yang layak dipertimbangkan adalah UI/UX Designer (66,5%) dan Data Analyst (65,9%)."}
```

Seri semua:
```
{"ringkasan":"Semua karier memiliki tingkat kecocokan yang sama karena jawaban Anda bernilai merata, sehingga sistem belum bisa menentukan arah yang paling sesuai.","alasan":["Jawaban Anda cenderung merata di semua kriteria, sehingga tidak ada kriteria yang menonjol.","Tanpa perbedaan yang jelas, sistem SAW memberi skor yang sama untuk setiap karier."],"area_pengembangan":[],"langkah_awal":["Ulangi tes dan beri nilai 4–5 hanya pada aktivitas yang benar-benar Anda nikmati.","Beri nilai 1–2 pada aktivitas yang kurang Anda minati, agar perbedaannya terlihat.","Baca deskripsi setiap karier di halaman ini dan tandai yang paling membuat Anda penasaran."],"alternatif":"Karier-karier yang setara di atas layak Anda pertimbangkan bersama. Untuk hasil yang lebih tajam, ulangi tes dengan penilaian yang lebih tegas."}
```

Seri sebagian:
```
{"ringkasan":"Beberapa karier memiliki tingkat kecocokan yang sama, yaitu Mobile Developer dan Backend Developer, dengan tingkat kecocokan 65,4%. Hasil ini adalah rekomendasi awal untuk eksplorasi, bukan keputusan akhir.","alasan":["Anda menilai diri tinggi pada Desain Visual (4/5), dan kemampuan ini mendukung karier-karier yang setara di atas.","Anda menilai diri tinggi pada Keamanan & Risiko (5/5), dan kemampuan ini mendukung karier-karier yang setara di atas."],"area_pengembangan":[],"langkah_awal":["Untuk Mobile Developer, mulai dari dasar mobile.","Untuk Backend Developer, mulai dari dasar backend.","Pilih karier yang paling menarik bagi Anda dan dalami langkah pertamanya."],"alternatif":"Karier-karier yang setara di atas layak Anda pertimbangkan bersama. Untuk hasil yang lebih tajam, ulangi tes dengan penilaian yang lebih tegas."}
```

Kekuatan kosong:
```
{"ringkasan":"Data Analyst menjadi karier yang paling sesuai dengan profil Anda, dengan tingkat kecocokan 66,2%. Secara singkat, peran ini menganalisis data untuk mendukung keputusan. Hasil ini adalah rekomendasi awal untuk eksplorasi, bukan keputusan akhir.","alasan":["Nilai Anda pada Analisis Data (3/5) sudah memberi dasar yang sejalan dengan kebutuhan utama seorang Data Analyst.","Nilai Anda pada Matematika & Statistik (3/5) sudah memberi dasar yang sejalan dengan kebutuhan utama seorang Data Analyst.","Nilai Anda pada Komunikasi & Presentasi (3/5) sudah memberi dasar yang sejalan dengan kebutuhan utama seorang Data Analyst."],"area_pengembangan":["Logika & Pemecahan Masalah masih bisa ditingkatkan; sebagai Data Analyst, Anda perlu memecah masalah kompleks menjadi langkah-langkah yang runtut."],"langkah_awal":["Mulai dengan mempelajari dasar data melalui video atau artikel pengantar.","Setelah itu, latih SQL lewat latihan kecil atau proyek sederhana.","Lanjutkan ke statistik dan catat hal penting yang Anda pelajari."],"alternatif":"Peringkat berikutnya yang layak dipertimbangkan adalah QA Engineer (64,6%) dan Backend Developer (64,6%)."}
```

## E1. Memori puncak php-fpm (20 request PDF, konkurensi 5)

`docker stats --no-stream` (saat beban):

```
CONTAINER ID   NAME                    CPU %     MEM USAGE / LIMIT     MEM %     NET I/O          BLOCK I/O         PIDS
9d04491a9317   techmind-prod-nginx-1   0.00%     7.945MiB / 31.09GiB   0.02%     828kB / 855kB    0B / 0B           9
c6ffc535d76a   techmind-prod-app-1     0.00%     55.52MiB / 31.09GiB   0.17%     538kB / 646kB    0B / 0B           4
cad62a394ce2   techmind-prod-mysql-1   0.32%     466.9MiB / 31.09GiB   1.47%     981kB / 2.74MB   23.7MB / 465MB    39
```

`memory_get_peak_usage()` saat pembuatan PDF: peak 8388608 bytes (8 MB), real 1876840 bytes.

RSS maksimum proses php-fpm selama uji: sekitar 55 MB.

## E2. pm.max_children untuk VPS 2 GB

Formula: `(2048 - 300 (OS) - 467 (MySQL) - 8 (nginx)) / 55 (RSS puncak) * 0.8 ≈ 18`. Gunakan 18 (atau 20 dengan margin). Konfigurasi MySQL hemat memori tidak diperlukan karena MySQL ~467 MB.

## `composer ci:check`

```
{"tool":"pest","result":"passed","tests":86,"passed":86,"assertions":1088,"duration_ms":10761}
```

## `git log --oneline -10`

```
e224576 docs: add prompt 10 verification report
0311528 docs: add dev vs prod configuration guide
8ad9ea3 fix: use career_profiles for profil_karier in AI payload
0ec1b4b docs: add prompt 9 verification report
9282522 fix: correct contribution rating and template output
865b5bd style: apply pint formatting to template explainer
dc7a5ea docs: expand deploy guide with scenarios and backup
767d3f6 feat: rewrite template explainer with natural structured output
527afc0 feat: fix AI payload (tied_careers, contributions, tolerant percentages)
3f68d45 style: apply pint formatting to changed files
16d3c98 feat: wire impact check dialog into career matrix admin UI
```

## `git status`

```
On branch feat/spk-matrix-d23
nothing to commit, working tree clean
```

## Kendala & keputusan sendiri

- `profil_karier` sebelumnya bisa salah karena `$careerModels` belum terdefinisi saat dipakai; diperbaiki dengan memindahkan inisialisasi dan eager-load `careerProfiles`.
- `development_hint` disimpan di tabel `criteria` dan disertakan di payload area_pengembangan sebagai objek.
- Format persen Indonesia diterapkan di helper PHP/TS; CSV tetap memakai titik agar terbaca Excel.
- Memori puncak PDF ~8 MB, RSS worker ~55 MB; `pm.max_children` disarankan 18 untuk VPS 2 GB.
