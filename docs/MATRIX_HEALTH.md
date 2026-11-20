# MATRIX HEALTH TECHMIND

Tanggal: 2026-11-20. Metode wajib SAW (D-01); rumus `app/Services/Spk/SawCalculator.php` tidak diubah. Masalah ada pada data matriks.

## A. Perbaikan LandingPageTest

- Diff ringkas: `tests/Feature/LandingPageTest.php` — assertion teks client (`Kenali potensimu`/`Kenali potensi Anda`/`Cara kerja TechMind`) diganti dengan `assertInertia(... component('public/landing') ... where('name','TechMind'))`. UI tidak diubah.
- Hasil: `./vendor/bin/pest tests/Feature/LandingPageTest.php` → 3 passed, 18 assertions.

## B. Alat Ukur

File yang dibuat:
- `app/Services/Spk/MatrixHealthAnalyzer.php` (memakai `SawCalculator`; fungsi `selfConsistency`, `nearestNeighbors`, `robustness`, `tieCases`, `analyze`).
- `app/Console/Commands/MatrixHealth.php` (command `techmind:matrix-health`, opsi `--samples=`, `--seed=`, `--json`).
- `tests/Feature/MatrixHealthTest.php` (grup `matrix-health`, tidak masuk `ci:check`).

Cara pakai:
- `php artisan techmind:matrix-health --samples=2000 --seed=42`
- `php artisan techmind:matrix-health --samples=2000 --seed=42 --json`
- `./vendor/bin/pest --group=matrix-health`

## B4. Hasil Kesehatan Matriks SAAT INI

Self-consistency (rating = profil karier sendiri):

| Karier | Rank | Margin (poin) | Runner-up |
|---|---:|---:|---|
| web_developer | 1 | 1.62 | ui_ux_designer |
| mobile_developer | 1 | 4.32 | web_developer |
| data_analyst | 1 | 6.49 | system_analyst |
| ui_ux_designer | 1 | 5.95 | system_analyst |
| qa_engineer | 1 | 0.54 | system_analyst |
| devops_engineer | 1 | 0.54 | cybersecurity_analyst |
| network_engineer | 1 | 0.54 | cybersecurity_analyst |
| cybersecurity_analyst | 1 | 1.62 | devops_engineer |
| system_analyst | 1 | 4.86 | ui_ux_designer |
| backend_developer | 1 | 2.70 | devops_engineer |

Nearest neighbors (min jarak Euclidean per karier): web↔qa 3.742; mobile↔qa 4.243; data↔system 5.657; uiux↔system 4.472; qa↔system 3.162; devops↔network 2.000; devops↔cyber 2.000; network↔cyber 2.000; cyber↔devops 2.000; system↔qa 3.162; backend↔devops 2.449. Jarak terdekat global = 2.000 (devops/network/cyber).

Robustness (noise ±1, 2000 sampel, seed 42):

| Karier | % menang |
|---|---:|
| web_developer | 66.15% |
| mobile_developer | 95.85% |
| data_analyst | 99.60% |
| ui_ux_designer | 100.00% |
| qa_engineer | 46.25% |
| devops_engineer | 23.55% |
| network_engineer | 49.00% |
| cybersecurity_analyst | 78.45% |
| system_analyst | 98.90% |
| backend_developer | 97.50% |

Tie cases (15 kasus: 3 seragam + 12 satu-kriteria-tinggi): maks karier seri di rank #1 = 10 (kasus seragam). `high_coding` seri 3 (web, mobile, backend); `high_attention_detail` seri 3 (qa, network, cyber).

## C1. Usulan Matriks Baru (10×12)

Semua total = 37. Sel yang berubah dari matriks saat ini ditandai **tebal**.

| Karier | C1 | C2 | C3 | C4 | C5 | C6 | C7 | C8 | C9 | C10 | C11 | C12 | TOTAL |
|---|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|
| web_developer | 3 | 2 | 1 | 3 | **2** | 4 | 4 | **5** | 5 | 2 | 2 | 4 | 37 |
| mobile_developer | 4 | 2 | 2 | 4 | **5** | **2** | 3 | 2 | 5 | 1 | 4 | 3 | 37 |
| backend_developer | 5 | 3 | 3 | 4 | 1 | 2 | 1 | **1** | 5 | 4 | **5** | 3 | 37 |
| data_analyst | 4 | 5 | 5 | 3 | 2 | 3 | 2 | 4 | 3 | 1 | 2 | 3 | 37 |
| ui_ux_designer | 3 | 3 | 1 | 3 | 5 | 5 | 5 | 4 | 2 | 1 | 1 | 4 | 37 |
| qa_engineer | 4 | 3 | 1 | 5 | 2 | 3 | 4 | **3** | **5** | 1 | 3 | **3** | 37 |
| devops_engineer | 4 | 3 | 2 | 4 | 1 | 3 | 1 | **2** | **5** | 5 | **2** | **5** | 37 |
| network_engineer | 4 | 2 | **4** | 5 | 1 | 3 | 2 | 3 | **2** | 5 | **2** | **4** | 37 |
| cybersecurity_analyst | **5** | **4** | 2 | 5 | 1 | **2** | 1 | 3 | 3 | 4 | 5 | **2** | 37 |
| system_analyst | 4 | 3 | 2 | 4 | 2 | 3 | 5 | 5 | 1 | 1 | 2 | 5 | 37 |

Kolom: C1 logic_problem_solving, C2 data_analysis, C3 math_statistics, C4 attention_detail, C5 visual_design, C6 creativity, C7 user_empathy, C8 communication, C9 coding, C10 network_infra, C11 security_awareness, C12 business_process.

## C2. Daftar Perubahan Sel

| Karier | Kriteria | Lama → Baru | Alasan domain |
|---|---|---|---|
| web_developer | visual_design (C5) | 3 → 2 | Web developer fokus implementasi; desain visual utama di UI/UX. |
| web_developer | communication (C8) | 4 → 5 | Komunikasi dengan klien/tim produk penting untuk web. |
| mobile_developer | visual_design (C5) | 4 → 5 | UI mobile sangat krusial. |
| mobile_developer | creativity (C6) | 3 → 2 | Mobile lebih implementasi daripada eksplorasi visual. |
| backend_developer | communication (C8) | 2 → 1 | Backend kurang berhadapan langsung dengan klien. |
| backend_developer | security_awareness (C11) | 4 → 5 | Backend bertanggung jawab keamanan API/data. |
| qa_engineer | communication (C8) | 4 → 3 | QA fokus pengujian, bukan presentasi. |
| qa_engineer | coding (C9) | 3 → 5 | Otomasi pengujian membutuhkan coding kuat. |
| qa_engineer | business_process (C12) | 4 → 3 | QA kurang fokus proses bisnis. |
| devops_engineer | communication (C8) | 3 → 2 | DevOps lebih infra/otomasi. |
| devops_engineer | coding (C9) | 4 → 5 | Otomasi CI/CD membutuhkan coding kuat. |
| devops_engineer | security_awareness (C11) | 4 → 2 | Keamanan utama cyber; DevOps fokus deployment. |
| devops_engineer | business_process (C12) | 3 → 5 | DevOps menangani proses rilis/operasi. |
| network_engineer | math_statistics (C3) | 2 → 4 | Analisis trafik/kapasitas jaringan. |
| network_engineer | coding (C9) | 3 → 2 | Network kurang coding. |
| network_engineer | security_awareness (C11) | 4 → 2 | Keamanan utama cyber. |
| network_engineer | business_process (C12) | 3 → 4 | Network mendukung proses operasional. |
| cybersecurity_analyst | logic_problem_solving (C1) | 4 → 5 | Analisis ancaman membutuhkan logika tinggi. |
| cybersecurity_analyst | data_analysis (C2) | 3 → 4 | Analisis log/data keamanan. |
| cybersecurity_analyst | creativity (C6) | 3 → 2 | Fokus analitis, bukan kreativitas. |
| cybersecurity_analyst | business_process (C12) | 3 → 2 | Fokus teknis keamanan. |

## C3. Hasil Kesehatan Matriks USULAN

Self-consistency (rating = profil karier sendiri):

| Karier | Rank | Margin (poin) | Runner-up |
|---|---:|---:|---|
| web_developer | 1 | 3.78 | qa_engineer |
| mobile_developer | 1 | 4.32 | qa_engineer |
| data_analyst | 1 | 6.49 | system_analyst |
| ui_ux_designer | 1 | 5.95 | system_analyst |
| qa_engineer | 1 | 3.78 | web_developer |
| devops_engineer | 1 | 4.32 | backend_developer |
| network_engineer | 1 | 3.24 | devops_engineer |
| cybersecurity_analyst | 1 | 2.70 | backend_developer |
| system_analyst | 1 | 4.86 | ui_ux_designer |
| backend_developer | 1 | 3.78 | cybersecurity_analyst |

Nearest neighbors (min jarak per karier): web↔qa 3.742; mobile↔qa 4.000; data↔system 5.657; uiux↔system 4.472; qa↔web 3.742; devops↔network 4.243; devops↔backend 4.243; network↔devops 4.243; cyber↔backend 3.464; system↔uiux 4.472; backend↔cyber 3.464. Jarak terdekat global = 3.464.

Robustness (noise ±1, 2000 sampel, seed 42):

| Karier | % menang |
|---|---:|
| web_developer | 94.65% |
| mobile_developer | 99.30% |
| data_analyst | 99.60% |
| ui_ux_designer | 99.95% |
| qa_engineer | 96.60% |
| devops_engineer | 98.60% |
| network_engineer | 92.45% |
| cybersecurity_analyst | 93.05% |
| system_analyst | 98.45% |
| backend_developer | 99.70% |

Tie cases (15 kasus): maks karier seri di rank #1 = 10 (kasus seragam, tidak berubah). `high_coding` seri 5 (web, mobile, qa, devops, backend) karena banyak karier coding=5.

## C4. Perbandingan Sebelum vs Sesudah

Margin (poin):

| Karier | Sebelum | Sesudah |
|---|---:|---:|
| web_developer | 1.62 | 3.78 |
| mobile_developer | 4.32 | 4.32 |
| data_analyst | 6.49 | 6.49 |
| ui_ux_designer | 5.95 | 5.95 |
| qa_engineer | 0.54 | 3.78 |
| devops_engineer | 0.54 | 4.32 |
| network_engineer | 0.54 | 3.24 |
| cybersecurity_analyst | 1.62 | 2.70 |
| system_analyst | 4.86 | 4.86 |
| backend_developer | 2.70 | 3.78 |

Robustness (%):

| Karier | Sebelum | Sesudah |
|---|---:|---:|
| web_developer | 66.15% | 94.65% |
| mobile_developer | 95.85% | 99.30% |
| data_analyst | 99.60% | 99.60% |
| ui_ux_designer | 100.00% | 99.95% |
| qa_engineer | 46.25% | 96.60% |
| devops_engineer | 23.55% | 98.60% |
| network_engineer | 49.00% | 92.45% |
| cybersecurity_analyst | 78.45% | 93.05% |
| system_analyst | 98.90% | 98.45% |
| backend_developer | 97.50% | 99.70% |

Jarak terdekat global: sebelum 2.000, sesudah 3.464.

## D. Status

- `composer ci:check`: **lulus** (Pint pass, TypeScript pass, Vite build pass, Pest 60 passed, 0 failed, 848 assertions; grup `fairness` dan `matrix-health` dikecualikan).
- `git status`: terdapat perubahan `composer.json`, `tests/Feature/LandingPageTest.php`, plus file baru `app/Console/Commands/MatrixHealth.php`, `app/Services/Spk/MatrixHealthAnalyzer.php`, `tests/Feature/MatrixHealthTest.php`, `docs/AUDIT_TECHMIND.md`, `docs/DIAGNOSA_SPK.md`, `docs/MATRIX_HEALTH.md`. Tidak ada file sementara di repo. Usulan matriks belum diterapkan ke seeder/database.

## E. Matriks FINAL (D-23)

Matriks FINAL (lihat `docs/SPK-METHOD.md` §6 / D-23) diterapkan ke `database/seeders/data/career_profiles.php`. Hasil `php artisan techmind:matrix-health --samples=2000 --seed=42`:

Self-consistency: semua karier rank 1; margin min 2.16 (web_developer, devops_engineer).

Robustness: min 81.05% (qa_engineer); semua karier ≥ 80%.

Nearest neighbors: jarak terdekat 3.162 (web_developer↔qa_engineer); semua ≥ 3.0.

Tie cases: maks seri di rank #1 = 10 (kasus seragam, tidak berubah); `high_coding` seri 3 (web, mobile, backend).
