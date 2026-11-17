# SPK-METHOD

Dokumen ini menjelaskan metode SPK yang direkomendasikan, rumus, contoh hitung, kriteria final, daftar karier, dan matriks profil karier.

## 1. Evaluasi Metode

### 1.1 SAW (Simple Additive Weighting)
- Ide: skor akhir alternatif = jumlah bobot kriteria x nilai alternatif pada kriteria tersebut.
- Pada TechMind: bobot kriteria = rating pengguna (ternormalisasi), nilai alternatif = skor profil karier (ternormalisasi per kriteria).
- Kelebihan: transparan, mudah dijelaskan, implementasi sederhana, pengujian mudah.
- Kekurangan: tidak menangkap "gap" secara eksplisit; sensitif terhadap normalisasi.

### 1.2 Profile Matching
- Ide: bandingkan profil pengguna dengan profil ideal karier, hitung gap, petakan gap ke bobot, pisahkan faktor inti dan sekunder.
- Kelebihan: penjelasan gap sangat jelas.
- Kekurangan: lebih banyak parameter (mapping gap, proporsi inti/sekunder), lebih sulit diuji dan dijelaskan ke awam.

### 1.3 TOPSIS
- Ide: jarak alternatif ke solusi ideal positif dan negatif; ranking berdasarkan closeness coefficient.
- Kelebihan: robust, menangani benefit/cost.
- Kekurangan: formula jarak Euclidean sulit dijelaskan ke awam; implementasi dan debugging lebih kompleks.

### 1.4 Rekomendasi
Gunakan **SAW** sebagai metode utama. Tampilkan gap analysis (selisih rating vs profil) sebagai penjelasan tambahan, bukan sebagai skor utama.

## 2. Rumus SAW (Kanonik)

Misal terdapat `m` karier (alternatif) dan `n` kriteria.

1. Rating pengguna: `r_j` (1..5) untuk kriteria `j`.
2. Bobot ternormalisasi:
   `w_j = r_j / Σ_{k=1}^{n} r_k`
3. Profil karier: `x_{ij}` (1..5) untuk karier `i`, kriteria `j`.
4. Normalisasi matriks keputusan (kriteria benefit):
   `r_{ij} = x_{ij} / max_{i}(x_{ij})`
5. Skor akhir karier:
   `V_i = Σ_{j=1}^{n} w_j * r_{ij}`
6. Ranking: urutkan `V_i` descending.

Catatan:
- Semua kriteria bersifat benefit (lebih tinggi lebih baik).
- Jika semua profil pada satu kriteria bernilai sama, normalisasi menghasilkan 1 untuk semua (tidak mempengaruhi ranking).
- Snapshot bobot dan profil disimpan agar hasil lama stabil (lihat DATABASE.md).

## 3. Contoh Hitung Manual (3 Karier x 4 Kriteria)

### 3.1 Data
Kriteria:
- C1 Logika
- C2 Analisis Data
- C3 Desain Visual
- C4 Komunikasi

Rating pengguna (r):
- C1=5, C2=4, C3=2, C4=3. Total = 14.

Profil karier (x):

| Karier | C1 | C2 | C3 | C4 |
|---|---:|---:|---:|---:|
| Web Developer | 4 | 2 | 3 | 3 |
| Data Analyst | 4 | 5 | 1 | 3 |
| UI/UX Designer | 3 | 2 | 5 | 4 |

### 3.2 Bobot
- w1 = 5/14 = 0.3571
- w2 = 4/14 = 0.2857
- w3 = 2/14 = 0.1429
- w4 = 3/14 = 0.2143

### 3.3 Normalisasi (max per kriteria)
- max C1 = 4 -> Web 1.000, Data 1.000, UI 0.750
- max C2 = 5 -> Web 0.400, Data 1.000, UI 0.400
- max C3 = 5 -> Web 0.600, Data 0.200, UI 1.000
- max C4 = 4 -> Web 0.750, Data 0.750, UI 1.000

### 3.4 Skor
- Web = 0.3571*1.000 + 0.2857*0.400 + 0.1429*0.600 + 0.2143*0.750 = 0.3571 + 0.1143 + 0.0857 + 0.1607 = 0.7179
- Data = 0.3571*1.000 + 0.2857*1.000 + 0.1429*0.200 + 0.2143*0.750 = 0.3571 + 0.2857 + 0.0286 + 0.1607 = 0.8321
- UI = 0.3571*0.750 + 0.2857*0.400 + 0.1429*1.000 + 0.2143*1.000 = 0.2679 + 0.1143 + 0.1429 + 0.2143 = 0.7393

Ranking: 1 Data Analyst (0.832), 2 UI/UX Designer (0.739), 3 Web Developer (0.718).

### 3.5 Gap Analysis (penjelasan tambahan)
Untuk kriteria dengan gap besar (|r_j - x_{ij}| >= 2), tampilkan pesan:
- Jika profil karier > rating pengguna: "Kamu mungkin perlu menguatkan X."
- Jika profil karier < rating pengguna: "Kamu unggul di X; karier ini kurang memanfaatkannya."

## 4. Kriteria Final (12)

Status: DATA AWAL, belum tervalidasi dengan sumber eksternal.

| Kode | Nama | Deskripsi Singkat |
|---|---|---|
| logic_problem_solving | Logika & Pemecahan Masalah | Kemampuan berpikir logis, debugging, algoritma |
| data_analysis | Analisis Data | Membaca, membersihkan, menafsirkan data |
| visual_design | Desain Visual | Estetika, layout, warna, tipografi |
| communication | Komunikasi & Presentasi | Menjelaskan ide ke tim/klien |
| attention_detail | Ketelitian & Pengujian | Cermat, uji kasus, kualitas |
| math_statistics | Matematika & Statistik | Probabilitas, statistik, model |
| network_infra | Jaringan & Infrastruktur | Server, jaringan, deployment |
| creativity | Kreativitas | Ide baru, eksplorasi solusi |
| self_discipline | Kerja Mandiri & Disiplin | Konsistensi, belajar mandiri |
| teamwork | Kolaborasi Tim | Kerja sama, koordinasi |
| security_awareness | Keamanan & Risiko | Ancaman, praktik aman |
| business_process | Pemahaman Bisnis/Proses | Kebutuhan pengguna, proses organisasi |

## 5. Daftar Karier (10)

Status: DATA AWAL, belum tervalidasi.

1. Web Developer
2. Mobile Developer
3. Data Analyst
4. UI/UX Designer
5. QA Engineer
6. DevOps Engineer
7. Network Engineer
8. Cybersecurity Analyst
9. System Analyst
10. Backend Developer

## 6. Matriks Profil Karier (skor ideal 1-5)

Status: DATA AWAL, belum tervalidasi. Baris = karier, kolom = kriteria (C1..C12 sesuai urutan kriteria final).

| Karier | C1 | C2 | C3 | C4 | C5 | C6 | C7 | C8 | C9 | C10 | C11 | C12 |
|---|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|
| Web Developer | 4 | 2 | 3 | 3 | 4 | 2 | 2 | 3 | 4 | 4 | 3 | 2 |
| Mobile Developer | 4 | 2 | 3 | 3 | 4 | 2 | 2 | 4 | 4 | 4 | 3 | 2 |
| Data Analyst | 4 | 5 | 2 | 4 | 4 | 5 | 1 | 2 | 4 | 3 | 2 | 3 |
| UI/UX Designer | 3 | 2 | 5 | 4 | 4 | 1 | 1 | 5 | 3 | 4 | 2 | 3 |
| QA Engineer | 4 | 3 | 2 | 3 | 5 | 2 | 2 | 2 | 4 | 4 | 3 | 2 |
| DevOps Engineer | 4 | 3 | 1 | 3 | 4 | 2 | 4 | 2 | 4 | 4 | 4 | 2 |
| Network Engineer | 3 | 2 | 1 | 3 | 4 | 2 | 5 | 1 | 4 | 3 | 4 | 2 |
| Cybersecurity Analyst | 4 | 3 | 1 | 4 | 5 | 3 | 4 | 2 | 4 | 3 | 5 | 3 |
| System Analyst | 4 | 4 | 2 | 5 | 4 | 3 | 2 | 3 | 4 | 4 | 3 | 5 |
| Backend Developer | 5 | 3 | 1 | 3 | 4 | 3 | 3 | 2 | 4 | 4 | 4 | 2 |

## 7. Validasi Data (Wajib Sebelum Produksi)

- Bandingkan dengan roadmap.sh, deskripsi lowongan, SFIA/okupasi resmi.
- Catat sumber yang benar-benar diverifikasi; jika tidak bisa verifikasi, tulis "belum tervalidasi".
- Simpan hasil validasi di `docs/SPK-METHOD.md` (revisi) atau dokumen terpisah.
