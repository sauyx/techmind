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
