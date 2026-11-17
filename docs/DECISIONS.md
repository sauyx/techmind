# DECISIONS

Format: `D-xx` — keputusan, opsi, rekomendasi, status.

## D-01 Metode SPK utama
- Opsi A: SAW (Simple Additive Weighting) dengan bobot dari rating pengguna dan nilai profil karier.
- Opsi B: Profile Matching berbasis gap.
- Opsi C: TOPSIS.
- Rekomendasi: A. Alasan: paling mudah dijelaskan ke pengunjung awam, implementasi dan pengujian paling sederhana, cukup akurat untuk rekomendasi awal. Gap analysis tetap ditampilkan sebagai penjelasan, bukan sebagai skor utama.
- Status: perlu persetujuan.

## D-02 Jumlah kriteria kuesioner
- Opsi A: 8 kriteria (lebih cepat).
- Opsi B: 12 kriteria (cakupan lebih luas).
- Rekomendasi: B dengan pengelompokan 3 langkah x 4 kriteria.
- Status: perlu persetujuan.

## D-03 Login pengunjung
- Opsi A: tes anonim tanpa login (nama/email opsional).
- Opsi B: wajib registrasi/login.
- Rekomendasi: A (sesuai konteks booth).
- Status: perlu persetujuan.

## D-04 Penyedia penjelasan AI
- Opsi A: API AI eksternal (OpenAI-compatible) + fallback template.
- Opsi B: hanya template (tanpa API).
- Rekomendasi: A dengan timeout, retry 1x, fallback wajib, cache hasil.
- Status: perlu persetujuan + ketersediaan API key/budget.

## D-05 Ekspor PDF
- Opsi A: DomPDF (server-side, stabil offline).
- Opsi B: Browsershot/Puppeteer (render chart JS, butuh Chromium).
- Rekomendasi: A; verifikasi ketersediaan Chromium sebelum mempertimbangkan B.
- Status: perlu persetujuan.

## D-06 Library grafik radar
- Opsi A: Recharts.
- Opsi B: Chart.js + react-chartjs-2.
- Rekomendasi: A (integrasi React/TypeScript lebih sederhana).
- Status: perlu persetujuan.

## D-07 Database
- Opsi A: MySQL 8.4 sesuai stack (via Docker jika perlu).
- Opsi B: MariaDB 10.11 yang tersedia di mesin.
- Rekomendasi: A untuk konsistensi stack; gunakan Docker Compose. Jika Docker tidak dipakai, B dengan uji kompatibilitas.
- Status: perlu persetujuan.

## D-08 Autentikasi admin
- Opsi A: Laravel auth bawaan (session, guard web).
- Opsi B: Fortify/Jetstream/Breeze.
- Rekomendasi: A (minimal, sedikit dependensi).
- Status: perlu persetujuan.

## D-09 Cache
- Opsi A: file cache (default, tanpa layanan tambahan).
- Opsi B: Redis.
- Rekomendasi: A untuk demo; Redis opsional jika tersedia.
- Status: perlu persetujuan.

## D-10 Token hasil
- Opsi A: UUID v4 (URL-safe, sulit ditebak).
- Opsi B: slug pendek (lebih mudah dibaca).
- Rekomendasi: A (keamanan akses hasil).
- Status: perlu persetujuan.

## D-11 Snapshot hasil
- Opsi A: simpan snapshot jawaban, bobot, profil, dan ranking di DB.
- Opsi B: hitung ulang saat halaman dibuka.
- Rekomendasi: A (hasil lama tidak berubah saat admin mengubah bobot).
- Status: perlu persetujuan.

## D-12 Mode demo
- Opsi A: seeder data contoh + akun admin demo (wajib untuk pameran).
- Opsi B: tanpa seeder (setup manual).
- Rekomendasi: A.
- Status: perlu persetujuan.

## D-13 Identitas visual
- Opsi A: aksen indigo (#4F46E5), zinc/slate.
- Opsi B: aksen teal (#0D9488), zinc/slate.
- Rekomendasi: A.
- Status: perlu persetujuan.

## D-14 Strategi deploy booth
- Opsi A: Docker Compose (app + MySQL + nginx) di laptop.
- Opsi B: `php artisan serve` + MySQL lokal.
- Rekomendasi: A untuk konsistensi; B sebagai cadangan cepat.
- Status: perlu persetujuan.

## Pertanyaan Terbuka

1. Nama institusi/prodi dan apakah logo resmi boleh digunakan? (Aset gambar tidak dibuat pada sesi ini.)
2. Apakah email pengunjung perlu disimpan (implikasi consent/privasi)?
3. Apakah admin boleh mengubah bobot saat event berlangsung?
4. Target browser/OS pengunjung (Chrome Android dominan?) untuk pengujian.
5. Anggaran dan batas penggunaan API AI (kuota, model).
6. Apakah perlu mode offline penuh (tanpa API) sebagai default?
7. Siapa penanggung jawab validasi matriks profil karier?

## Risiko

- Koneksi internet kampus tidak stabil: mitigasi cache, fallback template, timeout.
- API AI down/lambat: fallback template + log + status.
- Banyak pengunjung bersamaan: rate limit, optimasi query, cache referensi, hindari AI sinkron berat.
- Kualitas data bobot/profil: tandai belum tervalidasi; admin dapat menyesuaikan; snapshot menjaga histori.
- Perbedaan MySQL 8.4 vs MariaDB 10.11: gunakan Docker MySQL atau uji kompatibilitas.
- Scope creep pada sesi demo: batasi fitur sesuai ruang lingkup v1.
