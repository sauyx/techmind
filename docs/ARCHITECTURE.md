# ARCHITECTURE

## 1. Pola Umum

Arsitektur berlapis ketat:

`Migration -> Model -> Repository -> Service -> Controller -> Route -> Inertia -> React Page -> Test`

- Tidak ada API terpisah.
- Tidak ada SPA baru.
- Controller tipis; logika bisnis di Service.
- Repository mengakses Eloquent/DB; Service tidak query langsung.
- Frontend React menerima props via Inertia.

## 2. Direktori (Target)

```
app/
  Http/Controllers/Public/
  Http/Controllers/Admin/
  Http/Middleware/
  Models/
  Repositories/
  Services/
  Contracts/
  Providers/
resources/js/
  Pages/
  Components/
  Layouts/
  Types/
  Lib/
routes/
  web.php
database/
  migrations/
  seeders/
  factories/
tests/
  Feature/
  Unit/
```

## 3. Repository

- `CriterionRepository`
- `CareerRepository`
- `CareerProfileRepository`
- `TestSessionRepository`
- `TestAnswerRepository`
- `TestResultRepository`
- `VisitorFeedbackRepository`
- `AiExplanationRepository`
- `UserRepository` (admin)

## 4. Service

- `QuestionnaireService` (mulai tes, simpan jawaban, validasi kelengkapan)
- `SpkCalculationService` (implementasi SAW)
- `RecommendationService` (orkestrasi hitung + snapshot + simpan hasil)
- `CareerDetailService`
- `AiExplanationService` (pilih provider, fallback, logging, cache)
- `PdfExportService`
- `StatisticsService`
- `AdminCriterionService`, `AdminCareerService`, `AdminProfileService`
- `FeedbackService`

## 5. Contract (Interface)

- `App\Contracts\AiExplanationProvider`
  - `explain(ExplanationContext $context): string`
- Implementasi:
  - `OpenAiExplanationProvider` (HTTP client, timeout, retry)
  - `TemplateExplanationProvider` (fallback deterministik)
- `App\Contracts\SpkMethod` (opsional) dengan `SawMethod` (default). Memudahkan pengujian dan penggantian metode.

## 6. Controller

Publik:
- `HomeController`
- `TestController` (start, step, submit)
- `ResultController` (show by token)
- `FeedbackController` (store)
- `PdfController` (export)

Admin (auth + role admin):
- `Admin\DashboardController`
- `Admin\CriterionController`
- `Admin\CareerController`
- `Admin\CareerProfileController`
- `Admin\StatisticsController`
- `Admin\FeedbackExportController`

## 7. Route (Ringkas)

Publik:
- `GET /` -> landing
- `POST /tests` -> mulai sesi (throttle)
- `GET /tests/{token}` -> halaman tes
- `POST /tests/{token}/answers` -> simpan jawaban (throttle)
- `GET /results/{token}` -> hasil
- `GET /results/{token}/pdf` -> PDF
- `POST /results/{token}/feedback` -> penilaian (throttle)

Admin:
- `GET /admin/login`, `POST /admin/login`, `POST /admin/logout`
- `GET /admin/dashboard`
- `resource /admin/criteria`
- `resource /admin/careers`
- `GET/PUT /admin/careers/{career}/profiles`
- `GET /admin/statistics`
- `GET /admin/feedbacks/export`

## 8. Halaman React (Inertia)

Publik:
- `Pages/Welcome.tsx`
- `Pages/Test/Show.tsx` (multi-step)
- `Pages/Result/Show.tsx`
- `Pages/Feedback/ThankYou.tsx` (opsional)

Admin:
- `Pages/Admin/Dashboard.tsx`
- `Pages/Admin/Criteria/Index.tsx`, `Form.tsx`
- `Pages/Admin/Careers/Index.tsx`, `Form.tsx`
- `Pages/Admin/Profiles/Edit.tsx`
- `Pages/Admin/Statistics/Index.tsx`
- `Pages/Admin/Feedbacks/Index.tsx`

## 9. Komponen UI

- `Components/QuestionCard.tsx`
- `Components/ScaleInput.tsx`
- `Components/ProgressBar.tsx`
- `Components/RadarProfileChart.tsx`
- `Components/CareerCard.tsx`
- `Components/RoadmapList.tsx`
- `Components/ScoreBadge.tsx`
- `Layouts/PublicLayout.tsx`
- `Layouts/AdminLayout.tsx`

## 10. Strategi AI

- `AiExplanationService` menerima `ExplanationContext` (hasil ranking, profil, gap).
- Coba provider utama dengan timeout (mis. 6 detik) dan 1 retry.
- Jika gagal/timeout/rate limit -> `TemplateExplanationProvider`.
- Simpan log di `ai_explanations` (provider, model, status, latency, error).
- Cache hasil penjelasan berdasarkan hash input (TTL 24 jam) untuk menghindari panggilan berulang.
- Prompt version disimpan untuk audit.

## 11. Caching

- Cache kriteria aktif, karier aktif, dan profil (TTL 10 menit / tag) untuk endpoint publik.
- Invalidasi cache saat admin update.
- Cache penjelasan AI per hash hasil.

## 12. Rate Limit

- Mulai tes: 10/menit per IP.
- Submit jawaban: 10/menit per token/IP.
- Minta penjelasan AI: 5/menit per token.
- Feedback: 5/menit per IP.
- Admin login: throttle bawaan Laravel.

## 13. Testing

- Unit: `SpkCalculationService` (fixture deterministik), normalisasi, ranking, tie-break.
- Feature: alur tes, hasil, feedback, admin CRUD, auth, PDF response, fallback AI.
- Gunakan Pest.
