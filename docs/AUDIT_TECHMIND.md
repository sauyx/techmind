# AUDIT TEKNIS TECHMIND

Tanggal audit: 2026-11-20  
Mode: READ-ONLY (tidak ada perubahan kode, kecuali file laporan ini).

## 1. RINGKASAN PROJECT

- **Tujuan sistem (menurut README/docs):** Aplikasi web rekomendasi karier IT berbasis SPK metode SAW. Pengunjung mengisi kuesioner minat/skill skala 1–5; sistem menghitung ranking 10 jalur karier IT, menampilkan skor kecocokan, grafik radar, detail karier (deskripsi, skill, tools, roadmap), penjelasan hasil (AI dengan fallback template), riwayat via token unik, ekspor PDF, form penilaian pengunjung, panel admin CRUD referensi, statistik, ekspor CSV, mode demo pameran, QR booth, halaman error kustom. Sumber: `README.md`, `docs/SPEC-TECHMIND.md`, `docs/SPK-METHOD.md`.
- **Target pengguna & role:**
  - Pengunjung anonim (mahasiswa/calon mahasiswa), tanpa login; nama opsional. Sumber: `app/Http/Controllers/Public/TestController.php`, `app/Services/TestSubmissionService.php`.
  - Admin tunggal berbasis kolom `is_admin` + middleware `EnsureUserIsAdmin`. Sumber: `app/Models/User.php`, `app/Http/Middleware/EnsureUserIsAdmin.php`, `database/migrations/2026_11_17_024215_add_is_admin_to_users_table.php`.
  - Role pakar/validasi data: **TIDAK DITEMUKAN** di kode.
- **Estimasi progress real:** ±70–75%. Alasan: alur inti publik (tes → hasil → PDF → feedback), SPK SAW, AI+fallback, snapshot, admin CRUD referensi, statistik, booth QR, demo seeder, error pages, dan test sebagian besar lulus; namun masih ada test gagal (3), dokumentasi arsitektur/DB tidak sinkron, beberapa fitur dokumen (cache, retry AI, logging, user management, delete CRUD, settings) belum terimplementasi, dan matriks data belum tervalidasi.

## 2. TECH STACK

- **Backend:** PHP 8.4 (lingkungan; `composer.json` menetapkan `php: ^8.3`), Laravel Framework `^13.17`, Inertia Laravel `^3.0`, Fortify `^1.37.2`, DomPDF `^3.1`, Wayfinder `^0.1.14`, Chisel `^0.1.0`.
- **Frontend:** Inertia.js React `^3.0.0`, React `19.2.0`, TypeScript `5.7.2`, Vite `npm:@voidzero-dev/vite-plus-core@1.4.0`, Tailwind CSS `4.0.0`, `@tailwindcss/vite ^4.1.11`, Radix UI, lucide-react, recharts `^3.10.1`, qrcode `^1.5.4`, sonner, clsx/tailwind-merge, `@fontsource/inter`.
- **Database & ORM:** MySQL 8.4 (port host 3309 via Docker), Eloquent ORM. Testing juga SQLite in-memory dan MySQL testing (`phpunit.mysql.xml`).
- **Library lain:** Laravel Fortify (auth), Laravel Wayfinder (route helpers), Pest `^5.3` + plugin Laravel, Pint, PHPStan/Larastan, Mockery, Faker.
- **Cara menjalankan:**
  - Dev: `composer dev` (atau `php artisan serve` + `npm run dev`), HMR via Vite.
  - Build: `npm run build` (juga `npm run typecheck`/`tsc --noEmit`).
  - Test: `./vendor/bin/pest` (SQLite) atau `composer test:mysql` (MySQL), `composer ci:check`, `composer ci:check:full`.
  - Setup: `composer setup` (install, env, key, migrate, npm install, build).
- **Variabel env yang dibutuhkan (nama saja, tanpa nilai):** `APP_NAME`, `APP_ENV`, `APP_KEY`, `APP_DEBUG`, `APP_URL`, `APP_LOCALE`, `APP_FALLBACK_LOCALE`, `APP_TIMEZONE`, `LOG_CHANNEL`, `LOG_STACK`, `LOG_LEVEL`, `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `SESSION_DRIVER`, `SESSION_LIFETIME`, `SESSION_ENCRYPT`, `SESSION_PATH`, `SESSION_DOMAIN`, `BROADCAST_CONNECTION`, `FILESYSTEM_DISK`, `QUEUE_CONNECTION`, `CACHE_STORE`, `CACHE_PREFIX`, `REDIS_CLIENT`, `REDIS_HOST`, `REDIS_PASSWORD`, `REDIS_PORT`, `MAIL_MAILER`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`, `AI_DRIVER`, `AI_BASE_URL`, `AI_API_KEY`, `AI_MODEL`, `AI_TIMEOUT`, `ADMIN_EMAIL`, `ADMIN_PASSWORD`, `DB_ROOT_PASSWORD`, `DB_TEST_DATABASE`, `TECHMIND_DEMO_MODE`.

## 3. STRUKTUR FOLDER

```
app/
  Actions/Fortify/          action Fortify (CreateNewUser, ResetUserPassword)
  Concerns/                 trait validasi password/profil
  Console/Commands/         techmind:rank, techmind:simulate-ranking, techmind:seed-demo
  Contracts/                interface repository/AI
  Data/                     DTO AiExplanationResult
  Exceptions/               InvalidRatingsException
  Http/Controllers/         Public, Admin, Settings
  Http/Middleware/          EnsureUserIsAdmin, HandleAppearance, HandleInertiaRequests
  Http/Requests/            form request publik/admin/settings
  Models/                   Eloquent models
  Providers/                AppServiceProvider, FortifyServiceProvider
  Repositories/             akses DB
  Services/                 Spk, AI, PDF, admin, presentasi, submission
bootstrap/                  bootstrap app & providers
config/                     konfigurasi Laravel + techmind.php
database/
  factories/                factory
  migrations/               skema DB
  seeders/                  AdminSeeder, ReferenceDataSeeder, DemoSeeder + data/*.php
docs/                       spesifikasi, keputusan, metode, DB, arsitektur, UI/UX, deploy, audit
public/                     aset publik + build Vite
resources/
  css/                      Tailwind 4 (app.css)
  js/                       React pages/components/layouts/hooks/types
  views/                    Blade root, error pages, pdf/result.blade.php
routes/                     web.php, settings.php (tidak terdaftar), console.php
tests/                      Feature + Unit (Pest)
```

## 4. DATABASE / MODEL DATA

Tabel (sesuai migrasi aktual):

- `users`: id, name, email (unique), email_verified_at, password, two_factor_*, remember_token, is_admin, timestamps. Relasi: admin only (tidak ada relasi ke sesi tes).
- `password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `passkeys` (starter kit).
- `criteria`: id, code (unique), name, description, weight (decimal 5,2 default 1.00), display_order, step_group, is_active, timestamps. Index is_active+display_order.
- `careers`: id, code (unique), name, summary, description, skills/tools/roadmap (json), sort_order, is_active, is_validated, source_note, timestamps.
- `career_profiles`: id, career_id (FK careers cascade), criterion_id (FK criteria cascade), score (tinyint 1–5), timestamps; unique (career_id,criterion_id).
- `test_sessions`: id, token (varchar 36 unique), visitor_name (nullable), completed_at, timestamps. (Tidak ada status/ip_hash/user_agent_hash seperti docs lama.)
- `test_answers`: id, test_session_id (FK cascade), criterion_id (FK criteria restrict), criterion_code, criterion_name, rating (1–5), multiplier_snapshot (decimal 4,2), timestamps; unique (test_session_id,criterion_id).
- `test_results`: id, test_session_id (FK cascade), career_id (FK careers restrict), career_code, career_name, rank, saw_score (decimal 8,6), match_percentage (decimal 5,1), contribution_breakdown (json), gap_summary (json nullable), timestamps.
- `ai_explanations`: id, test_session_id (FK unique cascade), driver_used, model_name, content, is_fallback, latency_ms, timestamps.
- `visitor_feedbacks`: id, test_session_id (FK unique cascade), score (0–100), comment, suggestion, timestamps.

ERD teks:

`users` (admin) terpisah; `careers 1—N career_profiles N—1 criteria`; `test_sessions 1—N test_answers`; `test_sessions 1—N test_results` (10 baris per sesi); `test_sessions 1—1 ai_explanations`; `test_sessions 1—1 visitor_feedbacks`.

Seeder/data awal:
- `ReferenceDataSeeder`: 12 kriteria, 10 karier, 120 profil (12×10), idempoten. Sumber: `database/seeders/data/criteria.php`, `careers.php`, `career_profiles.php`.
- `AdminSeeder`: akun admin dari `ADMIN_EMAIL`/`ADMIN_PASSWORD`, `is_admin=true`, email verified.
- `DemoSeeder`: 18 sesi demo (penjelasan template + feedback) jika bukan production tanpa demo mode.
- Factory: `UserFactory`, `CareerFactory`, `CareerProfileFactory`, `CriterionFactory`, `TestAnswerFactory`, `TestResultFactory`, `TestSessionFactory`.

Catatan: `docs/DATABASE.md` dan `docs/ARCHITECTURE.md` sebagian tidak sinkron dengan implementasi aktual (mis. skema `test_results` lama berbasis `ranking` JSON/top_career_slug, nama kolom `weight_snapshot`, field `status/ip_hash`, controller/service yang disebut belum ada).

## 5. LOGIKA SPK (PALING PENTING)

- **Metode:** SAW (Simple Additive Weighting) dengan pengali admin. Implementasi: `app/Services/Spk/SawCalculator.php` (dipanggil `RecommendationService`, `TestSubmissionService`). Dokumen: `docs/SPK-METHOD.md`.
- **Kriteria (12):** `logic_problem_solving`, `data_analysis`, `math_statistics`, `attention_detail`, `visual_design`, `creativity`, `user_empathy`, `communication`, `coding`, `network_infra`, `security_awareness`, `business_process`. Bobot awal semua `1.00` dari kolom `criteria.weight` (DB), dapat diubah admin (0.50–2.00) sebagai pengali. Sumber: `database/seeders/data/criteria.php`, `app/Services/Admin/CriteriaManagementService.php`.
- **Alternatif (10):** Web Developer, Mobile Developer, Backend Developer, Data Analyst, UI/UX Designer, QA Engineer, DevOps Engineer, Network Engineer, Cybersecurity Analyst, System Analyst. Sumber: `database/seeders/data/careers.php`.
- **Pertanyaan kuesioner:** 12 item (kriteria itu sendiri), skala 1–5 (Likert), dibagi 3 langkah × 4 kriteria. Pemetaan pertanyaan → kriteria langsung 1:1 (kode kriteria). Sumber: `app/Http/Controllers/Public/TestController.php`, `resources/js/pages/public/test.tsx`.
- **Alur perhitungan (inti):**
  1. Rating pengguna `r_j` (1–5) + pengali admin `m_j`.
  2. Validasi multiplier (0.5–2.0), rating (1–5), semua kriteria aktif wajib ada, tidak ada kode asing, ada karier aktif. Sumber: `app/Services/Spk/SawCalculator.php` (validasi awal).
  3. Max per kriteria di antara karier aktif: `max_i(x_ij)` (fallback 1 jika 0).
  4. Bobot ternormalisasi: `w_j = (r_j * m_j) / Σ(r_k * m_k)` (error jika denominator ≤ 0).
  5. Normalisasi matriks: `r_ij = x_ij / max_i(x_ij)`.
  6. Skor: `V_i = Σ w_j * r_ij`; display = `V_i * 100` (1 desimal).
  7. Ranking descending dengan tie-break `sort_order` karier asc jika `|V_a-V_b| < 1e-9`.
  - Kutipan kode inti:
    - Bobot: `$weightedRatings[$code] = ((float) $ratings[$code]) * (float) $activeCriteria[...]['multiplier'];` lalu `$weights[$code] = $weightedRatings[$code] / $denominator;`
    - Normalisasi/skor: `$norm = $raw / $maxByCriterion[$code]; $contribution = $weights[$code] * $norm; $total += $contribution;`
    - Tie-break: `if (abs($a['score'] - $b['score']) >= self::TIE_EPSILON) { ... } return $a['sort_order'] <=> $b['sort_order'];`
- **Kebenaran matematis:** Rumus sesuai docs dan SAW kanonik (dengan pengali). Semua kriteria benefit; karier nonaktif dikecualikan dari max dan ranking; pembagian nol ditangani; validasi rentang ada. Snapshot jawaban/bobot/profil/skor disimpan. Catatan: SAW tidak menjamin karier yang profilnya sama persis dengan rating pengguna selalu rank #1 (karena normalisasi per kriteria berbasis max antar karier); ini sifat metode, bukan bug. Tes fairness (`tests/Feature/FairnessTest.php`) sebagian gagal untuk persona `web_developer` (ranked `mobile_developer`/`ui_ux_designer`), namun grup `fairness` dikecualikan dari CI. Gap analysis tambahan threshold gap ≥ 2 sudah sesuai docs.
- **Penyimpanan hasil:** Ya. `EloquentTestSessionRepository::createWithSnapshot` (transaksi) menyimpan `test_sessions`, 12 `test_answers` (snapshot `criterion_code/name/rating/multiplier_snapshot`), dan 10 `test_results` (skor, kontribusi, gap). `ai_explanations` dan `visitor_feedbacks` disimpan saat hasil dibuka/feedback. Riwayat hanya via token UUID (tidak ada daftar sesi publik/admin per pengunjung).

## 6. FITUR & HALAMAN

| No | Fitur/Halaman | Role | Route/URL | File terkait | Status | Catatan |
|---|---|---|---|---|---|---|
| 1 | Landing page | Publik | `/` | `resources/js/pages/public/landing.tsx`, `app/Http/Controllers/Controller.php` (Inertia) | Selesai | CTA ke `/tes` |
| 2 | Register | Publik | `/register` | `app/Actions/Fortify/CreateNewUser.php` | Belum/Tidak tersedia | Route tidak terdaftar (404); sesuai D-18 |
| 3 | Login | Publik/Admin | `/login` | `resources/js/pages/auth/login.tsx`, Fortify | Selesai | Throttle login |
| 4 | Logout | Auth | `POST /logout` | Fortify | Selesai |  |
| 5 | Dashboard user (starter) | Auth verified | `/dashboard` | `resources/js/pages/dashboard.tsx` | Sebagian | Halaman starter, bukan dashboard bisnis |
| 6 | Kuesioner publik | Publik | `/tes` (GET/POST) | `app/Http/Controllers/Public/TestController.php`, `resources/js/pages/public/test.tsx` | Selesai | 3 langkah, progress, validasi, low variance |
| 7 | Halaman hasil | Publik | `/hasil/{token}` | `app/Http/Controllers/Public/ResultController.php`, `resources/js/pages/public/result.tsx` | Selesai | Ranking, radar, gap, AI, feedback |
| 8 | Ekspor PDF | Publik | `/hasil/{token}/pdf` | `app/Services/Pdf/PdfExportService.php`, `resources/views/pdf/result.blade.php` | Selesai | DomPDF server-side |
| 9 | Feedback pengunjung | Publik | `POST /hasil/{token}/feedback` | `app/Services/VisitorFeedbackService.php` | Selesai | Skor 0–100 + komentar/saran |
| 10 | Profil settings | Auth | `/settings/profile` (tidak terdaftar) | `app/Http/Controllers/Settings/ProfileController.php`, `resources/js/pages/settings/profile.tsx` | Rusak/Tidak terhubung | `routes/settings.php` tidak dimuat di `bootstrap/app.php` |
| 11 | Security settings | Auth verified | `/settings/security` (tidak terdaftar) | `app/Http/Controllers/Settings/SecurityController.php` | Rusak/Tidak terhubung | Termasuk passkey/2FA UI (fitur dimatikan) |
| 12 | Dashboard admin | Admin | `/admin` | `app/Http/Controllers/Admin/DashboardController.php`, `resources/js/pages/admin/dashboard.tsx` | Selesai | KPI + chart + feedback terbaru |
| 13 | CRUD kriteria | Admin | `/admin/criteria` (GET/PUT) | `app/Http/Controllers/Admin/CriterionController.php`, `resources/js/pages/admin/criteria/index.tsx` | Sebagian | Hanya update; tidak ada create/delete |
| 14 | CRUD karier | Admin | `/admin/careers` (index/create/store/edit/update) | `app/Http/Controllers/Admin/CareerController.php`, `resources/js/pages/admin/careers/*.tsx` | Sebagian | Tidak ada delete/deactivate route; status via is_active |
| 15 | Edit profil karier (matriks) | Admin | Dalam form karier | `app/Services/Admin/CareerManagementService.php` | Selesai | Skor 1–5 semua kriteria aktif |
| 16 | Kelola user | Admin | — | — | TIDAK DITEMUKAN |  |
| 17 | Laporan/export | Admin | `/admin/feedbacks/export` | `app/Http/Controllers/Admin/FeedbackExportController.php` | Selesai | CSV UTF-8 BOM |
| 18 | Booth QR | Admin | `/admin/booth` | `app/Http/Controllers/Admin/BoothController.php`, `resources/js/pages/admin/booth.tsx` | Selesai | QR lokal (library qrcode) |
| 19 | Mode demo | Admin/Publik | Flag `demo_mode` | `config/techmind.php`, `database/seeders/DemoSeeder.php` | Selesai | Tombol simulasi hanya jika demo mode |
| 20 | Halaman error kustom | Publik | 404/500 | `resources/views/errors/404.blade.php`, `500.blade.php` | Selesai |  |
| 21 | Admin analytics/statistik terpisah | Admin | — | — | TIDAK DITEMUKAN | Statistik hanya di dashboard |

## 7. API / ROUTES

Semua route berbasis web (Inertia), bukan API terpisah.

Publik:
- `GET /` → `Inertia\Controller` (landing), name `home`.
- `GET /tes` → `Public\TestController@create`, name `tests.create`.
- `POST /tes` → `Public\TestController@store`, middleware `throttle:30,1`.
- `GET /hasil/{token}` → `Public\ResultController@show`, name `results.show`.
- `GET /hasil/{token}/pdf` → `Public\ResultController@downloadPdf`, middleware `throttle:20,1`.
- `POST /hasil/{token}/feedback` → `Public\ResultController@storeFeedback`, middleware `throttle:20,1`.

Admin (middleware `auth, verified, admin`, prefix `/admin`):
- `GET /admin` → `Admin\DashboardController@index` (`admin.dashboard`).
- `GET /admin/criteria` → `Admin\CriterionController@index` (`admin.criteria.index`).
- `PUT /admin/criteria/{criterion}` → `Admin\CriterionController@update` (`admin.criteria.update`).
- `GET /admin/careers`, `/create`, `/{career}/edit` → `Admin\CareerController`.
- `POST /admin/careers` → store; `PUT /admin/careers/{career}` → update.
- `GET /admin/feedbacks/export` → `Admin\FeedbackExportController@export`.
- `GET /admin/booth` → `Admin\BoothController@index`.

Auth (Fortify):
- `GET/POST /login`, `POST /logout`, plus route confirm-password/confirmed-password-status (meskipun fitur sebagian dimatikan).
- `settings/*` (dari `routes/settings.php`) **TIDAK terdaftar** di `bootstrap/app.php` (tidak muncul di `route:list`).

## 8. UI/UX AUDIT

- **Design system:** Tailwind CSS 4, font Inter (fontsource, lokal), aksen indigo `#4F46E5` (D-13), netral zinc/slate, CSS variables di `resources/css/app.css`, komponen shadcn/Radix di `resources/js/components/ui/*`, ikon lucide-react, toast sonner.
- **Konsistensi:** Halaman publik/admin memakai pola kartu, border zinc, tipografi serupa; admin layout konsisten. Brand color indigo dipakai berulang.
- **Responsiveness:** Grid responsif (`sm:`, `md:`, `lg:`), tabel dengan `overflow-x-auto` dan `min-w`, sticky progress bar. Mobile-first cukup baik. Booth QR 56×56 px.
- **Pengalaman kuesioner:** Progress bar + persentase terjawab, 3 langkah, tombol Kembali/Lanjut, validasi per langkah (semua item langkah wajib terjawab), peringatan low variance, input nama opsional, tombol simulasi (demo mode). Tidak ada penyimpanan sementara/autosave (refresh akan menghapus jawaban client-side). Sumber: `resources/js/pages/public/test.tsx`.
- **Penyajian hasil:** Ranking 10 karier, radar profil pengguna vs profil ideal (recharts), skor kecocokan, gap analysis (kekuatan/area penguatan/potensi kurang dimanfaatkan), penjelasan AI (label "Analisis AI" vs "Analisis Ringkas"), detail karier tab (analisis, deskripsi, skill/tools, roadmap), feedback widget 0–100, copy token, download PDF. Sumber: `resources/js/pages/public/result.tsx`.
- **Masalah UX yang ditemukan:** tidak ada autosave; sinkron AI pada GET hasil bisa memperlambat; settings pages tidak reachable; data matriks ditandai belum tervalidasi namun UI menampilkan "Tervalidasi/Belum Tervalidasi"; beberapa teks test stale ("Kenali potensimu").

## 9. KUALITAS KODE & KEAMANAN

- **Duplikasi:** `SHORT_LABELS` duplikat di `app/Services/ResultPresentationService.php` dan `app/Services/Pdf/RadarSvgGenerator.php`; logika gap dianalisis ulang di `TestSubmissionService` per karier. 
- **File tidak terpakai/dead code:** `routes/settings.php` dan controller/settings pages, `app/Actions/Fortify/CreateNewUser.php`, `ResetUserPassword.php`, komponen passkey, sebagian view Fortify; `resources/js/pages/dashboard.tsx` starter.
- **TODO/FIXME:** **TIDAK DITEMUKAN** (grep TODO/FIXME/XXX/HACK/TBD pada app/resources/routes/config/database/tests = 0).
- **Validasi input:** `StoreTestRequest` (ratings integer 1–5), `StoreVisitorFeedbackRequest` (0–100), `UpdateCriterionRequest` (weight 0.5–2.0), `Store/UpdateCareerRequest` (code unique, skor 1–5), service-level validasi SawCalculator. Good.
- **Proteksi role:** middleware `auth, verified, admin`; non-admin → 403, guest → redirect login. Sumber: `app/Http/Middleware/EnsureUserIsAdmin.php`, tests `AdminPanelTest`, `ScaffoldTest`.
- **Hashing password:** cast `password => hashed` di `app/Models/User.php`; AdminSeeder `Hash::make`.
- **CSRF:** middleware web standar Laravel (CSRF aktif). 
- **SQL injection:** Eloquent/parameterized; tidak ada raw query dengan input user (kecuali DB::statement migrasi statis).
- **XSS:** React escaping; Blade PDF menggunakan `{{ }}` (escaped) kecuali SVG base64 yang dibangkitkan server.
- **Secret ter-hardcode:** **TIDAK DITEMUKAN**; secret dari env (`.env` tidak di-commit, lihat `.gitignore`).
- **Error handling & logging:** AI gagal → fallback template + hasil disimpan; DB readiness dicek di booth. **TIDAK DITEMUKAN** pemanggilan logger/Log:: secara eksplisit di app (audit manual).
- **Testing:** Pest 65 test; terakhir dijalankan: 62 passed, 3 failed, 975 assertions. Pint lulus, TypeScript lulus, Vite build lulus. PHPStan (level 7) tidak dijalankan dalam audit ini (konfig ada).

## 10. BUG & MASALAH YANG TERDETEKSI

| No | Masalah | File:baris | Tingkat | Dampak |
|---|---|---|---|---|
| 1 | Test landing mengharapkan teks "Kenali potensimu", UI menampilkan "Kenali potensi Anda" | `tests/Feature/LandingPageTest.php:29` | Tinggi | CI (`composer ci:check`) gagal |
| 2 | Persona fairness `web_developer` (dua varian) tidak menempati rank #1 | `tests/Feature/FairnessTest.php:58`, `:95` | Sedang | Grup `fairness` gagal; indikasi ekspektasi/matriks |
| 3 | `routes/settings.php` tidak dimuat di `bootstrap/app.php`, sehingga settings pages tidak reachable | `bootstrap/app.php:12-14` | Sedang | Dead code; admin tidak bisa ubah profil/password via UI |
| 4 | Dokumentasi arsitektur/DB tidak sinkron dengan kode (skema `test_results`, nama kolom, daftar controller/service) | `docs/ARCHITECTURE.md`, `docs/DATABASE.md` | Sedang | Rujukan pengembang menyesatkan |
| 5 | FortifyServiceProvider mendaftarkan view registration/reset/verify/2FA/passkey meski D-18 menonaktifkan fitur | `app/Providers/FortifyServiceProvider.php:44-76` | Rendah | Dead code, potensi kebingungan |
| 6 | `TestSession` model/migrasi tidak memiliki status/ip_hash/user_agent_hash yang dijanjikan docs (retensi/privasi) | `app/Models/TestSession.php:12-18`, `database/migrations/2026_11_17_035106_create_test_sessions_table.php` | Sedang | Retensi/privasi belum terimplementasi |
| 7 | Tidak ada cache (kriteria/karier/profil/AI) padahal docs menjanjikan | `app/Repositories/*`, `docs/ARCHITECTURE.md §11` | Sedang | Risiko beban saat pameran |
| 8 | OpenAI explainer tidak retry dan tidak menyimpan `error_message` ke DB | `app/Services/Ai/OpenAiCompatibleExplainer.php`, `app/Services/Ai/ExplanationService.php` | Sedang | Observabilitas AI terbatas |
| 9 | Hasil AI dibangkitkan sinkron saat GET `/hasil/{token}` | `app/Http/Controllers/Public/ResultController.php:21`, `app/Services/ResultPresentationService.php:114` | Sedang | Latensi halaman hasil |
| 10 | `AdminSeeder` membaca `env()` langsung, bukan config | `database/seeders/AdminSeeder.php:10-11` | Rendah | Kurang konsisten dengan pola config |
| 11 | Indikator total skor karier: standar 37 vs maksimum 60 (12×5) membingungkan di UI | `resources/js/pages/admin/careers/index.tsx:86-95`, `app/Services/Admin/CareerManagementService.php:19` | Rendah | Potensi kesalahan input admin |

## 11. YANG BELUM DIKERJAKAN (SISA ±30%)

- Cache referensi (kriteria/karier/profil) dan invalidasi saat admin update.
- Retry AI + timeout + logging panggilan (`ai_explanations` dengan error_message/status) sesuai docs.
- Manajemen user admin (CRUD/role) dan penghapusan karier/kriteria (saat ini hanya update).
- Registrasi route settings atau penghapusan kode settings yang tidak terpakai.
- Penyimpanan sementara/autosave jawaban kuesioner.
- Daftar riwayat hasil (publik/admin) berdasarkan token/sesi.
- Halaman statistik terpisah dan manajemen feedback (bukan hanya export).
- Validasi matriks profil karier dengan sumber resmi (roadmap/SFIA/lowongan) dan pencatatan sumber.
- Perbaikan test yang gagal dan sinkronisasi dokumentasi dengan kode.
- Pengujian beban/mobile/browser target (D-17? terbuka) dan audit aset runtime (sudah 0 CDN menurut roadmap).
- Retensi/privasi data (status sesi, ip_hash, user_agent_hash, retensi 90 hari) bila diinginkan.

## 12. CATATAN TAMBAHAN

- Arsitektur berlapis (Migration → Model → Repository → Service → Controller → Route → Inertia → React → Test) diikuti dengan baik; repository binding di `app/Providers/AppServiceProvider.php`.
- `composer ci:check` (pint + tsc + build + pest non-fairness) saat ini **gagal** karena `LandingPageTest` (teks stale); `npm run build`, `npm run typecheck`, `pint --test` lulus.
- Data matriks dinyatakan "DATA AWAL, belum tervalidasi" di `docs/SPK-METHOD.md`; jangan diklaim sebagai sumber resmi.
- Tidak ada perubahan kode yang dilakukan selama audit ini; hanya laporan ini yang ditambahkan.
