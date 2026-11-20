# DIAGNOSA SPK TECHMIND

Tanggal: 2026-11-20. Mode Bagian A: perbaikan CI terbatas. Bagian B: read-only (tidak ada perubahan rumus/data).

## A. Hasil Perbaikan CI

- File diubah: `tests/Feature/LandingPageTest.php`.
- Perubahan: assertion teks klien yang stale (`'Kenali potensimu'`, lalu `'Kenali potensi Anda'`) dihapus karena teks tersebut dirender di sisi klien (JS) dan tidak ada di HTML awal; test sekarang hanya menegaskan `'TechMind'` yang memang ada di respons awal. UI tidak diubah.
- Assertion teks stale lain yang ditemukan: `tests/Feature/LandingPageTest.php` juga menegaskan `'Cara kerja TechMind'` (teks klien, tidak ada di HTML awal) — dihapus dalam perbaikan yang sama. `tests/Feature/BoothAndDemoTest.php` menegaskan `'Halaman tidak ditemukan'` (ada di HTML 404) — valid, tidak diubah.
- Hasil `composer ci:check`: **lulus** (Pint pass, TypeScript pass, Vite build pass, Pest 60 passed, 0 failed, 839 assertions).

## B1. Matriks Profil Karier (10×12)

Sumber: `database/seeders/data/career_profiles.php`. Semua total = 37. Kolom: C1 logic_problem_solving, C2 data_analysis, C3 math_statistics, C4 attention_detail, C5 visual_design, C6 creativity, C7 user_empathy, C8 communication, C9 coding, C10 network_infra, C11 security_awareness, C12 business_process.

| Karier | C1 | C2 | C3 | C4 | C5 | C6 | C7 | C8 | C9 | C10 | C11 | C12 | TOTAL |
|---|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|
| web_developer | 3 | 2 | 1 | 3 | 3 | 4 | 4 | 4 | 5 | 2 | 2 | 4 | 37 |
| mobile_developer | 4 | 2 | 2 | 4 | 4 | 3 | 3 | 2 | 5 | 1 | 4 | 3 | 37 |
| backend_developer | 5 | 3 | 3 | 4 | 1 | 2 | 1 | 2 | 5 | 4 | 4 | 3 | 37 |
| data_analyst | 4 | 5 | 5 | 3 | 2 | 3 | 2 | 4 | 3 | 1 | 2 | 3 | 37 |
| ui_ux_designer | 3 | 3 | 1 | 3 | 5 | 5 | 5 | 4 | 2 | 1 | 1 | 4 | 37 |
| qa_engineer | 4 | 3 | 1 | 5 | 2 | 3 | 4 | 4 | 3 | 1 | 3 | 4 | 37 |
| devops_engineer | 4 | 3 | 2 | 4 | 1 | 3 | 1 | 3 | 4 | 5 | 4 | 3 | 37 |
| network_engineer | 4 | 2 | 2 | 5 | 1 | 3 | 2 | 3 | 3 | 5 | 4 | 3 | 37 |
| cybersecurity_analyst | 4 | 3 | 2 | 5 | 1 | 3 | 1 | 3 | 3 | 4 | 5 | 3 | 37 |
| system_analyst | 4 | 3 | 2 | 4 | 2 | 3 | 5 | 5 | 1 | 1 | 2 | 5 | 37 |
| MAX per kriteria | 5 | 5 | 5 | 5 | 5 | 5 | 5 | 5 | 5 | 5 | 5 | 5 | 37 |

## B2. Persona FairnessTest

Sumber: `tests/Feature/FairnessTest.php`. Rating dalam urutan kriteria: C1..C12.

Persona set 1 (`test_personas_rank_expected_career_first`):
- web_developer → [4,2,2,3,3,3,3,3,5,2,3,2] (diharapkan #1 web_developer)
- mobile_developer → [4,2,2,4,3,3,3,3,5,2,3,2]
- backend_developer → [5,3,3,4,1,2,2,3,5,4,4,2]
- data_analyst → [4,5,5,4,2,2,2,4,3,1,2,3]
- ui_ux_designer → [3,3,1,4,5,5,5,4,2,1,2,4]
- qa_engineer → [4,3,2,5,2,2,3,3,3,2,3,2]
- devops_engineer → [4,3,2,4,1,2,2,3,4,5,4,2]
- network_engineer → [4,2,2,4,1,2,2,3,3,5,4,2]
- cybersecurity_analyst → [4,3,2,5,1,2,2,4,3,4,5,3]
- system_analyst → [4,3,2,4,2,3,5,5,1,1,2,5]

Persona set 2 (`test_second_personas_rank_expected_career_first`):
- web_developer → [3,2,1,3,4,4,3,4,4,2,2,3]
- mobile_developer → [3,2,1,4,4,3,3,3,4,1,3,3]
- backend_developer → [4,3,2,4,1,2,2,3,5,4,4,3]
- data_analyst → [3,4,4,3,2,3,2,4,2,1,2,4]
- ui_ux_designer → [2,2,1,3,5,5,4,4,1,1,1,4]
- qa_engineer → [4,3,2,5,2,2,3,4,2,2,3,3]
- devops_engineer → [4,3,2,4,1,2,2,3,4,5,4,3]
- network_engineer → [3,2,2,4,1,2,2,3,2,5,4,2]
- cybersecurity_analyst → [3,3,2,5,1,2,2,4,2,4,5,3]
- system_analyst → [3,3,2,4,2,3,4,5,1,1,2,5]

## B3. Hasil Simulasi Persona (SAW)

Format skor = V_i × 100. "DIFF" = skor rank #1 − skor karier yang diharapkan.

Set 1:
- web_developer: top5 = mobile_developer 66.9, web_developer 65.7, backend_developer 64.6, qa_engineer 64.0, devops_engineer 62.9. Posisi expected (web_developer): 2. DIFF=1.14.
- mobile_developer: top5 = mobile_developer 67.2, web_developer 65.6, qa_engineer 65.0, backend_developer 65.0, devops_engineer 63.3. Posisi expected: 1. DIFF=0.
- backend_developer: top5 = backend_developer 71.1, devops_engineer 68.9, cybersecurity_analyst 68.4, network_engineer 67.9, mobile_developer 63.7. Posisi expected: 1. DIFF=0.
- data_analyst: top5 = data_analyst 70.8, system_analyst 64.9, qa_engineer 63.8, backend_developer 63.8, cybersecurity_analyst 62.2. Posisi expected: 1. DIFF=0.
- ui_ux_designer: top5 = ui_ux_designer 74.4, system_analyst 69.7, qa_engineer 67.7, web_developer 67.2, mobile_developer 64.1. Posisi expected: 1. DIFF=0.
- qa_engineer: top5 = qa_engineer 67.1, cybersecurity_analyst 65.9, network_engineer 65.3, backend_developer 65.3, mobile_developer 64.7. Posisi expected: 1. DIFF=0.
- devops_engineer: top5 = devops_engineer 70.0, backend_developer 70.0, network_engineer 69.4, cybersecurity_analyst 69.4, qa_engineer 62.2. Posisi expected: 1. DIFF=0.
- network_engineer: top5 = network_engineer 70.6, devops_engineer 70.0, cybersecurity_analyst 70.0, backend_developer 69.4, qa_engineer 62.4. Posisi expected: 1. DIFF=0.
- cybersecurity_analyst: top5 = cybersecurity_analyst 70.5, network_engineer 69.5, devops_engineer 68.9, backend_developer 68.4, qa_engineer 65.3. Posisi expected: 1. DIFF=0.
- system_analyst: top5 = system_analyst 75.1, ui_ux_designer 70.3, qa_engineer 70.3, web_developer 65.4, data_analyst 64.3. Posisi expected: 1. DIFF=0.

Set 2:
- web_developer: top5 = ui_ux_designer 68.6, web_developer 68.0, mobile_developer 65.1, qa_engineer 65.1, system_analyst 64.0. Posisi expected (web_developer): 2. DIFF=0.57.
- mobile_developer: top5 = mobile_developer 68.2, web_developer 67.1, ui_ux_designer 67.1, qa_engineer 67.1, system_analyst 64.1. Posisi expected: 1. DIFF=0.
- backend_developer: top5 = backend_developer 70.3, devops_engineer 69.2, cybersecurity_analyst 68.6, network_engineer 68.1, mobile_developer 63.8. Posisi expected: 1. DIFF=0.
- data_analyst: top5 = data_analyst 68.8, system_analyst 67.1, qa_engineer 64.1, ui_ux_designer 63.5, web_developer 61.2. Posisi expected: 1. DIFF=0.
- ui_ux_designer: top5 = ui_ux_designer 77.6, system_analyst 70.9, web_developer 67.9, qa_engineer 66.7, mobile_developer 62.4. Posisi expected: 1. DIFF=0.
- qa_engineer: top5 = qa_engineer 68.0, system_analyst 68.0, cybersecurity_analyst 65.7, network_engineer 65.1, data_analyst 63.4. Posisi expected: 1. DIFF=0.
- devops_engineer: top5 = devops_engineer 69.7, backend_developer 69.7, network_engineer 69.2, cybersecurity_analyst 69.2, qa_engineer 62.7. Posisi expected: 1. DIFF=0.
- network_engineer: top5 = network_engineer 70.6, cybersecurity_analyst 70.0, devops_engineer 69.4, backend_developer 67.5, qa_engineer 61.9. Posisi expected: 1. DIFF=0.
- cybersecurity_analyst: top5 = cybersecurity_analyst 70.6, network_engineer 69.4, devops_engineer 68.3, backend_developer 66.7, qa_engineer 65.0. Posisi expected: 1. DIFF=0.
- system_analyst: top5 = system_analyst 74.3, ui_ux_designer 69.7, qa_engineer 69.7, web_developer 65.1, data_analyst 64.6. Posisi expected: 1. DIFF=0.

## B4. Persona Ekstrem

- Semua rating = 5: top5 = web_developer 61.7, mobile_developer 61.7, data_analyst 61.7, ui_ux_designer 61.7, qa_engineer 61.7 (semua skor sama; tie-break sort_order → web_developer #1).
- Semua rating = 1: top5 = web_developer 61.7, mobile_developer 61.7, data_analyst 61.7, ui_ux_designer 61.7, qa_engineer 61.7 (sama; tie-break sort_order).
- Semua rating = 3: top5 = web_developer 61.7, mobile_developer 61.7, data_analyst 61.7, ui_ux_designer 61.7, qa_engineer 61.7 (sama; tie-break sort_order).
- Hanya coding=5, lainnya=1: top5 = web_developer 71.3, mobile_developer 71.3, backend_developer 71.3, devops_engineer 66.3, data_analyst 61.2 (tiga karier dengan coding=5 tie; tie-break sort_order → web_developer #1).

Karier yang "selalu menang" pada kasus ekstrem: web_developer (karena sort_order terendah di antara yang tie).

## B5. Perbandingan SAW vs Euclidean vs Cosine (rank #1)

| Persona | SAW #1 | Euclidean #1 | Cosine #1 | Expected |
|---|---|---|---|---|
| P1 web_developer | mobile_developer | mobile_developer | mobile_developer | web_developer |
| P1 mobile_developer | mobile_developer | mobile_developer | mobile_developer | mobile_developer |
| P1 backend_developer | backend_developer | backend_developer | backend_developer | backend_developer |
| P1 data_analyst | data_analyst | data_analyst | data_analyst | data_analyst |
| P1 ui_ux_designer | ui_ux_designer | ui_ux_designer | ui_ux_designer | ui_ux_designer |
| P1 qa_engineer | qa_engineer | qa_engineer | qa_engineer | qa_engineer |
| P1 devops_engineer | devops_engineer | devops_engineer | devops_engineer | devops_engineer |
| P1 network_engineer | network_engineer | network_engineer | network_engineer | network_engineer |
| P1 cybersecurity_analyst | cybersecurity_analyst | cybersecurity_analyst | cybersecurity_analyst | cybersecurity_analyst |
| P1 system_analyst | system_analyst | system_analyst | system_analyst | system_analyst |
| P2 web_developer | ui_ux_designer | web_developer | web_developer | web_developer |
| P2 mobile_developer | mobile_developer | mobile_developer | mobile_developer | mobile_developer |
| P2 backend_developer | backend_developer | backend_developer | backend_developer | backend_developer |
| P2 data_analyst | data_analyst | data_analyst | data_analyst | data_analyst |
| P2 ui_ux_designer | ui_ux_designer | ui_ux_designer | ui_ux_designer | ui_ux_designer |
| P2 qa_engineer | qa_engineer | qa_engineer | qa_engineer | qa_engineer |
| P2 devops_engineer | devops_engineer | devops_engineer | devops_engineer | devops_engineer |
| P2 network_engineer | network_engineer | network_engineer | network_engineer | network_engineer |
| P2 cybersecurity_analyst | cybersecurity_analyst | cybersecurity_analyst | cybersecurity_analyst | cybersecurity_analyst |
| P2 system_analyst | system_analyst | system_analyst | system_analyst | system_analyst |
| EXT all5 | web_developer | web_developer | web_developer | - |
| EXT all1 | web_developer | web_developer | web_developer | - |
| EXT all3 | web_developer | web_developer | web_developer | - |
| EXT coding5_rest1 | web_developer | web_developer | web_developer | - |

## B6. Kutipan Keputusan & Dokumen Metode

- Alasan dipilih SAW (`docs/SPK-METHOD.md` §1.1 & §1.4): "Kelebihan: transparan, mudah dijelaskan, implementasi sederhana, pengujian mudah." dan "Gunakan SAW sebagai metode utama. Tampilkan gap analysis (selisih rating vs profil) sebagai penjelasan tambahan, bukan sebagai skor utama."
- Metode dikunci oleh keputusan `D-01` (`docs/DECISIONS.md`): "Keputusan final: A, dengan syarat tes persona di Sesi 3 (bukan sesi ini). Status: disetujui (bersyarat)." Opsi A = SAW; opsi B = Profile Matching; opsi C = TOPSIS.
- Asal angka 37 (`docs/SPK-METHOD.md` §6, `docs/DECISIONS.md` D-19/D-22): "Status: DATA AWAL, belum tervalidasi. Total tiap karier = 37 (invarian desain)." D-19: "matriks diseimbangkan (total tiap karier 37)." D-22: "tiga baris matriks diubah (...) total tiap karier tetap 37." Sumber validasi: **TIDAK DITEMUKAN** (belum tervalidasi).

## B7. Status git

- Script diagnostik dibuat di `/tmp/opencode` (di luar repo) dan dihapus setelah eksekusi.
- `git status`: terdapat perubahan `tests/Feature/LandingPageTest.php` (Bagian A) dan file baru `docs/AUDIT_TECHMIND.md`, `docs/DIAGNOSA_SPK.md`. Tidak ada file sementara di repo.

## Kesimpulan Awal (fakta)

1. Matriks profil 10 karier × 12 kriteria semuanya berjumlah 37 (invarian desain), max per kriteria = 5.
2. SAW diimplementasikan sesuai rumus docs: bobot ternormalisasi dari rating×pengali, normalisasi per max antar karier aktif, skor V_i, tie-break sort_order.
3. Dari 20 persona, 18 menghasilkan karier yang diharapkan di rank #1; 2 persona (P1 web_developer dan P2 web_developer) tidak.
4. Pada P1 web_developer, SAW menempatkan mobile_developer #1 (selisih 1.14 poin); Euclidean dan Cosine juga menempatkan mobile_developer #1.
5. Pada P2 web_developer, SAW menempatkan ui_ux_designer #1 (selisih 0.57 poin), sedangkan Euclidean dan Cosine menempatkan web_developer #1.
6. Pada rating seragam (1/3/5), semua karier memiliki skor identik 61.7; tie-break sort_order membuat web_developer selalu #1.
7. Pada kasus hanya coding=5, tiga karier dengan coding=5 tie di 71.3; tie-break sort_order membuat web_developer #1.
8. Metode SAW dipilih karena transparan, mudah dijelaskan, implementasi sederhana, pengujian mudah (docs/SPK-METHOD.md).
9. Keputusan D-01 mengunci SAW sebagai metode utama (status disetujui bersyarat, bergantung tes persona Sesi 3).
10. Angka standar total skor 37 berasal dari penyeimbangan matriks (D-19/D-22); sumber validasi ilmiah **TIDAK DITEMUKAN** di repo.
