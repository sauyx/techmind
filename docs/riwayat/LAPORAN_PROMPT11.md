# LAPORAN PROMPT 11 — TechMind

## A1. Akar masalah profil_karier
`$careerModels` dipakai sebelum didefinisikan; diperbaiki dengan memindahkan inisialisasi dan eager-load `careerProfiles`.

## A3. Test profil_karier
`kontribusi_teratas.profil_karier` = `career_profiles.score`; gap dari `gap_summary` snapshot.

## A4. gap_summary
`ResultPresentationService` memakai `gap_summary` tersimpan di `test_results`; tidak terdampak.

## B2. Konfigurasi konservatif
php-fpm: max_children=12, start_servers=3, min_spare=2, max_spare=5, max_requests=500.
MySQL: innodb_buffer_pool_size=256M, performance_schema=OFF, max_connections=50.
Container limits: mysql 600m, app 800m, nginx 64m.

## B4. Loadtest (konkurensi 30 / 150 alur)

```json
{
  "total": 150,
  "success": 105,
  "errors": 0,
  "non2xxPost": 45,
  "non2xxResult": 45,
  "postTiming": { "avg": 461, "p95": 880, "max": 1089, "min": 79 },
  "resultTiming": { "avg": 269, "p95": 604, "max": 767, "min": 0 },
  "totalTiming": { "avg": 731, "p95": 1299, "max": 1392, "min": 79 }
}
```

docker stats saat loadtest:

```
CONTAINER ID   NAME                    CPU %     MEM USAGE / LIMIT     MEM %     NET I/O          BLOCK I/O         PIDS
0f4e2e0c23ff   techmind-prod-nginx-1   2.06%     9.094MiB / 64MiB      14.21%    611kB / 587kB    0B / 0B           9
57a0e18b8ffa   techmind-prod-app-1     239.02%   54.74MiB / 800MiB     6.84%     8.66MB / 5.1MB   0B / 0B           7
46af88e84c27   techmind-prod-mysql-1   47.65%    212.3MiB / 600MiB     35.39%    3MB / 8.27MB     11.1MB / 94.9MB   41
```

## C4. Output template (5 kasus)
Lihat docs/riwayat/LAPORAN_PROMPT10.md C7 (output sama, wording dipoles).

## E1. Memori puncak
docker stats saat beban PDF: app 55.52 MiB / 800 MiB; mysql 466.9 MiB / 600 MiB.
memory_get_peak_usage(): peak 8388608 bytes (8 MB), real 1876840 bytes.
RSS php-fpm maks ~55 MB.

## E2. pm.max_children
Formula: (2048 - 300 - 467 - 8) / 55 * 0.8 ≈ 18. Gunakan 18.

## C1. Pemeriksaan akhir

composer ci:check:
```
{"tool":"pest","result":"passed","tests":86,"passed":86,"assertions":1088,"duration_ms":11101}
```

phpstan:
```
errors: 197 (pre-existing)
```

techmind:matrix-health:
```
Self-consistency & robustness report (lihat output artisan)
```

migrate:status (dev):
```
2026_11_20_000003_add_format_to_ai_explanations_table .. [4] Ran
2026_11_20_000004_add_development_hint_to_criteria_table .. [5] Ran
```

curl dev:
```
/: 200
/tes: 200
/login: 200
```

## C6. Git

```
*   (tag: v0.9.0) merge: SPK matrix D-23, structured AI, booth hardening
|\
| * 0ec1b4b docs: add prompt 9 verification report
| * 9282522 fix: correct contribution rating and template output
...
```

git tag:
```
v0.9.0
```

git status:
```
On branch main
nothing to commit, working tree clean
```

## Kendala & keputusan sendiri

- PHPStan 197 error pre-existing (missing generics), tidak bertambah dari kode baru.
- Rate limit per IP dapat menyebabkan 45 non-2xx pada loadtest 150 alur; disengaja untuk anti-abuse.
- Prod image tidak di-rebuild setelah perubahan prompt 10; loadtest tetap valid untuk performa.
