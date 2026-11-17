# ROADMAP

Maksimal 8 sesi implementasi. Setiap sesi memiliki tujuan, file yang disentuh, kriteria selesai, dan test wajib.

## Sesi 1 — Fondasi Proyek

Status: selesai (bukti: Docker MySQL healthy di port 3309; migrate:fresh --seed sukses; composer ci:check lulus: 43 passed, 2 skipped; npm run build sukses).
- Tujuan: inisialisasi Laravel 13 + Inertia React TS + Tailwind + Pest; konfigurasi dasar.
- File: `composer.json`, `package.json`, `vite.config.*`, `tailwind.config.*`, `phpunit.xml`, `.env.example`.
- Kriteria selesai: `php artisan serve` dan `npm run dev` jalan; route `/` menampilkan halaman Inertia.
- Test: Pest smoke test route `/` (200).
- Branch: boleh digabung dengan Sesi 2 (`feat/foundation`).

## Sesi 2 — Database & Model & Repository
- Tujuan: migrasi, model, repository, seeder/factory dasar.
- File: `database/migrations/*`, `app/Models/*`, `app/Repositories/*`, `database/seeders/*`, `database/factories/*`.
- Kriteria selesai: `php artisan migrate:fresh --seed` sukses; repository dasar tersedia.
- Test: migration test, factory test, repository unit/feature.
- Branch: `feat/foundation`.

## Sesi 3 — Kuesioner Publik
- Tujuan: alur sesi tes anonim, langkah, validasi, penyimpanan jawaban.
- File: `app/Http/Controllers/Public/TestController.php`, `app/Services/QuestionnaireService.php`, `resources/js/Pages/Test/Show.tsx`, komponen terkait.
- Kriteria selesai: pengunjung dapat mulai tes, isi 12 kriteria, submit.
- Test: feature tes mulai/submit, validasi skala 1-5, token unik.
- Branch: boleh digabung dengan Sesi 4 (`feat/questionnaire-spk`).

## Sesi 4 — Perhitungan SPK & Hasil
- Tujuan: implementasi SAW, ranking, halaman hasil, radar.
- File: `app/Services/SpkCalculationService.php`, `RecommendationService.php`, `ResultController.php`, `Pages/Result/Show.tsx`, `RadarProfileChart.tsx`.
- Kriteria selesai: hasil ranking tampil, radar tampil, detail karier tampil.
- Test: unit SAW fixture, feature hasil.
- Branch: `feat/questionnaire-spk`.

## Sesi 5 — AI Explanation + Fallback + Cache + Rate Limit
- Tujuan: abstraksi provider, fallback template, logging, cache, throttle.
- File: `Contracts/AiExplanationProvider.php`, providers, `AiExplanationService.php`, middleware throttle.
- Kriteria selesai: penjelasan tampil; jika API gagal, fallback tampil; log tersimpan.
- Test: unit fallback, feature AI timeout (mock).
- Branch: boleh digabung dengan Sesi 6 (`feat/result-ai-pdf`).

## Sesi 6 — PDF + Feedback
- Tujuan: ekspor PDF dan form penilaian pengunjung.
- File: `PdfController.php`, `PdfExportService.php`, `FeedbackController.php`, template Blade PDF.
- Kriteria selesai: PDF terunduh, feedback tersimpan dan tervalidasi.
- Test: feature PDF response, feedback validation.
- Branch: `feat/result-ai-pdf`.

## Sesi 7 — Admin CRUD & Statistik
- Tujuan: login admin, CRUD kriteria/karier/profil, statistik, ekspor feedback.
- File: `Admin/*Controller`, `Pages/Admin/*`, services admin.
- Kriteria selesai: admin dapat kelola data dan melihat statistik.
- Test: auth, CRUD, statistik, export.
- Branch: boleh digabung dengan Sesi 8 (`feat/admin-demo`).

## Sesi 8 — Demo Mode, Polish, Uji Stabilitas
- Tujuan: seeder demo, akun admin demo, polish UI, uji beban/koneksi, checklist deploy.
- File: seeder, factories, docs deployment, cache/rate limit final.
- Kriteria selesai: `migrate:fresh --seed` menghasilkan demo lengkap; suite test hijau; lint/typecheck hijau.
- Test: feature end-to-end, `pest`, `npm run build`.
- Branch: `feat/admin-demo`.

## Catatan Branch

- Sesi 1+2: `feat/foundation`
- Sesi 3+4: `feat/questionnaire-spk`
- Sesi 5+6: `feat/result-ai-pdf`
- Sesi 7+8: `feat/admin-demo`
- Semua branch dari `docs/initial-planning` atau `main` sesuai keputusan tim.

## Catatan Sesi 3b

- Matrix tuning D-22 diterapkan; simulasi skenario uniform/typical/peaky ditambahkan.

## Catatan Audit Sesi 3c

- Audit SPK selesai; laporan di `docs/audit/SESI-3C-SPK-AUDIT.md`.
- Tidak ada perubahan matriks, formula, bobot, atau persona.

## Catatan Sesi 4.1

- Landing page publik selesai: navigasi, hero, value proposition, career grid, cara kerja, metode SAW, FAQ, footer.
- CTA sementara mengarah ke anchor `#cara-kerja` karena questionnaire belum tersedia.

## Catatan Sesi 4.2

- Alur tes publik selesai: mulai sesi anonim, kuesioner 3 langkah, submit jawaban, snapshot jawaban dan hasil.
- Halaman hasil selesai: ranking 10 karier, radar profil, gap analysis, penjelasan template, detail karier.
- Belum: ekspor PDF, feedback pengunjung, integrasi AI nyata, admin CRUD.
