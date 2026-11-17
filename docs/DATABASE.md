# DATABASE

## 1. ERD (Mermaid)

```mermaid
erDiagram
    users {
        bigint id PK
        string name
        string email UK
        string password
        string role
        timestamp created_at
        timestamp updated_at
    }
    criteria {
        bigint id PK
        string code UK
        string name
        text description
        decimal weight
        int sort_order
        boolean is_active
        timestamp created_at
        timestamp updated_at
    }
    careers {
        bigint id PK
        string slug UK
        string name
        text summary
        json skills
        json tools
        json roadmap
        boolean is_active
        int sort_order
        timestamp created_at
        timestamp updated_at
    }
    career_profiles {
        bigint id PK
        bigint career_id FK
        bigint criterion_id FK
        tinyint ideal_score
        timestamp created_at
        timestamp updated_at
    }
    test_sessions {
        bigint id PK
        string token UK
        string visitor_name
        string visitor_email
        string status
        string ip_hash
        string user_agent_hash
        timestamp started_at
        timestamp completed_at
        timestamp created_at
        timestamp updated_at
    }
    test_answers {
        bigint id PK
        bigint test_session_id FK
        bigint criterion_id
        string criterion_code
        string criterion_name
        tinyint rating
        decimal weight_snapshot
        timestamp created_at
    }
    test_results {
        bigint id PK
        bigint test_session_id FK
        string method_code
        json ranking
        string top_career_slug
        string top_career_name
        bigint ai_explanation_id
        timestamp created_at
    }
    visitor_feedbacks {
        bigint id PK
        bigint test_session_id FK
        bigint test_result_id FK
        tinyint score
        text comment
        text suggestion
        string ip_hash
        timestamp created_at
    }
    ai_explanations {
        bigint id PK
        bigint test_result_id FK
        string provider
        string model
        string prompt_version
        string status
        longtext content
        text error_message
        int latency_ms
        timestamp created_at
    }

    users ||--o{ } : "admin only"
    careers ||--|{ career_profiles : has
    criteria ||--|{ career_profiles : used_by
    test_sessions ||--|{ test_answers : has
    test_sessions ||--|| test_results : has
    test_results ||--o{ visitor_feedbacks : has
    test_results ||--o{ ai_explanations : has
```

## 2. Tabel dan Kolom

### users
- `id` bigint PK
- `name` varchar(255) not null
- `email` varchar(255) unique not null
- `password` varchar(255) not null
- `role` varchar(32) not null default `admin`
- `remember_token` varchar(100) nullable
- `created_at`, `updated_at` timestamp

### criteria
- `id` bigint PK
- `code` varchar(64) unique not null
- `name` varchar(128) not null
- `description` text nullable
- `weight` decimal(5,2) not null default 1.00
- `sort_order` int not null default 0
- `is_active` boolean not null default true
- `created_at`, `updated_at` timestamp
- Index: `is_active`, `sort_order`

### careers
- `id` bigint PK
- `slug` varchar(96) unique not null
- `name` varchar(128) not null
- `summary` text nullable
- `skills` json nullable
- `tools` json nullable
- `roadmap` json nullable
- `is_active` boolean not null default true
- `sort_order` int not null default 0
- `created_at`, `updated_at` timestamp
- Index: `is_active`, `sort_order`

### career_profiles
- `id` bigint PK
- `career_id` bigint FK -> careers.id (cascade delete)
- `criterion_id` bigint FK -> criteria.id (cascade delete)
- `ideal_score` tinyint unsigned not null (1..5)
- `created_at`, `updated_at` timestamp
- Unique: (`career_id`, `criterion_id`)
- Index: `criterion_id`

### test_sessions
- `id` bigint PK
- `token` varchar(64) unique not null (UUID v4)
- `visitor_name` varchar(128) nullable
- `visitor_email` varchar(255) nullable
- `status` varchar(16) not null default `in_progress` (`in_progress`, `completed`, `abandoned`)
- `ip_hash` char(64) nullable
- `user_agent_hash` char(64) nullable
- `started_at` timestamp nullable
- `completed_at` timestamp nullable
- `created_at`, `updated_at` timestamp
- Index: `status`, `completed_at`, `created_at`

### test_answers
- `id` bigint PK
- `test_session_id` bigint FK -> test_sessions.id (cascade delete)
- `criterion_id` bigint nullable FK -> criteria.id (set null on delete)
- `criterion_code` varchar(64) not null
- `criterion_name` varchar(128) not null
- `rating` tinyint unsigned not null (1..5)
- `weight_snapshot` decimal(5,2) not null
- `created_at` timestamp
- Unique: (`test_session_id`, `criterion_code`)
- Index: `test_session_id`

### test_results
- `id` bigint PK
- `test_session_id` bigint unique FK -> test_sessions.id (cascade delete)
- `method_code` varchar(32) not null (default `SAW`)
- `ranking` json not null (array of {career_slug, career_name, score, contributions?})
- `top_career_slug` varchar(96) not null
- `top_career_name` varchar(128) not null
- `ai_explanation_id` bigint nullable FK -> ai_explanations.id (set null)
- `created_at` timestamp
- Index: `top_career_slug`, `created_at`

### visitor_feedbacks
- `id` bigint PK
- `test_session_id` bigint nullable FK -> test_sessions.id (set null)
- `test_result_id` bigint nullable FK -> test_results.id (set null)
- `score` tinyint unsigned not null (0..100)
- `comment` text nullable
- `suggestion` text nullable
- `ip_hash` char(64) nullable
- `created_at` timestamp
- Index: `test_result_id`, `score`, `created_at`

### ai_explanations
- `id` bigint PK
- `test_result_id` bigint FK -> test_results.id (cascade delete)
- `provider` varchar(64) not null (`openai`, `template`)
- `model` varchar(64) nullable
- `prompt_version` varchar(32) not null
- `status` varchar(16) not null (`success`, `fallback`, `failed`)
- `content` longtext not null
- `error_message` text nullable
- `latency_ms` int unsigned nullable
- `created_at` timestamp
- Index: `test_result_id`, `status`, `created_at`

## 3. Snapshot Strategy

- `test_answers.weight_snapshot` menyimpan bobot kriteria saat tes.
- `test_answers.criterion_code/name` menyimpan identitas kriteria saat tes.
- `test_results.ranking` menyimpan skor dan profil karier yang dipakai (slug, nama, skor, kontribusi per kriteria).
- Dengan snapshot, perubahan bobot/profil/karier oleh admin tidak mengubah hasil lama.

## 4. Retensi dan Privasi

- Simpan `ip_hash` (SHA-256 + salt app), bukan IP mentah.
- Email pengunjung opsional; jika disimpan, tampilkan kebijakan privasi.
- Retensi data tes: default 90 hari (dapat diubah admin) untuk demo; feedback dapat disimpan lebih lama.
