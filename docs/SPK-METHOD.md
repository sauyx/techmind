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

## 2. Rumus SAW (Kanonik, dengan Pengali Admin)

Misal terdapat `m` karier (alternatif) dan `n` kriteria.

1. Rating pengguna: `r_j` (1..5) untuk kriteria `j`.
2. Pengali admin: `m_j` (default 1.00, rentang 0.50-2.00).
3. Bobot ternormalisasi:
   `w_j = (r_j * m_j) / Σ_{k=1}^{n} (r_k * m_k)`
   Jika semua `m_k = 1`, identik dengan SAW kanonik.
4. Profil karier: `x_{ij}` (1..5) untuk karier `i`, kriteria `j`.
5. Normalisasi matriks keputusan (kriteria benefit, max di antara karier aktif):
   `r_{ij} = x_{ij} / max_{i aktif}(x_{ij})`
6. Skor akhir karier:
   `V_i = Σ_{j=1}^{n} w_j * r_{ij}`
7. Ranking: urutkan `V_i` descending.

Tie-break (D-21): jika `|V_a - V_b| < 1e-9`, urutkan berdasarkan `sort_order` karier ascending (deterministik).

Catatan:
- Semua kriteria bersifat benefit (lebih tinggi lebih baik).
- Karier nonaktif tidak ikut ranking dan tidak ikut penentuan max.
- Skor kecocokan tampilan = `V_i * 100` dibulatkan 1 desimal; ini indeks kecocokan relatif, bukan probabilitas.

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

## 4. Kriteria Final (12) — Sesi 3 (D-19)

Status: DATA AWAL, belum tervalidasi.

Langkah 1 "Cara Berpikir":
- `logic_problem_solving` Logika & Pemecahan Masalah
- `data_analysis` Analisis Data
- `math_statistics` Matematika & Statistik
- `attention_detail` Ketelitian & Pengujian

Langkah 2 "Kreasi & Interaksi":
- `visual_design` Desain Visual
- `creativity` Kreativitas
- `user_empathy` Empati & Riset Pengguna
- `communication` Komunikasi & Presentasi

Langkah 3 "Teknis & Bisnis":
- `coding` Pemrograman & Pengembangan Perangkat Lunak
- `network_infra` Jaringan & Infrastruktur
- `security_awareness` Keamanan & Risiko
- `business_process` Pemahaman Bisnis & Proses

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

## 6. Matriks Profil Karier (skor ideal 1-5) — Revisi D-23

Status: DATA AWAL berbasis penalaran domain, belum divalidasi pakar. Total tiap karier = 37 (invarian D-19). Revisi berdasarkan analisis kesehatan matriks (D-23); rumus SAW tidak berubah.

Kolom: C1 logic_problem_solving, C2 data_analysis, C3 math_statistics, C4 attention_detail, C5 visual_design, C6 creativity, C7 user_empathy, C8 communication, C9 coding, C10 network_infra, C11 security_awareness, C12 business_process.

| Karier | C1 | C2 | C3 | C4 | C5 | C6 | C7 | C8 | C9 | C10 | C11 | C12 | Total |
|---|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|
| Web Developer | 4 | 2 | 1 | 3 | 2 | 4 | 3 | 4 | 5 | 2 | 3 | 4 | 37 |
| Mobile Developer | 4 | 2 | 2 | 4 | 4 | 3 | 3 | 2 | 5 | 1 | 4 | 3 | 37 |
| Backend Developer | 5 | 3 | 3 | 4 | 1 | 2 | 1 | 2 | 5 | 4 | 5 | 2 | 37 |
| Data Analyst | 4 | 5 | 5 | 3 | 2 | 3 | 2 | 4 | 3 | 1 | 2 | 3 | 37 |
| UI/UX Designer | 3 | 3 | 1 | 3 | 5 | 5 | 5 | 4 | 2 | 1 | 1 | 4 | 37 |
| QA Engineer | 4 | 3 | 1 | 5 | 2 | 3 | 4 | 4 | 4 | 1 | 3 | 3 | 37 |
| DevOps Engineer | 4 | 3 | 2 | 4 | 1 | 3 | 1 | 4 | 5 | 5 | 3 | 2 | 37 |
| Network Engineer | 4 | 2 | 3 | 5 | 1 | 3 | 2 | 4 | 2 | 5 | 3 | 3 | 37 |
| Cybersecurity Analyst | 5 | 4 | 2 | 5 | 1 | 3 | 1 | 3 | 2 | 3 | 5 | 3 | 37 |
| System Analyst | 4 | 3 | 2 | 4 | 2 | 3 | 5 | 5 | 1 | 1 | 2 | 5 | 37 |

## 6a. Pengujian Kesehatan Matriks

Metrik (lihat `app/Services/Spk/MatrixHealthAnalyzer.php`):
- Self-consistency: input = profil karier → karier tsb wajib #1, margin ≥ 2.0 (poin).
- Robustness: rating = profil + noise {-1,0,+1} (clip 1-5), 2000 sampel, seed 42 → tiap karier ≥ 80% jadi #1 tanpa seri (margin > 1e-9).
- Nearest neighbors: jarak Euclidean antar profil → jarak terdekat ≥ 3.0.
- Tie cases: rating seragam (1,3,5) dan satu-kriteria-tinggi (12 kasus).

Hasil pada matriks D-23: self-consistency semua rank 1, margin min 2.16 (web_developer, devops_engineer); robustness min 81.05% (qa_engineer); jarak terdekat 3.162 (web_developer↔qa_engineer). Matriks lulus ambang `MatrixHealthTest`.

## 7b. Perubahan dari Versi Sesi 3 (D-22)

- `web_developer`: 4,2,2,3,3,4,4,3,5,2,2,3 -> 3,2,1,3,3,4,4,4,5,2,2,4
- `mobile_developer`: 4,2,2,4,4,3,3,3,5,1,3,3 -> 4,2,2,4,4,3,3,2,5,1,4,3
- `qa_engineer`: 4,3,2,5,2,3,4,3,3,2,3,3 -> 4,3,1,5,2,3,4,4,3,1,3,4
- Total tiap karier tetap 37.

## 7. Validasi Data (Wajib Sebelum Produksi)
## 7a. Perubahan dari Versi Sesi 0

- Kriteria `self_discipline` dan `teamwork` diganti `coding` dan `user_empathy`.
- Urutan kriteria dikelompokkan menjadi 3 langkah x 4 kriteria.
- Bobot admin (`criteria.weight`) ditambahkan sebagai pengali (D-20).
- Tie-break deterministik ditambahkan (D-21).
- Matriks diseimbangkan sehingga total tiap karier = 37.



## 7. Validasi Data (Wajib Sebelum Produksi)

- Bandingkan dengan roadmap.sh, deskripsi lowongan, SFIA/okupasi resmi.
- Catat sumber yang benar-benar diverifikasi; jika tidak bisa verifikasi, tulis "belum tervalidasi".
- Simpan hasil validasi di `docs/SPK-METHOD.md` (revisi) atau dokumen terpisah.
