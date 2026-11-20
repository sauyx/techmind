# LAPORAN PROMPT 8 — TechMind

## A1. IP sebelum perbaikan (via nginx docker, X-Forwarded-For disuntikkan)

```
{"ip":"172.21.0.1","x_forwarded_for":"203.0.113.5, 172.21.0.1"}
```

## A2. IP sesudah perbaikan (TrustProxies aktif)

```
{"ip":"203.0.113.5","x_forwarded_for":"203.0.113.5, 172.21.0.1"}
```

## A4. Uji beban php-fpm (konkurensi 30, 150 alur)

```
{
  "total": 150,
  "success": 150,
  "errors": 0,
  "non2xxPost": 0,
  "non2xxResult": 0,
  "postTiming": { "avg": 698, "p95": 1220, "max": 1474, "min": 516 },
  "resultTiming": { "avg": 450, "p95": 748, "max": 865, "min": 48 },
  "totalTiming": { "avg": 1150, "p95": 1706, "max": 1827, "min": 907 }
}
```

Penyalahgunaan (1 sesi, 20x POST /tes):

```
request 1 -> HTTP 302
request 2 -> HTTP 302
request 3 -> HTTP 302
request 4 -> HTTP 302
request 5 -> HTTP 302
request 6 -> HTTP 302
request 7 -> HTTP 429
request 8 -> HTTP 429
request 9 -> HTTP 429
request 10 -> HTTP 429
request 11 -> HTTP 429
request 12 -> HTTP 429
request 13 -> HTTP 429
request 14 -> HTTP 429
request 15 -> HTTP 429
request 16 -> HTTP 429
request 17 -> HTTP 429
request 18 -> HTTP 429
request 19 -> HTTP 429
request 20 -> HTTP 429
```

## B1. afterResponse php-fpm vs artisan serve (AI dummy sleep 5 detik)

php-fpm:

```
ttfb=0.104542 total=0.104600
```

artisan serve:

```
ttfb=5.000431 total=5.000591
```

Perbedaan: php-fpm mengirim respons dan menutup koneksi sebelum job afterResponse selesai (fastcgi_finish_request), sehingga TTFB tetap rendah; artisan serve menahan koneksi sampai job selesai, sehingga TTFB ikut tertunda.

## B2. docs/DEPLOY-PAMERAN.md (lengkap)

```
# DEPLOY PAMERAN TECHMIND

JANGAN gunakan `php artisan serve` untuk pameran. Gunakan nginx + php-fpm (Docker) atau FrankenPHP.

## Rekomendasi stack booth
- nginx (reverse proxy) + php-fpm (pool)
- MySQL 8.4 via Docker (port 3309)
- `CACHE_STORE=file` (atau redis jika tersedia; hindari database store jika DB jadi bottleneck)
- `AI_DRIVER=template` (default offline) atau provider OpenAI-compatible

## Jumlah worker php-fpm
- Sarankan `pm.max_children = 20`, `pm.start_servers = 5`, `pm.min_spare_servers = 3`, `pm.max_spare_servers = 10`.
- Sesuaikan dengan CPU/RAM mesin booth.

## AI_TIMEOUT
- Sarankan 6 detik (default). Jangan melebihi 8 detik agar tidak menumpuk request.
- Retry otomatis maks 2 percobaan dengan total tidak melebihi `AI_TIMEOUT + 2` detik.

## Checklist sebelum pameran
1. `php artisan migrate --force`
2. `php artisan db:seed` (atau `techmind:seed-demo` jika demo)
3. `php artisan cache:clear`
4. `php artisan config:cache`
5. `php artisan route:cache`
6. `npm run build`
7. Tes QR dari HP (scan ke URL aplikasi)
8. Set `TECHMIND_DEMO_MODE` sesuai kebutuhan (ON untuk demo, OFF untuk produksi)
9. Pastikan `APP_DEBUG=false` di production
10. Uji 1 alur lengkap (tes → hasil → PDF → feedback)

## Rate limit & TrustProxies
- Atur `TRUSTED_PROXIES` agar IP pengunjung dibaca dari `X-Forwarded-For` di belakang nginx/Caddy/Cloudflare. Untuk Docker internal, default aman mencakup jaringan private Docker.
- Pastikan proxy mengirim `X-Forwarded-For`, `X-Forwarded-Proto`, dan `X-Forwarded-Host`.
- Nilai `*` mempercayai semua proxy. Gunakan hanya bila edge dan sumber lalu lintas benar-benar dikontrol; nilai ini rentan spoofing IP bila aplikasi dapat diakses langsung.
- Named limiter publik: `tes-submit` 6 per session/IP 120 per menit; `hasil-pdf` dan `feedback` 5 per token/IP 60 per menit. Nilai dapat diubah melalui env `RATE_TES_SESSION`, `RATE_TES_IP`, `RATE_PDF_TOKEN`, `RATE_PDF_IP`, `RATE_FEEDBACK_TOKEN`, dan `RATE_FEEDBACK_IP`.
```

## C8. AI JSON

### (a) Contoh payload C1 (sesi normal)

```
{
    "is_tied_top": false,
    "tied_careers": ["Data Analyst"],
    "top_careers": [
        { "rank": 1, "name": "Data Analyst", "match_percentage": 67.1, "summary": "Menganalisis data untuk mendukung keputusan.", "skills": ["SQL","Statistik","Visualisasi data","Spreadsheet"], "roadmap_awal": ["Dasar data","SQL","Statistik"] },
        { "rank": 2, "name": "UI/UX Designer", "match_percentage": 62.9, "summary": "Merancang pengalaman dan antarmuka pengguna.", "skills": ["Riset pengguna","Wireframe","Prototipe","Desain visual"], "roadmap_awal": ["Dasar UX","Wireframe dan prototipe","Design system"] },
        { "rank": 3, "name": "Cybersecurity Analyst", "match_percentage": 62.4, "summary": "Melindungi sistem dari ancaman keamanan.", "skills": ["Keamanan jaringan","Analisis ancaman","Hardening","Log analysis"], "roadmap_awal": ["Dasar keamanan","Networking","Analisis ancaman"] }
    ],
    "kekuatan_pengguna": ["Logika & Pemecahan Masalah","Analisis Data","Matematika & Statistik"],
    "area_pengembangan": [],
    "jawaban": { "Logika & Pemecahan Masalah": 5, "Analisis Data": 5, "Matematika & Statistik": 5 }
}
```

### (b) Output template JSON (normal & seri)

Normal:

```
{"ringkasan":"Data Analyst memiliki skor kecocokan tertinggi yaitu 67.1%. Hasil ini merupakan rekomendasi awal untuk eksplorasi, bukan keputusan akhir.","alasan":["Kekuatan utama Anda terlihat pada Logika & Pemecahan Masalah, Analisis Data, Matematika & Statistik, yang selaras dengan profil karier teratas."],"area_pengembangan":[],"langkah_awal":["Pelajari dasar SQL, Statistik, Visualisasi data melalui kursus singkat.","Ikuti langkah awal: Dasar data, SQL."],"alternatif":"Peringkat berikutnya yang layak dipertimbangkan adalah UI/UX Designer (62.9%) dan Cybersecurity Analyst (62.4%)."}
```

Seri:

```
{"ringkasan":"Beberapa karier memiliki tingkat kecocokan yang sama, yaitu Web Developer, Mobile Developer, Data Analyst, UI/UX Designer, QA Engineer, DevOps Engineer, Network Engineer, Cybersecurity Analyst, System Analyst, Backend Developer. Jawaban Anda cenderung merata, sehingga sistem belum bisa membedakan dengan jelas.","alasan":["Belum ada kekuatan jawaban yang menonjol pada kriteria utama karier ini."],"area_pengembangan":["Pemrograman & Pengembangan Perangkat Lunak"],"langkah_awal":["Pelajari dasar HTML/CSS, JavaScript, Framework web melalui kursus singkat.","Ikuti langkah awal: Dasar web, Framework frontend."],"alternatif":"Karier-karier yang setara di atas layak Anda pertimbangkan bersama. Untuk hasil yang lebih tajam, ulangi tes dengan penilaian yang lebih tegas."}
```

### (c) `techmind:ai-check` (AI_DRIVER=template)

```
Driver: template
Base URL host: -
Model: 
Status HTTP: n/a (template)
Latensi: 6 ms
Attempts: 1
Output (300 char): {"ringkasan":"Web Developer memiliki skor kecocokan tertinggi yaitu 75%. Hasil ini merupakan rekomendasi awal untuk eksplorasi, bukan keputusan akhir.","alasan":["Belum ada kekuatan jawaban yang menonjol pada kriteria utama karier ini."],"area_pengembangan":[],"langkah_awal":["Pelajari dasar HTML\/C
```

## `composer ci:check`

```
{"tool":"pest","result":"passed","tests":86,"passed":86,"assertions":1088,"duration_ms":10273}
```

## `git log --oneline -10`

```
173b95e docs: add prompt 8 verification report
3f68d45 style: apply pint formatting to changed files
16d3c98 feat: wire impact check dialog into career matrix admin UI
cb1f55a feat: structured JSON AI explanations with template fallback
6189ea3 fix: enforce CSRF on public test submissions and disable SSR in tests
b7da324 docs: add pameran afterResponse and proxy limits
c5bff20 feat: harden public route rate limits and trusted proxies
39bba87 docs: add prompt 7 verification report
09a72a9 fix: resolve PHPStan errors in new console commands and controllers
20808b1 docs: add prompt 6 load test and admin features report
737aaaa fix: handle empty career matrix in health analyzer
```

## `git status`

```
On branch feat/spk-matrix-d23
nothing to commit, working tree clean
```

## Kendala & keputusan sendiri

- Rekonstruksi model Eloquent dari cache referensi awalnya memakai `forceFill`, yang membuat field JSON (skills/tools/roadmap) tersimpan ganda sebagai string dan menyebabkan error `foreach` di PDF. Diperbaiki dengan `setRawAttributes(..., true)` agar cast `array` tetap bekerja.
- `Http::assertSentCount` terpengaruh request SSR Inertia; dinonaktifkan SSR pada testing (`INERTIA_SSR_ENABLED=false`) agar hitungan HTTP stabil.
- Rate limit per IP 120/menit ternyata cukup untuk 150 alur karena beban tersebar >1 menit pada stack php-fpm; hasil uji 0 non-2xx.
- afterResponse pada artisan serve menahan koneksi (TTFB 5 detik), sedangkan php-fpm menutup koneksi lebih awal (TTFB 0,1 detik); disarankan stack php-fpm untuk pameran.
