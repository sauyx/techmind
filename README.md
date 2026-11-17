# TechMind

Aplikasi web rekomendasi karier IT berbasis Sistem Penunjang Keputusan (SPK).

## Prasyarat

- PHP 8.4, Composer
- Node.js 24, npm
- Docker + Docker Compose

## Menjalankan dari Nol

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

## Mode Demo Pameran

Aktifkan mode demo di `.env`:

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

Panduan deploy VPS dan booth offline ada di `docs/DEPLOYMENT.md`.

## Pemeriksaan Kualitas

```bash
composer ci:check
```

Perintah ini menjalankan Pint, TypeScript (`tsc --noEmit`), build Vite, dan Pest (SQLite).

Tes terhadap MySQL 8.4:

```bash
composer test:mysql
```

Gabungan penuh (Pint, TypeScript, build, Pest SQLite + MySQL):

```bash
composer ci:check:full
```

Wajib lulus sebelum merge ke `main`.

Container init script membuat database `techmind_test` untuk pengujian MySQL.
