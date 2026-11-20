# PR Summary — SPK matrix D-23, structured AI, booth hardening

## Area
- SPK & matriks: D-23 career matrix, matrix health tooling, tie handling.
- AI & template: structured JSON explanations, ExplanationPayloadBuilder, TemplateAiExplainer natural output, tolerant percentage validation.
- Performa & cache: reference data cache with invalidation, php-fpm tuning, memory limits.
- Keamanan & rate limit: named RateLimiter, TrustProxies, CSRF on /tes, 429 view.
- Admin: career impact check dialog, criteria development_hint.
- Deploy: dev vs prod env separation, docker-compose.prod name, deploy guide.
- Test: 86 tests, ci:check.
- Dokumentasi: laporan, deploy, PR summary.

## Migration baru (berurutan)
- 2026_11_20_000001_add_status_to_ai_explanations_table
- 2026_11_20_000002_add_indexes_for_admin_lists
- 2026_11_20_000003_add_format_to_ai_explanations_table
- 2026_11_20_000004_add_development_hint_to_criteria_table

## Upgrade DB lama
php artisan migrate --force

## Risiko
- PHPStan 197 error pre-existing (missing generics).
- Rate limit per IP 120/menit dapat membatasi loadtest 150 alur.
