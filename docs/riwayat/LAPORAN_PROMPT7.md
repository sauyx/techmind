# LAPORAN PROMPT 7 — TechMind

## A2. route:list

```
GET|HEAD admin .. admin.dashboard
GET|HEAD admin/booth .. admin.booth
GET|HEAD admin/careers .. admin.careers.index
POST admin/careers .. admin.careers.store
GET|HEAD admin/careers/create admin.careers.create
PUT admin/careers/{career} admin.careers.update
GET|HEAD admin/careers/{career}/edit admin.careers.edit
PATCH admin/careers/{career}/toggle admin.careers.toggle
GET|HEAD admin/criteria admin.criteria.index
PUT admin/criteria/{criterion} admin.criteria.update
GET|HEAD admin/feedback admin.feedbacks.index
GET|HEAD admin/feedbacks/export admin.feedbacks.export
GET|HEAD admin/kesehatan-matriks admin.matrix-health.index
POST admin/kesehatan-matriks/preview admin.matrix-health.preview
GET|HEAD admin/sesi admin.sessions.index
GET|HEAD admin/sesi/{session} admin.sessions.show
DELETE admin/sesi/{session} admin.sessions.destroy
GET|HEAD settings/profile .. profile.edit
PATCH settings/profile profile.update
PUT settings/password user-password.update
```

## A3. Migration index

File: `database/migrations/2026_11_20_000002_add_indexes_for_admin_lists.php`

```php
public function up(): void
{
    Schema::table('test_sessions', function (Blueprint $table) {
        $table->index('created_at');
    });

    Schema::table('visitor_feedbacks', function (Blueprint $table) {
        $table->index('created_at');
    });
}
```

## A4. techmind:matrix-health (potongan)

```
Self-consistency: semua karier rank 1; margin min 2.16.
Robustness: min 81.05% (qa_engineer).
Nearest neighbors: jarak terdekat 3.162.
Tie cases: maks seri rank #1 = 10.
```

## B3. Uji beban php-fpm (konkurensi 10, 50 alur)

```
total: 50
success: 50
errors: 0
non2xxPost: 0
non2xxResult: 0
postTiming avg 288, p95 397, max 544, min 193
resultTiming avg 202, p95 332, max 380, min 56
totalTiming avg 491, p95 649, max 711, min 367
```

Konkurensi 30, 150 alur: 78 sukses, 72 non-2xx (rate limit 30/min).

## C1. Prompt AI (OpenAiCompatibleExplainer)

System:
```
Anda adalah asisten karier yang ramah. Tulis penjelasan ringkas dalam Bahasa Indonesia, maksimal 180 kata, tanpa markdown berlebihan, tanpa emoji, dan tanpa mengidentifikasi identitas pengguna.
```

User (contoh):
```
Jawab hanya dengan paragraf penjelasan. Data anonim: skor kriteria 1-5 {...} dan tiga karier teratas [{...}]. <tieNote jika seri>
```

## C2. Contoh output TemplateAiExplainer

Normal (backend profile):
```
Backend Developer memiliki skor kecocokan tertinggi, yaitu 75.1%. ...
Peringkat kedua dan ketiga yang juga layak Anda pertimbangkan adalah DevOps Engineer (70.8%) dan Cybersecurity Analyst (70.3%).
```

Seri (semua rating 3):
```
Beberapa karier memiliki tingkat kecocokan yang sama, yaitu Web Developer, Mobile Developer, ... Jawaban Anda cenderung merata...
Karier-karier yang setara di atas layak Anda pertimbangkan bersama.
```

## C3. Contoh output `techmind:ai-check` (template)

```
Driver: template
Base URL host: -
Model:
Status HTTP: n/a (template)
Latensi: 5 ms
Attempts: 1
Output (300 char): Web Developer memiliki skor kecocokan tertinggi, yaitu 75.0%. ...
```

## D1. PHPStan

- 169 error tersisa (pre-existing: missing type hints/generics). Error dari kode baru diperbaiki. Dijalankan dengan `--memory-limit=512M`.

## Git

- Branch: `feat/spk-matrix-d23`.
- `git status`: bersih.
- `git log --oneline -15`: lihat commit terbaru.

## Kendala & keputusan

- Cache Eloquent di file store menyebabkan `__PHP_Incomplete_Class`; diperbaiki dengan menyimpan array dan merekonstruksi model via `forceFill`.
- Throttle 30/min membatasi uji beban 150 alur; hasil 72 non-2xx karena rate limit.
- Cek Dampak UI diwiring di halaman kriteria (endpoint preview); integrasi form karier in-memory dapat dikembangkan lanjut.
