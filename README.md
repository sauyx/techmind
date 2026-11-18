# TechMind

Aplikasi web rekomendasi karier IT berbasis Sistem Penunjang Keputusan (SPK) dengan metode SAW. Pengunjung mengisi kuesioner minat dan skill (skala 1–5), sistem menghitung ranking jalur karier IT secara transparan, menampilkan profil radar, detail karier, dan penjelasan hasil dalam bahasa natural. Dirancang untuk demo booth kampus (mata kuliah Digital Kreatif, Prodi Sistem Informasi) dengan durasi 2–3 menit per pengunjung melalui scan QR di HP.

## Fitur

- Landing page + mulai tes tanpa login; sesi anonim (nama/email opsional).
- Kuesioner 12 kriteria skala 1–5, dibagi 3 langkah (4 kriteria per langkah).
- Perhitungan SPK (SAW) menghasilkan ranking karier + skor, disertai bobot dan detail transparan.
- Halaman hasil: ranking, grafik radar profil pengguna vs profil karier, detail karier (deskripsi, skill, tools, roadmap).
- Penjelasan hasil oleh AI dengan fallback template offline (AI tidak menentukan ranking).
- Riwayat hasil via token unik + ekspor PDF (DomPDF, server-side).
- Form penilaian pengunjung (0–100, tanggapan, saran).
- Panel admin: CRUD kriteria/bobot/karier/profil, statistik, ekspor tanggapan CSV.
- Mode demo: seeder data contoh, akun admin demo, tombol simulasi cepat, halaman QR booth (100% lokal).
- Halaman error kustom, rate limit endpoint publik, snapshot hasil stabil.

## Stack

- Laravel 13, PHP 8.4
- Inertia.js 3 + React + TypeScript
- Vite, Tailwind CSS 4
- MySQL 8.4
- Pest (unit/feature), Pint, PHPStan
- Node 24

## Arsitektur

Arsitektur berlapis ketat:

`Migration -> Model -> Repository -> Service -> Controller -> Route -> Inertia -> React Page -> Test`

- Tidak ada API terpisah / SPA baru.
- Controller tipis; logika bisnis di Service.
- Repository mengakses DB; Service tidak query langsung.
- Frontend React menerima props via Inertia.

Detail: `docs/ARCHITECTURE.md`.

## Struktur Direktori (ringkas)

```
app/          Http/, Models/, Repositories/, Services/, Contracts/, Providers/
resources/js/ Pages/, Components/, Layouts/, Types/, Lib/
routes/       web.php
database/     migrations/, seeders/, factories/
tests/        Feature/, Unit/
docs/         spesifikasi, keputusan, SPK, DB, arsitektur, UI/UX, deploy
```

## Prasyarat

- PHP 8.4, Composer
- Node.js 24, npm
- Docker + Docker Compose

## Instalasi (dari nol)

1. Salin environment:

```bash
cp .env.example .env
php artisan key:generate
```

2. Isi kredensial admin di `.env` (minimal untuk demo):

```env
ADMIN_EMAIL=admin@techmind.local
ADMIN_PASSWORD=change-me
```

3. Jalankan database MySQL 8.4:

```bash
docker compose up -d
```

Database tersedia di host port `3309`.

4. Install dependensi:

```bash
composer install
npm install
```

5. Migrasi dan seed:

```bash
php artisan migrate:fresh --seed
```

6. Build aset frontend:

```bash
npm run build
```

7. Jalankan aplikasi:

```bash
php artisan serve
```

Akses `http://localhost:8000`. Login admin di `http://localhost:8000/login` menggunakan `ADMIN_EMAIL` dan `ADMIN_PASSWORD` dari `.env`.

Shortcut: `composer setup` menjalankan langkah 1–6 sekaligus.

## Mode Demo Pameran

Aktifkan di `.env`:

```env
TECHMIND_DEMO_MODE=true
AI_DRIVER=template
```

Lalu siapkan data demo dan jalankan aplikasi:

```bash
php artisan techmind:seed-demo
php artisan serve
```

Perintah tersebut menjalankan `ReferenceDataSeeder` dan `DemoSeeder` (18 sesi selesai, penjelasan template, dan tanggapan pengunjung). Tombol simulasi cepat pada kuesioner hanya muncul saat `TECHMIND_DEMO_MODE=true`.

Halaman QR pameran tersedia di `http://localhost:8000/admin/booth` setelah login admin. QR dibangkitkan 100% lokal tanpa API pihak ketiga.

Panduan deploy VPS dan booth offline: `docs/DEPLOYMENT.md`.

## Variabel Environment Penting

| Variabel | Keterangan |
|---|---|
| `AI_DRIVER` | `template` (default, offline) atau provider OpenAI-compatible |
| `AI_BASE_URL`, `AI_API_KEY`, `AI_MODEL` | Kredensial provider AI (opsional) |
| `AI_TIMEOUT` | Timeout panggilan AI (detik) |
| `ADMIN_EMAIL`, `ADMIN_PASSWORD` | Akun admin |
| `TECHMIND_DEMO_MODE` | Aktifkan mode demo pameran |
| `DB_*` | Koneksi MySQL (default port 3309) |

## Pemeriksaan Kualitas

```bash
composer ci:check        # Pint, TypeScript, build Vite, Pest (SQLite)
composer test:mysql      # Pest terhadap MySQL 8.4
composer ci:check:full   # gabungan penuh (wajib lulus sebelum merge ke main)
```

Tes terhadap MySQL 8.4:

```bash
composer test:mysql
```

Gabungan penuh (Pint, TypeScript, build, Pest SQLite + MySQL):

```bash
composer ci:check:full
```

Wajib lulus sebelum merge ke `main`. Container init script membuat database `techmind_test` untuk pengujian MySQL.

Development server dengan HMR:

```bash
composer dev   # php artisan dev (serve + queue + logs + vite)
# atau manual:
php artisan serve & npm run dev
```

## Dokumentasi

- `docs/SPEC-TECHMIND.md` — spesifikasi produk
- `docs/ARCHITECTURE.md` — arsitektur berlapis
- `docs/DATABASE.md` — skema database & snapshot
- `docs/SPK-METHOD.md` — metode SAW
- `docs/DECISIONS.md` — keputusan (D-xx)
- `docs/UI-UX.md` — panduan UI/UX
- `docs/DEPLOYMENT.md` — deploy VPS & booth offline
- `docs/ROADMAP.md` — roadmap & status sesi
- `AGENTS.md` — aturan kerja agen

## Kontribusi

- Branch: `docs/initial-planning`, lalu `feat/*`, `fix/*`.
- Commit: Conventional Commits (`feat:`, `fix:`, `docs:`, `test:`, `refactor:`, `chore:`).
- Commit kecil dan fokus; jangan commit secret/`.env`.
- Jangan merge ke `main` tanpa persetujuan.
- Lulus `composer ci:check:full` sebelum merge.
- Tambahkan test untuk fitur/bug yang disentuh.

## Lisensi

Proyek ini berbasis Laravel React Starter Kit (MIT). Lihat `composer.json` untuk detail.
