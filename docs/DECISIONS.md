# DECISIONS

Format: `D-xx` — keputusan, opsi, rekomendasi/status.

## D-01 Metode SPK utama
- Opsi A: SAW (Simple Additive Weighting) dengan bobot dari rating pengguna dan nilai profil karier.
- Opsi B: Profile Matching berbasis gap.
- Opsi C: TOPSIS.
- Keputusan final: A, dengan syarat tes persona di Sesi 3 (bukan sesi ini).
- Status: disetujui (bersyarat).

## D-02 Jumlah kriteria kuesioner
- Opsi A: 8 kriteria (lebih cepat).
- Opsi B: 12 kriteria (cakupan lebih luas).
- Keputusan final: B, dapat turun menjadi 10, diputuskan di Sesi 2.
- Status: disetujui (bersyarat).

## D-03 Login pengunjung
- Opsi A: tes anonim tanpa login (nama/email opsional).
- Opsi B: wajib registrasi/login.
- Keputusan final: A.
- Status: disetujui.

## D-04 Penyedia penjelasan AI
- Opsi A: API AI eksternal (OpenAI-compatible) + fallback template.
- Opsi B: hanya template (tanpa API).
- Keputusan final: driver AI default "template"; provider diatur lewat env (`AI_DRIVER`, `AI_BASE_URL`, `AI_API_KEY`, `AI_MODEL`); AI tidak menentukan ranking.
- Status: disetujui.

## D-05 Ekspor PDF
- Opsi A: DomPDF (server-side, stabil offline).
- Opsi B: Browsershot/Puppeteer (render chart JS, butuh Chromium).
- Keputusan final: A.
- Status: disetujui.

## D-06 Library grafik radar
- Opsi A: Recharts.
- Opsi B: Chart.js + react-chartjs-2.
- Keputusan final: A.
- Status: disetujui.

## D-07 Database
- Opsi A: MySQL 8.4 sesuai stack (via Docker jika perlu).
- Opsi B: MariaDB 10.11 yang tersedia di mesin.
- Keputusan final: A via Docker, port host 3309 (3307/3308 dipakai proyek lain; jangan sentuh container lain).
- Status: disetujui.

## D-08 Autentikasi admin
- Opsi A: Laravel auth bawaan (session, guard web).
- Opsi B: Fortify/Jetstream/Breeze.
- Keputusan final: A; boleh memakai starter kit resmi untuk halaman login, tetapi registrasi publik DIMATIKAN (route dan halaman dihapus), tanpa Spatie, admin dibedakan lewat kolom `is_admin` + middleware.
- Status: disetujui.

## D-09 Cache
- Opsi A: file cache (default, tanpa layanan tambahan).
- Opsi B: Redis.
- Keputusan final: A.
- Status: disetujui.

## D-10 Token hasil
- Opsi A: UUID v4 (URL-safe, sulit ditebak).
- Opsi B: slug pendek (lebih mudah dibaca).
- Keputusan final: A.
- Status: disetujui.

## D-11 Snapshot hasil
- Opsi A: simpan snapshot jawaban, bobot, profil, dan ranking di DB.
- Opsi B: hitung ulang saat halaman dibuka.
- Keputusan final: A.
- Status: disetujui.

## D-12 Mode demo
- Opsi A: seeder data contoh + akun admin demo (wajib untuk pameran).
- Opsi B: tanpa seeder (setup manual).
- Keputusan final: A.
- Status: disetujui.

## D-13 Identitas visual
- Opsi A: aksen indigo (#4F46E5), zinc/slate.
- Opsi B: aksen teal (#0D9488), zinc/slate.
- Keputusan final: A, lewat CSS variable.
- Status: disetujui.

## D-14 Strategi deploy booth
- Keputusan sebelumnya: Docker Compose vs `php artisan serve`.
- Keputusan final sesi ini: Docker Compose untuk database dan dev lokal; deploy booth dibahas di Sesi 7.
- Status: disetujui.

## D-15 Email pengunjung
- Keputusan final: email pengunjung tidak dikumpulkan.
- Status: disetujui.

## D-16 Kemandirian runtime (offline)
- Keputusan final: aplikasi harus berfungsi penuh tanpa internet saat runtime; tidak boleh ada aset dari CDN (font, ikon, skrip); semua dibundel lewat npm.
- Status: disetujui.

## Pertanyaan Terbuka

1. Nama institusi/prodi dan apakah logo resmi boleh digunakan? (Aset gambar tidak dibuat pada sesi ini.)
2. Apakah admin boleh mengubah bobot saat event berlangsung?
3. Target browser/OS pengunjung (Chrome Android dominan?) untuk pengujian.
4. Anggaran dan batas penggunaan API AI (kuota, model).
5. Apakah perlu mode offline penuh (tanpa API) sebagai default?
6. Siapa penanggung jawab validasi matriks profil karier?

## Risiko

- Koneksi internet kampus tidak stabil: mitigasi cache, fallback template, timeout.
- API AI down/lambat: fallback template + log + status.
- Banyak pengunjung bersamaan: rate limit, optimasi query, cache referensi, hindari AI sinkron berat.
- Kualitas data bobot/profil: tandai belum tervalidasi; admin dapat menyesuaikan; snapshot menjaga histori.
- Perbedaan MySQL 8.4 vs MariaDB 10.11: gunakan Docker MySQL atau uji kompatibilitas.
- Scope creep pada sesi demo: batasi fitur sesuai ruang lingkup v1.
