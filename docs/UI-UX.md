# UI-UX

## 1. Sitemap

Publik:
- `/` Landing
- `/tests/{token}` Kuesioner (3 langkah)
- `/results/{token}` Hasil
- `/results/{token}/pdf` Ekspor PDF

Admin:
- `/admin/login`
- `/admin/dashboard`
- `/admin/criteria`
- `/admin/careers`
- `/admin/careers/{id}/profiles`
- `/admin/statistics`
- `/admin/feedbacks`

## 2. Alur Mobile (Pengunjung)

1. Scan QR -> Landing: judul, penjelasan singkat, tombol "Mulai Tes", opsi nama/email.
2. Langkah 1-3: 4 kriteria per langkah, skala 1-5 (tap), progress bar.
3. Submit -> loading singkat.
4. Hasil: kartu rekomendasi utama, ranking, radar, detail karier, penjelasan.
5. Aksi: lihat roadmap, ekspor PDF, isi penilaian.
6. Form penilaian -> ucapan terima kasih.

Target waktu: 2-3 menit.

## 3. Gaya Visual

- Minimalis enterprise, inspirasi Linear/Stripe/Vercel.
- Tipografi: Inter / system-ui; heading semibold; body 14-16px.
- Palet: zinc/slate untuk netral, satu aksen indigo (#4F46E5) (lihat D-13).
- Tanpa emoji.
- Border lebih diutamakan daripada shadow; shadow hanya untuk modal/popover.
- Radius: 8px (rounded-lg), hover halus 150ms ease.
- Spacing konsisten 4/8px.
- Mobile-first; container max-width 480px untuk publik.

## 4. Komponen Utama

- `ScaleInput`: tombol 1-5 dengan label tekstual (Sangat tidak ... - Sangat ...).
- `ProgressBar`: langkah x dari y.
- `RadarProfileChart`: dua polygon (pengguna vs karier).
- `CareerCard`: ranking, skor, badge.
- `RoadmapList`: langkah belajar berurutan.

## 5. Grafik Radar

- Rekomendasi: Recharts (`RadarChart`, `PolarGrid`, `PolarAngleAxis`, `Radar`).
- Alasan: komponen React deklaratif, TypeScript, ukuran wajar, mudah disesuaikan.
- Aksesibilitas: sediakan tabel ringkasan skor di bawah chart.

## 6. PDF

- Default: DomPDF (server-side), template Blade.
- Konten: identitas sesi (nama opsional), tanggal, ranking, skor, profil radar sebagai tabel/SVG statis, detail karier teratas, penjelasan.
- Jika chart JS tidak dapat dirender di PDF, gunakan tabel + SVG statis.
- Verifikasi ketersediaan Chromium sebelum memilih Browsershot.

## 7. Identitas Visual TechMind

- Wordmark teks "TechMind" (tanpa aset gambar pada sesi ini).
- Font: Inter semibold, tracking -0.02em.
- Aksen: indigo #4F46E5 untuk CTA dan highlight.
- Tidak menggunakan logo/gambar hingga aset disetujui.
