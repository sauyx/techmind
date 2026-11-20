# DEPLOY PAMERAN TECHMIND

JANGAN gunakan `php artisan serve` untuk pameran. Gunakan nginx + php-fpm (Docker) atau FrankenPHP.

## File docker yang tersedia

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

`docker-compose.prod.yml`:

```yaml
services:
  nginx:
    image: nginx:1.27-alpine
    ports:
      - "8088:80"
    volumes:
      - ./docker/nginx.conf:/etc/nginx/conf.d/default.conf:ro
      - app_public:/var/www/html/public:ro
    depends_on:
      - app
    restart: unless-stopped

  app:
    build:
      context: .
      dockerfile: Dockerfile.prod
    env_file:
      - .env.production
    volumes:
      - app_public:/var/www/html/public
    depends_on:
      mysql:
        condition: service_healthy
    restart: unless-stopped

  mysql:
    image: mysql:8.4
    environment:
      MYSQL_DATABASE: ${DB_DATABASE:-techmind}
      MYSQL_USER: ${DB_USERNAME:-techmind}
      MYSQL_PASSWORD: ${DB_PASSWORD:-secret}
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD:-secret}
    volumes:
      - mysql_data:/var/lib/mysql
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]
      interval: 10s
      timeout: 5s
      retries: 10
    restart: unless-stopped

volumes:
  mysql_data:
  app_public:
```

## Rekomendasi stack booth
- nginx (reverse proxy) + php-fpm (pool)
- MySQL 8.4 via Docker (jaringan internal; port tidak diekspos pada compose production)
- `CACHE_STORE=file` (atau redis jika tersedia)
- `AI_DRIVER=template` (default offline) atau provider OpenAI-compatible

## Port database
- Compose dev (`docker-compose.yml`) mengekspos MySQL di `127.0.0.1:3309` untuk akses lokal.
- Compose production TIDAK mengekspos port MySQL. Akses DB hanya via `docker exec` atau jaringan internal.

## Jumlah worker php-fpm (VPS RAM 2 GB)
Pengukuran worker php-fpm saat alur tes + PDF: sekitar 41 MB per worker (RSS).
Rumus: `pm.max_children = (RAM_total - RAM_OS_dan_layanan_lain) / RAM_per_worker`.
Contoh: `(2048 MB - 512 MB) / 41 MB ≈ 37`. Gunakan 20 untuk margin aman.
- Sarankan `pm.max_children = 20`, `pm.start_servers = 5`, `pm.min_spare_servers = 3`, `pm.max_spare_servers = 10`.

## AI_TIMEOUT
- Sarankan 6 detik (default). Jangan melebihi 8 detik.
- Retry otomatis maks 2 percobaan dengan total tidak melebihi `AI_TIMEOUT + 2` detik.

## Rate limit & TrustProxies
- Atur `TRUSTED_PROXIES` agar IP pengunjung dibaca dari `X-Forwarded-For` di belakang nginx/Caddy/Cloudflare.
- Pastikan proxy mengirim `X-Forwarded-For`, `X-Forwarded-Proto`, dan `X-Forwarded-Host`.
- Nilai `*` mempercayai semua proxy. Gunakan hanya bila edge dan sumber lalu lintas benar-benar dikontrol.
- Named limiter publik: `tes-submit` 6 per session/IP 120 per menit; `hasil-pdf` dan `feedback` 5 per token/IP 60 per menit. Nilai dapat diubah melalui env.

## Skenario 1 — VPS (dari server kosong)
1. Install Docker dan Docker Compose.
2. Clone repo, salin `.env.production.example` ke `.env.production`, isi secret.
3. `docker compose -f docker-compose.prod.yml up -d --build`
4. Reverse proxy Caddy (contoh Caddyfile): `techmind.example.com { reverse_proxy nginx:80 }`. Set `TRUSTED_PROXIES=*` hanya jika Caddy adalah satu-satunya edge; lebih aman daftar IP Caddy.
5. Firewall: buka hanya 22, 80, 443. Tutup 3306/3309/8088 dari publik.
6. `docker compose -f docker-compose.prod.yml exec app php artisan migrate --force`
7. `docker compose -f docker-compose.prod.yml exec app php artisan db:seed`

## Skenario 2 — Laptop booth via LAN/hotspot
1. `docker compose -f docker-compose.prod.yml up -d --build`
2. Akses via IP lokal laptop, mis. `http://192.168.1.10:8088`.
3. QR mengarah ke IP lokal laptop.
4. Set `AI_DRIVER=template` jika tanpa internet.
5. Catatan: link hasil tidak bisa dibuka pengunjung setelah pulang karena IP lokal.

## Backup & restore DB
Sebelum pameran:
```
docker compose -f docker-compose.prod.yml exec mysql mysqldump -u root -psecret techmind > backup-pre.sql
```
Sesudah pameran:
```
docker compose -f docker-compose.prod.yml exec mysql mysqldump -u root -psecret techmind > backup-post.sql
```
Restore:
```
docker compose -f docker-compose.prod.yml exec -T mysql mysql -u root -psecret techmind < backup-post.sql
```

## Checklist sebelum pameran
1. `php artisan migrate --force`
2. `php artisan db:seed` (atau `techmind:seed-demo` jika demo)
3. `php artisan cache:clear`
4. `php artisan config:cache`
5. `php artisan route:cache`
6. `npm run build` (jika build belum terjadi di image)
7. Tes QR dari HP
8. Set `TECHMIND_DEMO_MODE` sesuai kebutuhan
9. Pastikan `APP_DEBUG=false`
10. Uji 1 alur lengkap (tes → hasil → PDF → feedback)
11. H-1: `php artisan techmind:ai-check`
12. H-1: `php artisan techmind:matrix-health`

## Dev vs Prod
- `.env` = development lokal: `APP_ENV=local`, `APP_DEBUG=true`, `DB_HOST=127.0.0.1`, `DB_PORT=3309`, `APP_URL=http://127.0.0.1:8000`. Jalankan: `php artisan serve` + `npm run dev`.
- `.env.production` = production Docker: dibaca via `env_file:` di `docker-compose.prod.yml`. Jalankan: `docker compose -f docker-compose.prod.yml up -d`.
- Project Docker prod memakai `name: techmind-prod` agar tidak bentrok dengan compose dev (`techmind`).
