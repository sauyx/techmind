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

Akses `http://localhost:8000`. Login admin di `http://localhost:8000/login`.

## Pemeriksaan Kualitas

```bash
composer ci:check
```

Perintah ini menjalankan Pint, TypeScript (`tsc --noEmit`), build Vite, dan Pest.
