# TechMind — Panduan Windows (One-Click via Docker)

Panduan ini untuk teman yang memakai Windows dan ingin menjalankan TechMind tanpa install PHP/Node/MySQL manual.

## Prasyarat

1. Windows 10/11 64-bit.
2. Docker Desktop (https://www.docker.com/products/docker-desktop/).
   - Aktifkan WSL 2 saat diminta.
   - Pastikan Docker Desktop berstatus "Running".
3. Koneksi internet (untuk build pertama).

## Cara Menjalankan

1. Ekstrak ZIP TechMind ke folder, misal `C:\techmind`.
2. Double-click `start-windows.bat`.
   - Build pertama bisa 5–10 menit (mengunduh image dan dependensi).
   - Kredensial admin default: `admin@techmind.local` / `change-me` (ubah di `docker-compose.full.yml` bagian `environment` jika perlu).
3. Browser otomatis terbuka:
   - Aplikasi: http://localhost:8000
   - phpMyAdmin: http://localhost:8080
4. Login admin:
   - Email: `admin@techmind.local`
   - Password: `change-me`
   (ubah di file `.env` jika ingin, lalu jalankan `stop-windows.bat` dan `start-windows.bat` lagi)

## Menghentikan

Double-click `stop-windows.bat` atau jalankan `docker compose -f docker-compose.full.yml down`.

## Mode Demo

Sudah aktif secara default (`TECHMIND_DEMO_MODE=true`, `AI_DRIVER=template`). Data demo (18 sesi, penjelasan template, tanggapan pengunjung) dibuat otomatis saat pertama kali container jalan.

## Troubleshooting

- "Docker Desktop belum berjalan": buka Docker Desktop, tunggu status Running, coba lagi.
- Port 8000/8080 bentrok: ubah port di `docker-compose.full.yml` (misal `"8001:80"`), lalu jalankan ulang.
- Build gagal karena jaringan: coba lagi; Docker akan melanjutkan layer yang sudah terunduh.
- Ingin reset data: `docker compose -f docker-compose.full.yml down -v` lalu `start-windows.bat` (menghapus volume database).
- Login admin gagal: cek `ADMIN_EMAIL` dan `ADMIN_PASSWORD` di file `.env`.
