# SPEC-TECHMIND

## 1. Ringkasan

TechMind adalah aplikasi web rekomendasi karier IT berbasis Sistem Penunjang Keputusan (SPK). Pengunjung mengisi kuesioner minat dan skill (skala 1-5), sistem menghitung ranking jalur karier IT secara transparan, menampilkan profil radar, detail karier, dan penjelasan hasil dalam bahasa natural. Tujuan utama adalah demo booth kampus (mata kuliah Digital Kreatif, Prodi Sistem Informasi) dengan durasi 2-3 menit per pengunjung melalui scan QR di HP.

## 2. Tujuan Produk

- Memberi rekomendasi karier IT yang mudah dipahami pengunjung awam.
- Menampilkan proses perhitungan SPK secara transparan (bobot, skor, ranking).
- Menyediakan detail karier: deskripsi, skill, tools, roadmap belajar.
- Menyediakan penjelasan hasil oleh AI dengan fallback template offline.
- Menyediakan riwayat hasil via link unik/token dan ekspor PDF.
- Menyediakan form penilaian pengunjung.
- Menyediakan panel admin untuk kelola kriteria, bobot, karier, profil, statistik, dan ekspor tanggapan.
- Menyediakan mode demo dengan seeder data contoh dan akun admin demo.

## 3. Persona

- Pengunjung booth: mahasiswa/pelajar/umum, tidak ingin registrasi, menggunakan HP.
- Admin/dosen: mengelola data dan melihat statistik, login via akun.
- Operator booth: menjalankan demo, menangani gangguan.

## 4. Fitur Target

1. Landing page + mulai tes tanpa login; sesi anonim; nama/email opsional.
2. Kuesioner 12 kriteria skala 1-5, dibagi 3 langkah (4 kriteria per langkah).
3. Perhitungan SPK menghasilkan ranking karier + skor.
4. Halaman hasil: ranking, grafik radar profil pengguna vs profil karier, detail karier.
5. Penjelasan hasil AI natural + fallback template.
6. Riwayat hasil via token unik + ekspor PDF.
7. Form penilaian pengunjung (0-100, tanggapan, saran).
8. Admin: CRUD kriteria/bobot/karier/profil; statistik; ekspor tanggapan.
9. Mode demo: seeder + akun admin demo.

## 5. Ruang Lingkup

- Aplikasi web server-rendered via Inertia.js (bukan SPA terpisah, bukan API publik).
- Autentikasi admin berbasis session Laravel.
- Penyimpanan MySQL 8.4 (atau kompatibel).
- Perhitungan SPK dilakukan di backend (Service), frontend hanya presentasi.
- Snapshot jawaban/hasil agar hasil lama stabil.
- Rate limit endpoint publik.
- Caching data referensi (kriteria/karier/profil).
- Export PDF server-side.

## 6. Di Luar Ruang Lingkup (v1)

- Registrasi/login pengunjung.
- Aplikasi mobile native.
- Rekomendasi personal jangka panjang / notifikasi.
- Integrasi SSO kampus.
- Multi-bahasa (selain Bahasa Indonesia).
- Pelatihan/model ML.
- Integrasi pihak ketiga selain penyedia AI teks.
- Real-time collaboration.

## 7. Non-Fungsional

- Mobile-first untuk halaman publik; target layar 360px ke atas.
- Waktu respons halaman publik < 2 detik pada kondisi normal.
- Submit kuesioner + hasil < 3 detik tanpa AI; dengan AI < 8 detik atau fallback.
- Stabil pada koneksi tidak stabil: fallback template, cache lokal data referensi.
- Aksesibilitas dasar: kontras, fokus keyboard, label form.
- Keamanan: CSRF, validasi, rate limit, hashing IP, tidak menyimpan rahasia di frontend.

## 8. Konvensi Penamaan

- Kode/identifier Bahasa Inggris (snake_case DB, camelCase JS/TS, PascalCase komponen).
- Dokumen dan UI Bahasa Indonesia.
- Branch: `docs/initial-planning`, lalu `feat/*`, `fix/*`.
- Commit: Conventional Commits.

## 9. Referensi Dokumen

- `docs/DECISIONS.md`
- `docs/SPK-METHOD.md`
- `docs/DATABASE.md`
- `docs/ARCHITECTURE.md`
- `docs/UI-UX.md`
- `docs/ROADMAP.md`
- `AGENTS.md`
