# AGENTS.md — TechMind

## 1. Stack (Jangan Diubah Tanpa Alasan Kuat)

- Laravel 13, PHP 8.4
- Inertia.js + React + TypeScript
- Vite, Tailwind CSS 3
- MySQL 8.4
- Pest
- Node 24 (tersedia di mesin)

## 2. Arsitektur (Wajib)

Ikuti berlapis ketat:

`Migration -> Model -> Repository -> Service -> Controller -> Route -> Inertia -> React Page -> Test`

- Tidak membuat API terpisah.
- Tidak membuat SPA baru.
- Controller tipis; logika di Service.
- Repository mengakses DB; Service tidak query langsung.

## 3. Konvensi Penamaan

- DB: snake_case.
- PHP: camelCase method, PascalCase class.
- JS/TS: camelCase variable/function, PascalCase komponen.
- Route/file: kebab-case bila relevan.
- Dokumen dan UI: Bahasa Indonesia.
- Kode/identifier: Bahasa Inggris.

## 4. Commit

- Conventional Commits: `feat:`, `fix:`, `docs:`, `test:`, `refactor:`, `chore:`.
- Commit kecil dan fokus.
- Jangan commit secret/`.env`.
- Jangan merge ke main tanpa persetujuan.

## 5. Testing & Verifikasi

- Wajib jalankan test setelah perubahan: `php artisan test` atau `./vendor/bin/pest`.
- Wajib lint/typecheck bila tersedia: `npm run lint`, `npm run typecheck`, `npm run build`.
- Jangan klaim selesai tanpa bukti (output test nyata).
- Tambahkan test untuk fitur/bug yang disentuh.

## 6. Data & SPK

- Metode utama: SAW (lihat docs/SPK-METHOD.md).
- Matriks profil karier adalah DATA AWAL belum tervalidasi; jangan klaim sumber.
- Snapshot hasil wajib (lihat docs/DATABASE.md).

## 7. AI

- Gunakan abstraksi `AiExplanationProvider`.
- Selalu sediakan fallback template.
- Log setiap panggilan di `ai_explanations`.

## 8. Laporan Akhir Sesi

Setiap sesi kerja harus melaporkan:
- daftar file yang diubah/ditambah
- perintah test yang dijalankan
- hasil test nyata (ringkas)
- risiko/next step

## 9. Yang Tidak Boleh Dilakukan

- Mengubah stack tanpa alasan kuat dan persetujuan.
- Menginstall dependensi tanpa kebutuhan jelas.
- Mengklaim selesai tanpa bukti.
- Menyimpan secret di repo.
