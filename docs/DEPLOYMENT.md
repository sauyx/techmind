# DEPLOYMENT

Panduan deploy TechMind ke VPS/hosting dan mode cadangan booth offline.

## 1. Persyaratan Server

- Ubuntu 22.04/24.04 atau setara.
- PHP 8.4 dengan ekstensi `mbstring`, `openssl`, `pdo_mysql`, `zip`, `gd`, `dom`, `fileinfo`.
- MySQL 8.4.
- Nginx.
- Node.js 24 hanya untuk build aset; produksi hanya membutuhkan hasil `public/build`.
- Certbot untuk SSL.

## 2. Persiapan Server

```bash
sudo apt update
sudo apt install -y php8.4-fpm php8.4-mysql php8.4-zip php8.4-gd php8.4-mbstring php8.4-xml nginx mysql-server certbot python3-certbot-nginx git unzip
```

## 3. Clone dan Install Aplikasi

```bash
git clone https://github.com/sauyx/techmind.git /var/www/techmind
cd /var/www/techmind
composer install --no-dev --optimize-autoloader
npm ci
npm run build
cp .env.example .env
php artisan key:generate
```

## 4. Environment Produksi

Isi `.env` dengan nilai aktual:

```env
APP_ENV=production
APP_URL=https://techmind.contoh.ac.id
APP_DEBUG=false

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=techmind
DB_USERNAME=techmind
DB_PASSWORD=ganti-dengan-password-kuat

ADMIN_EMAIL=admin@techmind.ac.id
ADMIN_PASSWORD=ganti-dengan-password-kuat

AI_DRIVER=template
AI_BASE_URL=
AI_API_KEY=
AI_MODEL=
AI_TIMEOUT=6

TECHMIND_DEMO_MODE=false
```

Untuk pameran dengan data demo, gunakan `TECHMIND_DEMO_MODE=true`. Di production, seeder demo menolak berjalan jika flag ini tidak aktif.

## 5. Database dan Migrasi

```bash
sudo mysql -e "CREATE DATABASE techmind CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql -e "CREATE USER 'techmind'@'localhost' IDENTIFIED BY 'ganti-dengan-password-kuat';"
sudo mysql -e "GRANT ALL PRIVILEGES ON techmind.* TO 'techmind'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"

php artisan migrate:fresh --seed
php artisan techmind:seed-demo
```

Perintah `techmind:seed-demo` menjalankan `ReferenceDataSeeder` lalu `DemoSeeder` untuk 18 sesi demo, penjelasan template, dan tanggapan pengunjung.

## 6. Virtual Host Nginx

Contoh `/etc/nginx/sites-available/techmind`:

```nginx
server {
    listen 80;
    server_name techmind.contoh.ac.id;
    root /var/www/techmind/public;

    index index.php;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Aktifkan dan amankan dengan SSL:

```bash
sudo ln -s /etc/nginx/sites-available/techmind /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
sudo certbot --nginx -d techmind.contoh.ac.id
```

SSL wajib dipasang agar QR code pada `/admin/booth` dapat dibuka pengunjung dengan peramban modern.

## 7. Folder dan Cache

```bash
sudo chown -R www-data:www-data /var/www/techmind
sudo chmod -R 775 /var/www/techmind/storage /var/www/techmind/bootstrap/cache

php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 8. Mode Cadangan Booth Offline

Jika koneksi internet kampus tidak stabil:

1. Siapkan hotspot lokal dari laptop/PC booth.
2. Set `.env`:

```env
APP_URL=http://192.168.43.100
AI_DRIVER=template
AI_TIMEOUT=6
TECHMIND_DEMO_MODE=true
```

3. Bangun ulang aset dan jalankan server lokal:

```bash
npm ci
npm run build
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan techmind:seed-demo
php artisan serve --host=0.0.0.0 --port=8000
```

4. Buka `http://192.168.43.100:8000/admin/booth` untuk mencetak QR URL hotspot.
5. Driver `template` memastikan penjelasan AI tetap dihasilkan tanpa API eksternal. Semua aset JavaScript, font, dan QR code dibundel/dibangkitkan secara lokal; tidak ada aset CDN.

## 9. Checklist Sebelum Booth Dibuka

- `GET /` dan `GET /tes` berfungsi pada perangkat pengunjung.
- Akun admin login di `/login`.
- `GET /admin/booth` menampilkan QR yang terbuka.
- `GET /admin/feedbacks/export` masih dapat diunduh.
- `TECHMIND_DEMO_MODE=true` hanya aktif saat pameran; matikan kembali setelah acara.
- Cadangan database tersedia dan tidak ada secret di repo.
