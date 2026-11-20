# LAPORAN PROMPT 9 — TechMind

## A1. `git diff 39bba87..HEAD --stat`

```
 app/Console/Commands/AiCheck.php                   |  20 ++-
 app/Data/AiExplanationResult.php                   |   1 +
 app/Http/Controllers/Admin/CareerController.php    |   1 +
 .../Controllers/Admin/TestSessionController.php    |   3 +-
 app/Http/Controllers/Public/TestController.php     |   6 +-
 app/Models/AiExplanation.php                       |   1 +
 app/Providers/AppServiceProvider.php               |  30 +++-
 app/Repositories/CareerProfileRepository.php       |  10 +-
 app/Repositories/CareerRepository.php              |   4 +-
 app/Repositories/CriterionRepository.php           |   2 +-
 .../EloquentAiExplanationRepository.php            |   1 +
 app/Services/Ai/ExplanationPayloadBuilder.php      |  93 ++++++++++
 app/Services/Ai/ExplanationService.php             |   1 +
 app/Services/Ai/OpenAiCompatibleExplainer.php      | 149 ++++++++++++----
 app/Services/Ai/TemplateAiExplainer.php            |  70 +++++++-
 app/Services/ResultPresentationService.php         |   2 +
 bootstrap/app.php                                  |  15 ++
 config/inertia.php                                 |   2 +-
 config/techmind.php                                |  10 ++
 ..._000003_add_format_to_ai_explanations_table.php |  22 +++
 docker/nginx.conf                                  |   3 +
 docs/DEPLOY-PAMERAN.md                            |   6 +
 docs/LAPORAN_PROMPT8.md                           | 190 +++++++++++++++++++++
 phpunit.xml                                        |   1 +
 resources/js/pages/admin/careers/form.tsx          | 118 ++++++++++++-
 resources/js/pages/admin/careers/index.tsx         | 137 ++++++++++++++-
 resources/js/pages/public/result.tsx               | 148 +++++++++++++++-
 resources/prompts/explanation_system.txt           |  31 ++++
 resources/views/errors/429.blade.php               |  34 ++++
 resources/views/pdf/result.blade.php               |  34 +++-
 routes/web.php                                     |   6 +-
 tests/Feature/AiExplanationTest.php                | 172 ++++++++++++++++---
 tests/Feature/MatrixHealthPreviewTest.php          |  33 ++++
 33 files changed, 1266 insertions(+), 90 deletions(-)
```

## A3. Audit temuan & tindakan

| File | Temuan | Tindakan |
|---|---|---|
| OpenAiCompatibleExplainer.php | Method `parseJson()` tidak terpakai (duplikat logika parsing) | Dihapus |
| OpenAiCompatibleExplainer.php | Validasi persentase terlalu ketat (hanya exact match) | Diperlonggar (koma, spasi, pembulatan) |
| TemplateAiExplainer.php | Kode lama (paragraphs) menumpuk di atas implementasi baru | Ditulis ulang total |
| ExplanationPayloadBuilder.php | `tied_careers` terisi walau tidak seri; `rating_anda` salah key | Diperbaiki |
| CareerProfileRepository.php | Rekonstruksi model via `forceFill` membuat field JSON ganda | Diganti `setRawAttributes` |
| CareerRepository.php | Sama seperti di atas (skills/tools/roadmap) | Diganti `setRawAttributes` |
| CriterionRepository.php | Rekonstruksi via `forceFill` | Diganti `setRawAttributes` |
| resources/prompts/explanation_system.txt | Aturan seri & kekuatan kosong belum ada | Ditambah |

## A4. Commit 6189ea3 — CSRF

Sebelumnya POST /tes dikecualikan dari CSRF sejak commit `c5bff20` (`validateCsrfTokens(except: ['tes'])`). Commit 6189ea3 menghapus pengecualian tersebut.

Diff relevan:

```diff
-        $middleware->validateCsrfTokens(except: ['tes']);
```

## B2. Payload lengkap (sesi contoh normal)

```json
{
    "is_tied_top": false,
    "tied_careers": [],
    "top_careers": [
        { "rank": 1, "name": "Data Analyst", "match_percentage": 67.1, "summary": "Menganalisis data untuk mendukung keputusan.", "skills": ["SQL","Statistik","Visualisasi data","Spreadsheet"], "roadmap_awal": ["Dasar data","SQL","Statistik"] },
        { "rank": 2, "name": "UI/UX Designer", "match_percentage": 62.9, "summary": "Merancang pengalaman dan antarmuka pengguna.", "skills": ["Riset pengguna","Wireframe","Prototipe","Desain visual"], "roadmap_awal": ["Dasar UX","Wireframe dan prototipe","Design system"] },
        { "rank": 3, "name": "Cybersecurity Analyst", "match_percentage": 62.4, "summary": "Melindungi sistem dari ancaman keamanan.", "skills": ["Keamanan jaringan","Analisis ancaman","Hardening","Log analysis"], "roadmap_awal": ["Dasar keamanan","Networking","Analisis ancaman"] }
    ],
    "kekuatan_pengguna": ["Logika & Pemecahan Masalah","Analisis Data","Matematika & Statistik"],
    "area_pengembangan": [],
    "kontribusi_teratas": [
        { "kriteria": "Logika & Pemecahan Masalah", "rating_anda": 5, "profil_karier": 4 },
        { "kriteria": "Analisis Data", "rating_anda": 5, "profil_karier": 4 },
        { "kriteria": "Matematika & Statistik", "rating_anda": 5, "profil_karier": 3 }
    ],
    "jawaban": {
        "Logika & Pemecahan Masalah": 5,
        "Analisis Data": 5,
        "Matematika & Statistik": 5,
        "Ketelitian & Pengujian": 4,
        "Desain Visual": 4,
        "Kreativitas": 4,
        "Empati & Riset Pengguna": 3,
        "Komunikasi & Presentasi": 3,
        "Pemrograman & Pengembangan Perangkat Lunak": 3,
        "Jaringan & Infrastruktur": 2,
        "Keamanan & Risiko": 2,
        "Pemahaman Bisnis & Proses": 2
    }
}
```

## C4. Output template

Normal sesi 1:

```
{"ringkasan":"Data Analyst menjadi karier yang paling sesuai dengan profil Anda, dengan tingkat kecocokan 67.1%. Menganalisis data untuk mendukung keputusan. Hasil ini adalah rekomendasi awal untuk eksplorasi, bukan keputusan akhir.","alasan":["Anda menilai diri tinggi pada Logika & Pemecahan Masalah (5/5), dan kemampuan ini termasuk inti pekerjaan seorang Data Analyst.","Anda menilai diri tinggi pada Analisis Data (5/5), dan kemampuan ini termasuk inti pekerjaan seorang Data Analyst.","Anda menilai diri tinggi pada Matematika & Statistik (5/5), dan kemampuan ini termasuk inti pekerjaan seorang Data Analyst."],"area_pengembangan":[],"langkah_awal":["Mulai dengan memahami Dasar data: konsep dasar dan contoh penerapannya.","Luangkan 30 menit sehari untuk berlatih SQL secara bertahap.","Buat catatan singkat dari materi Statistik dan ulangi tiap minggu."],"alternatif":"Peringkat berikutnya yang layak dipertimbangkan adalah UI/UX Designer (62.9%) dan Cybersecurity Analyst (62.4%)."}
```

Normal sesi 2:

```
{"ringkasan":"System Analyst menjadi karier yang paling sesuai dengan profil Anda, dengan tingkat kecocokan 72.4%. Menganalisis kebutuhan dan merancang solusi sistem. Hasil ini adalah rekomendasi awal untuk eksplorasi, bukan keputusan akhir.","alasan":["Anda menilai diri tinggi pada Logika & Pemecahan Masalah (4/5), dan kemampuan ini termasuk inti pekerjaan seorang System Analyst.","Anda menilai diri tinggi pada Empati & Riset Pengguna (5/5), dan kemampuan ini termasuk inti pekerjaan seorang System Analyst.","Anda menilai diri tinggi pada Komunikasi & Presentasi (5/5), dan kemampuan ini termasuk inti pekerjaan seorang System Analyst."],"area_pengembangan":[],"langkah_awal":["Luangkan 30 menit sehari untuk berlatih Dasar sistem secara bertahap.","Buat catatan singkat dari materi Analisis kebutuhan dan ulangi tiap minggu.","Mulai dengan memahami Pemodelan: konsep dasar dan contoh penerapannya."],"alternatif":"Peringkat berikutnya yang layak dipertimbangkan adalah UI/UX Designer (66.5%) dan Data Analyst (65.9%)."}
```

Seri semua:

```
{"ringkasan":"Semua karier memiliki tingkat kecocokan yang sama karena jawaban Anda bernilai merata, sehingga sistem belum bisa menentukan arah yang paling sesuai.","alasan":["Jawaban Anda cenderung merata di semua kriteria, sehingga tidak ada kriteria yang menonjol.","Tanpa perbedaan yang jelas, sistem SAW memberi skor yang sama untuk setiap karier."],"area_pengembangan":[],"langkah_awal":["Ulangi tes dan beri nilai 4–5 hanya pada aktivitas yang benar-benar Anda nikmati.","Beri nilai 1–2 pada aktivitas yang kurang Anda minati, agar perbedaannya terlihat.","Baca deskripsi setiap karier di halaman ini dan tandai yang paling membuat Anda penasaran."],"alternatif":"Karier-karier yang setara di atas layak Anda pertimbangkan bersama. Untuk hasil yang lebih tajam, ulangi tes dengan penilaian yang lebih tegas."}
```

Seri sebagian:

```
{"ringkasan":"Beberapa karier memiliki tingkat kecocokan yang sama, yaitu Data Analyst, Backend Developer, dengan tingkat kecocokan 65.2%. Hasil ini adalah rekomendasi awal untuk eksplorasi, bukan keputusan akhir.","alasan":["Anda menilai diri tinggi pada Matematika & Statistik (4/5), dan kemampuan ini mendukung karier-karier yang setara di atas.","Perbedaan kecil antar kriteria membuat sistem belum bisa menentukan satu karier unggulan."],"area_pengembangan":[],"langkah_awal":["Untuk Data Analyst, mulai dari Dasar data.","Untuk Backend Developer, mulai dari Dasar backend.","Pilih karier yang paling menarik bagi Anda dan dalami langkah pertamanya."],"alternatif":"Karier-karier yang setara di atas layak Anda pertimbangkan bersama. Untuk hasil yang lebih tajam, ulangi tes dengan penilaian yang lebih tegas."}
```

Kekuatan kosong:

```
{"ringkasan":"Mobile Developer menjadi karier yang paling sesuai dengan profil Anda, dengan tingkat kecocokan 65.6%. Membangun aplikasi mobile native atau cross-platform. Hasil ini adalah rekomendasi awal untuk eksplorasi, bukan keputusan akhir.","alasan":["Kontribusi terbesar pada skor karier ini berasal dari Pemrograman & Pengembangan Perangkat Lunak (rating Anda 3/5, profil karier 5/5).","Kontribusi terbesar pada skor karier ini berasal dari Logika & Pemecahan Masalah (rating Anda 3/5, profil karier 4/5).","Kontribusi terbesar pada skor karier ini berasal dari Desain Visual (rating Anda 3/5, profil karier 4/5)."],"area_pengembangan":["Ketelitian & Pengujian masih bisa ditingkatkan; seorang Mobile Developer perlu Cermat, uji kasus, kualitas.","Pemrograman & Pengembangan Perangkat Lunak masih bisa ditingkatkan; seorang Mobile Developer perlu Menulis dan merawat kode perangkat lunak."],"langkah_awal":["Mulai dengan memahami Dasar mobile: konsep dasar dan contoh penerapannya.","Luangkan 30 menit sehari untuk berlatih Framework cross-platform secara bertahap.","Buat catatan singkat dari materi Integrasi API dan ulangi tiap minggu."],"alternatif":"Peringkat berikutnya yang layak dipertimbangkan adalah Data Analyst (64.0%) dan UI/UX Designer (63.2%)."}
```

## D1. Docker files

`ls -R docker`:

```
docker:
entrypoint.prod.sh
entrypoint.sh
mysql
nginx.conf
php.ini

docker/mysql:
init.sql
```

`docker-compose.prod.yml` ada (lihat docs/DEPLOY-PAMERAN.md).

## D2. Memori worker php-fpm

```
PID 23 RSS: 41620 KB
PID 24 RSS: 41236 KB
```

Rumus: `(2048 - 512) / 41 ≈ 37`, gunakan 20 untuk margin aman.

## `composer ci:check`

```
{"tool":"pest","result":"passed","tests":86,"passed":86,"assertions":1088,"duration_ms":10559}
```

## `git log --oneline -10`

```
da9e2cf docs: add prompt 9 verification report
9282522 fix: correct contribution rating and template output
865b5bd style: apply pint formatting to template explainer
dc7a5ea docs: expand deploy guide with scenarios and backup
767d3f6 feat: rewrite template explainer with natural structured output
527afc0 feat: fix AI payload (tied_careers, contributions, tolerant percentages)
3f68d45 style: apply pint formatting to changed files
16d3c98 feat: wire impact check dialog into career matrix admin UI
cb1f55a feat: structured JSON AI explanations with template fallback
6189ea3 fix: enforce CSRF on public test submissions and disable SSR in tests
b7da324 docs: add pameran afterResponse and proxy limits
```

## `git status`

```
On branch feat/spk-matrix-d23
nothing to commit, working tree clean
```

## Kendala & keputusan sendiri

- `tied_careers` sebelumnya terisi walau tidak seri; diperbaiki agar kosong saat `is_tied_top=false`.
- Validasi persentase diperlonggar untuk menerima koma, spasi, dan pembulatan.
- Template ditulis ulang agar selalu memenuhi skema validator AI (2-3 alasan, 2-3 langkah, ringkasan >=14 kata).
- Worker php-fpm diukur ~41 MB; `pm.max_children` disarankan 20 untuk VPS 2 GB.
