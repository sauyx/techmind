# Audit SPK TechMind — Sesi 3C

## 1. Ringkasan Eksekutif

Audit dilakukan tanpa mengubah matriks, bobot, formula, persona, atau threshold. Hasil audit menunjukkan implementasi SAW sesuai spesifikasi. Kegagalan dua persona Web Developer pada `FairnessTest` bukan disebabkan bug implementasi, melainkan konsekuensi kesamaan profil dan definisi persona yang kurang membedakan Web Developer dari Mobile Developer dan UI/UX Designer.

## 2. Hasil Audit Formula

Implementasi `SawCalculator` diverifikasi terhadap `docs/SPK-METHOD.md`:

- Validasi rating: bilangan bulat 1-5, semua kriteria aktif wajib ada, tidak ada kode asing.
- Bobot: `w_j = (r_j * m_j) / SUM(r_k * m_k)`, dengan `m_j = criteria.weight`.
- Normalisasi: `r_ij = x_ij / max(x_ij)` di antara karier aktif.
- Skor: `V_i = SUM(w_j * r_ij)`.
- Ranking: descending `V_i`; tie-break `sort_order` ascending jika selisih < `1e-9`.
- Karier nonaktif tidak dikirim ke calculator oleh repository; max dihitung hanya dari karier aktif.
- Presisi penuh disimpan, tampilan dibulatkan 1 desimal.
- Tidak ada query DB di calculator; data diambil melalui repository.

Kesimpulan: formula implementasi konsisten dengan dokumentasi. Tidak ditemukan bug.

Catatan: validasi rentang `criteria.weight` 0.50-2.00 baru ada di calculator, belum ada validasi model/request admin karena CRUD admin belum diimplementasikan (sesuai rencana Sesi 7).

## 3. Breakdown Skor Dua Persona Web Developer

### Persona 1: `4,2,2,3,3,3,3,3,5,2,3,2`

Ranking lengkap:

| Peringkat | Karier | Skor |
|---:|---|---:|
| 1 | Mobile Developer | 0.6685714286 |
| 2 | Web Developer | 0.6571428571 |
| 3 | Backend Developer | 0.6457142857 |
| 4 | QA Engineer | 0.6400000000 |
| 5 | DevOps Engineer | 0.6285714286 |
| 6 | UI/UX Designer | 0.6228571429 |
| 7 | Network Engineer | 0.6228571429 |
| 8 | Cybersecurity Analyst | 0.6228571429 |
| 9 | Data Analyst | 0.6114285714 |
| 10 | System Analyst | 0.6057142857 |

Selisih Mobile Developer - Web Developer: `0.0114285714`.

Kontribusi Web Developer:

| Kriteria | Rating | Profil Web | Norm | Bobot | Kontribusi |
|---|---:|---:|---:|---:|---:|
| logic_problem_solving | 4 | 3 | 0.6000 | 0.114286 | 0.068571 |
| data_analysis | 2 | 2 | 0.4000 | 0.057143 | 0.022857 |
| math_statistics | 2 | 1 | 0.2000 | 0.057143 | 0.011429 |
| attention_detail | 3 | 3 | 0.6000 | 0.085714 | 0.051429 |
| visual_design | 3 | 3 | 0.6000 | 0.085714 | 0.051429 |
| creativity | 3 | 4 | 0.8000 | 0.085714 | 0.068571 |
| user_empathy | 3 | 4 | 0.8000 | 0.085714 | 0.068571 |
| communication | 3 | 4 | 0.8000 | 0.085714 | 0.068571 |
| coding | 5 | 5 | 1.0000 | 0.142857 | 0.142857 |
| network_infra | 2 | 2 | 0.4000 | 0.057143 | 0.022857 |
| security_awareness | 3 | 2 | 0.4000 | 0.085714 | 0.034286 |
| business_process | 2 | 4 | 0.8000 | 0.057143 | 0.045714 |

Kontribusi Mobile Developer:

| Kriteria | Rating | Profil Mobile | Norm | Bobot | Kontribusi |
|---|---:|---:|---:|---:|---:|
| logic_problem_solving | 4 | 4 | 0.8000 | 0.114286 | 0.091429 |
| data_analysis | 2 | 2 | 0.4000 | 0.057143 | 0.022857 |
| math_statistics | 2 | 2 | 0.4000 | 0.057143 | 0.022857 |
| attention_detail | 3 | 4 | 0.8000 | 0.085714 | 0.068571 |
| visual_design | 3 | 4 | 0.8000 | 0.085714 | 0.068571 |
| creativity | 3 | 3 | 0.6000 | 0.085714 | 0.051429 |
| user_empathy | 3 | 3 | 0.6000 | 0.085714 | 0.051429 |
| communication | 3 | 2 | 0.4000 | 0.085714 | 0.034286 |
| coding | 5 | 5 | 1.0000 | 0.142857 | 0.142857 |
| network_infra | 2 | 1 | 0.2000 | 0.057143 | 0.011429 |
| security_awareness | 3 | 4 | 0.8000 | 0.085714 | 0.068571 |
| business_process | 2 | 3 | 0.6000 | 0.057143 | 0.034286 |

Kriteria yang paling membuat Web kalah: `logic_problem_solving` (-0.022857), `security_awareness` (-0.034286), `attention_detail` (-0.017143), `visual_design` (-0.017143), `math_statistics` (-0.011429).

### Persona 2: `3,2,1,3,4,4,3,4,4,2,2,3`

Ranking lengkap:

| Peringkat | Karier | Skor |
|---:|---|---:|
| 1 | UI/UX Designer | 0.6857142857 |
| 2 | Web Developer | 0.6800000000 |
| 3 | Mobile Developer | 0.6514285714 |
| 4 | QA Engineer | 0.6514285714 |
| 5 | System Analyst | 0.6400000000 |
| 6 | DevOps Engineer | 0.6057142857 |
| 7 | Network Engineer | 0.6057142857 |
| 8 | Data Analyst | 0.6000000000 |
| 9 | Cybersecurity Analyst | 0.6000000000 |
| 10 | Backend Developer | 0.5942857143 |

Selisih UI/UX Designer - Web Developer: `0.0057142857`.

Kontribusi Web Developer:

| Kriteria | Rating | Profil Web | Norm | Bobot | Kontribusi |
|---|---:|---:|---:|---:|---:|
| logic_problem_solving | 3 | 3 | 0.6000 | 0.085714 | 0.051429 |
| data_analysis | 2 | 2 | 0.4000 | 0.057143 | 0.022857 |
| math_statistics | 1 | 1 | 0.2000 | 0.028571 | 0.005714 |
| attention_detail | 3 | 3 | 0.6000 | 0.085714 | 0.051429 |
| visual_design | 4 | 3 | 0.6000 | 0.114286 | 0.068571 |
| creativity | 4 | 4 | 0.8000 | 0.114286 | 0.091429 |
| user_empathy | 3 | 4 | 0.8000 | 0.085714 | 0.068571 |
| communication | 4 | 4 | 0.8000 | 0.114286 | 0.091429 |
| coding | 4 | 5 | 1.0000 | 0.114286 | 0.114286 |
| network_infra | 2 | 2 | 0.4000 | 0.057143 | 0.022857 |
| security_awareness | 2 | 2 | 0.4000 | 0.057143 | 0.022857 |
| business_process | 3 | 4 | 0.8000 | 0.085714 | 0.068571 |

Kontribusi UI/UX Designer:

| Kriteria | Rating | Profil UI/UX | Norm | Bobot | Kontribusi |
|---|---:|---:|---:|---:|---:|
| logic_problem_solving | 3 | 3 | 0.6000 | 0.085714 | 0.051429 |
| data_analysis | 2 | 3 | 0.6000 | 0.057143 | 0.034286 |
| math_statistics | 1 | 1 | 0.2000 | 0.028571 | 0.005714 |
| attention_detail | 3 | 3 | 0.6000 | 0.085714 | 0.051429 |
| visual_design | 4 | 5 | 1.0000 | 0.114286 | 0.114286 |
| creativity | 4 | 5 | 1.0000 | 0.114286 | 0.114286 |
| user_empathy | 3 | 5 | 1.0000 | 0.085714 | 0.085714 |
| communication | 4 | 4 | 0.8000 | 0.114286 | 0.091429 |
| coding | 4 | 2 | 0.4000 | 0.114286 | 0.045714 |
| network_infra | 2 | 1 | 0.2000 | 0.057143 | 0.011429 |
| security_awareness | 2 | 1 | 0.2000 | 0.057143 | 0.011429 |
| business_process | 3 | 4 | 0.8000 | 0.085714 | 0.068571 |

Kriteria yang paling membuat Web kalah: `visual_design` (-0.045714), `creativity` (-0.022857), `user_empathy` (-0.017143), `data_analysis` (-0.011429). Web unggul pada `coding` (+0.068571), `network_infra` (+0.011429), `security_awareness` (+0.011429).

## 4. Perbandingan Profil Karier

### Web Developer vs Mobile Developer

Profil (12 kriteria):

| Kriteria | Web | Mobile |
|---|---:|---:|
| logic_problem_solving | 3 | 4 |
| data_analysis | 2 | 2 |
| math_statistics | 1 | 2 |
| attention_detail | 3 | 4 |
| visual_design | 3 | 4 |
| creativity | 4 | 3 |
| user_empathy | 4 | 3 |
| communication | 4 | 2 |
| coding | 5 | 5 |
| network_infra | 2 | 1 |
| security_awareness | 2 | 4 |
| business_process | 4 | 3 |

Analisis:
- Mobile unggul pada logika, ketelitian, desain visual, keamanan, dan matematika.
- Web unggul pada kreativitas, empati, komunikasi, jaringan, dan proses bisnis.
- Profil keduanya sangat mirip, terutama pada coding=5 dan skor tinggi pada kreativitas/empati.
- Perbedaan terbesar ada pada `security_awareness` (2 vs 4), `communication` (4 vs 2), `logic` (3 vs 4), dan `attention_detail` (3 vs 4).
- Karakteristik spesifik Web (browser, rendering, responsive, SEO, aksesibilitas) dan Mobile (platform native, device API, offline) belum terwakili secara eksplisit dalam 12 kriteria. Kriteria saat ini terlalu umum, sehingga perbedaan kedua karier bergantung pada skor yang relatif berdekatan.

### Web Developer vs UI/UX Designer

Profil (12 kriteria):

| Kriteria | Web | UI/UX |
|---|---:|---:|
| logic_problem_solving | 3 | 3 |
| data_analysis | 2 | 3 |
| math_statistics | 1 | 1 |
| attention_detail | 3 | 3 |
| visual_design | 3 | 5 |
| creativity | 4 | 5 |
| user_empathy | 4 | 5 |
| communication | 4 | 4 |
| coding | 5 | 2 |
| network_infra | 2 | 1 |
| security_awareness | 2 | 1 |
| business_process | 4 | 4 |

Analisis:
- UI/UX unggul kuat pada visual_design, creativity, user_empathy.
- Web unggul kuat pada coding, network_infra, security_awareness.
- Persona 2 memiliki rating tinggi pada visual_design (4), creativity (4), user_empathy (3), communication (4), dan coding (4). Karena rating dan profil UI/UX sangat tinggi pada kriteria visual/kreativitas/empati, UI/UX mendapat kontribusi lebih besar pada kriteria-kriteria tersebut.
- Web hanya unggul pada coding, tetapi rating coding persona 2 adalah 4, bukan 5, sehingga keunggulan Web tidak cukup menutup gap pada kriteria visual/kreativitas.
- Secara matematis, persona 2 lebih cocok ke UI/UX berdasarkan rating yang diberikan. Ini bukan bug perhitungan.

## 5. Analisis Distribusi Simulasi

Hasil simulasi 10.000 vektor (seed 42):

| Karier | Uniform % | Typical % | Peaky % |
|---|---:|---:|---:|
| UI/UX Designer | 16.99 | 17.59 | 18.54 |
| Data Analyst | 16.49 | 16.68 | 17.40 |
| Mobile Developer | 11.89 | 11.50 | 10.73 |
| System Analyst | 10.85 | 10.18 | 11.52 |
| Web Developer | 8.86 | 10.02 | 7.85 |
| Backend Developer | 9.75 | 9.52 | 7.99 |
| Network Engineer | 8.08 | 8.01 | 8.19 |
| DevOps Engineer | 7.77 | 7.65 | 8.03 |
| Cybersecurity Analyst | 6.07 | 5.35 | 6.26 |
| QA Engineer | 3.25 | 3.50 | 3.49 |

Analisis:
- UI/UX dan Data Analyst memang paling sering menang, tetapi tidak ekstrem (di bawah 20%) dan tidak melanggar target <25%.
- QA Engineer paling jarang menang (3.25-3.50%), namun masih di atas threshold 3%.
- Distribusi menunjukkan konsentrasi moderat pada karier dengan profil tinggi pada kriteria yang umum dirating tinggi (visual, data, komunikasi).
- Tidak ada indikasi kesalahan struktural yang menyebabkan satu karier mendominasi. Namun matriks memiliki bias natural terhadap karier dengan skor tinggi pada kriteria yang cenderung dirating tinggi oleh pengguna acak.
- Jika diinginkan distribusi lebih merata, perubahan harus dilakukan pada matriks profil, bukan pada formula.

## 6. Audit Kualitas Persona

### Persona 1 Web Developer

Rating: `4,2,2,3,3,3,3,3,5,2,3,2`

Analisis:
- Profil menunjukkan minat kuat pada coding (5) dan logika (4), dengan skor sedang pada visual, kreativitas, empati, dan komunikasi (3).
- Karakteristik ini sebenarnya lebih dekat ke Backend Developer atau Mobile Developer daripada Web Developer murni.
- Mobile Developer unggul karena profil Mobile lebih tinggi pada logic, attention_detail, visual_design, dan security_awareness, yang semuanya dirating cukup tinggi (3-4).
- Persona tidak memiliki pembeda yang cukup kuat untuk memisahkan Web dari Mobile.

### Persona 2 Web Developer

Rating: `3,2,1,3,4,4,3,4,4,2,2,3`

Analisis:
- Profil menunjukkan minat tinggi pada visual, kreativitas, komunikasi, dan coding.
- Ini lebih representatif untuk UI/UX Designer atau Full-stack dengan kecenderungan frontend/design.
- Karena rating visual/kreativitas/empati/communication tinggi, UI/UX Designer secara matematis lebih cocok.
- Persona ini kurang representatif untuk Web Developer murni yang lebih menekankan coding, logika, dan infrastruktur.

Kesimpulan audit persona:
- Kedua persona Web Developer tidak cukup membedakan Web Developer dari Mobile Developer dan UI/UX Designer.
- Masalah utama ada pada definisi persona, bukan pada formula atau matriks.

## 7. Daftar Temuan

| ID | Temuan | Klasifikasi | Prioritas |
|---|---|---|---|
| F-01 | Persona 1 Web Developer lebih cocok ke Mobile Developer. | C | Tinggi |
| F-02 | Persona 2 Web Developer lebih cocok ke UI/UX Designer. | C | Tinggi |
| F-03 | Profil Web Developer dan Mobile Developer sangat mirip. | B | Sedang |
| F-04 | Kriteria belum membedakan karakteristik spesifik Web vs Mobile. | D | Sedang |
| F-05 | UI/UX dan Data Analyst sedikit dominan pada distribusi acak. | B | Rendah |
| F-06 | QA Engineer memiliki persentase rank 1 terendah (3.25-3.50%). | B | Rendah |
| F-07 | `criteria.weight` belum divalidasi rentangnya di model/request. | A/E | Rendah |

## 8. Rekomendasi

Rekomendasi yang disarankan (tanpa perubahan pada sesi ini):

1. Pertahankan formula SAW; tidak ditemukan kesalahan implementasi.
2. Pertahankan matriks saat ini untuk Sesi 4; perubahan matriks sebaiknya diputuskan setelah validasi sumber.
3. Persona Web Developer perlu ditulis ulang pada sesi validasi data, dengan fokus pada karakteristik yang membedakan Web dari Mobile dan UI/UX (misalnya: browser/rendering, responsive, aksesibilitas, API integration).
4. Jika ingin distribusi lebih merata, pertimbangkan penyesuaian matriks pada sesi tuning data, bukan perubahan formula.
5. Tambahkan validasi `criteria.weight` rentang 0.50-2.00 pada model/request saat CRUD admin diimplementasikan.

Risiko perubahan:
- Mengubah matriks dapat mempengaruhi hasil tes keadilan dan perlu re-validasi.
- Mengubah persona dapat membuat tes lulus secara artifisial jika tidak hati-hati.
- Menambah kriteria baru akan mempengaruhi seluruh matriks dan kuesioner.

## 9. Kesimpulan

Sistem layak masuk Sesi 4. Tidak ditemukan bug pada implementasi SPK. Kegagalan dua persona Web Developer disebabkan oleh definisi persona yang kurang membedakan Web Developer dari Mobile Developer dan UI/UX Designer, bukan oleh formula atau matriks. Distribusi simulasi masih dalam batas yang wajar dan tidak melanggar threshold yang ditetapkan.
