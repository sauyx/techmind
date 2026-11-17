<?php

namespace Database\Seeders;

use App\Contracts\AiExplanationRepository;
use App\Contracts\CareerRepository;
use App\Contracts\VisitorFeedbackRepository;
use App\Models\TestSession;
use App\Services\Ai\TemplateAiExplainer;
use App\Services\TestSubmissionService;
use Illuminate\Database\Seeder;
use RuntimeException;

class DemoSeeder extends Seeder
{
    private const NAMES = [
        'Alya Prameswari',
        'Bagas Ramadhan',
        'Citra Lestari',
        'Dimas Anggara',
        'Eka Saputra',
        null,
        'Fajar Nugroho',
        'Gita Maharani',
        'Hadi Kusuma',
        'Intan Permata',
        'Joko Santoso',
        null,
        'Kirana Putri',
        'Lukman Hakim',
        'Maya Anggraini',
        'Nanda Prasetyo',
        null,
        'Rani Oktaviani',
    ];

    private const SCORES = [86, 92, 79, 95, 88, 81, 97, 84, 90, 83, 94, 80, 89, 96, 82, 91, 87, 93];

    private const COMMENTS = [
        'Hasilnya mudah dipahami dan relevan dengan minat saya.',
        'Tampilan ringkas, alur pengisiannya cepat.',
        'Rekomendasi karier membantu saya melihat pilihan lain.',
        'Penjelasan kecocokan terasa masuk akal.',
        'Saya jadi lebih tahu kriteria yang perlu diperkuat.',
        'Pertanyaannya jelas dan tidak membingungkan.',
        'Grafik perbandingan profil membantu membaca hasil.',
        'Alurnya nyaman digunakan di ponsel.',
        'Roadmap belajar menjadi nilai tambah.',
        'Hasil analisis cukup terperinci.',
        'Saya suka ringkasan kekuatan dan area pengembangan.',
        'Prosesnya selesai dalam beberapa menit.',
        'Pilihan karier yang ditampilkan cukup beragam.',
        'Halaman hasil rapi dan mudah dipindai.',
        'Penjelasan metode SAW membuat hasil lebih transparan.',
        'Form penilaian mudah digunakan.',
        'Saya merekomendasikan alat ini ke teman satu angkatan.',
        'Secara keseluruhan pengalaman pengisian menyenangkan.',
    ];

    private const SUGGESTIONS = [
        'Tambahkan contoh proyek nyata untuk setiap karier.',
        'Sertakan estimasi kebutuhan skill dasar di roadmap.',
        'Beri opsi membandingkan dua karier sekaligus.',
        'Tambahkan informasi prospek kerja di Indonesia.',
        'Sediakan tautan sumber belajar yang sudah dikurasi.',
        'Perjelas istilah teknis pada halaman hasil.',
        'Tambahkan mode gelap yang konsisten di masa depan.',
        'Sertakan ringkasan yang bisa dibagikan ke media sosial.',
        'Tambahkan contoh portofolio sederhana per karier.',
        'Beri penjelasan singkat untuk setiap kriteria penilaian.',
        'Sediakan opsi ekspor ringkasan ke PDF yang lebih ringkas.',
        'Tambahkan simulasi perubahan jawaban terhadap hasil.',
        'Sertakan tips wawancara untuk karier teratas.',
        'Tambahkan daftar komunitas belajar yang relevan.',
        'Perbanyak contoh roadmap untuk pemula.',
        'Beri opsi menyimpan hasil dengan akun ringan.',
        'Tambahkan penjelasan tentang keterbatasan hasil analisis.',
        'Sertakan versi cetak satu halaman untuk pameran.',
    ];

    public function run(): void
    {
        if (app()->environment('production') && ! config('techmind.demo_mode')) {
            throw new RuntimeException('DemoSeeder tidak diizinkan berjalan di production tanpa TECHMIND_DEMO_MODE=true.');
        }

        $namedVisitors = array_values(array_filter(self::NAMES));

        if (TestSession::query()->whereIn('visitor_name', $namedVisitors)->exists()) {
            $this->command?->info('Data demo sudah tersedia. Seeder dilewati.');

            return;
        }

        $careers = app(CareerRepository::class)->activeWithProfiles()->values();
        $submission = app(TestSubmissionService::class);
        $template = app(TemplateAiExplainer::class);
        $explanations = app(AiExplanationRepository::class);
        $feedbacks = app(VisitorFeedbackRepository::class);

        if ($careers->isEmpty()) {
            throw new RuntimeException('Data karier aktif belum tersedia. Jalankan ReferenceDataSeeder terlebih dahulu.');
        }

        foreach (self::NAMES as $index => $name) {
            $career = $careers[$index % $careers->count()];

            $ratings = [];
            foreach ($career->careerProfiles as $profile) {
                if ($profile->criterion) {
                    $ratings[(string) $profile->criterion->code] = (int) $profile->score;
                }
            }

            $session = $submission->submit($name, $ratings);
            $session->load(['answers', 'results']);

            $result = $template->explain($session);
            $explanations->store($session, $result);

            $feedbacks->updateOrCreate($session, [
                'score' => self::SCORES[$index % count(self::SCORES)],
                'comment' => self::COMMENTS[$index % count(self::COMMENTS)],
                'suggestion' => self::SUGGESTIONS[$index % count(self::SUGGESTIONS)],
            ]);
        }

        $this->command?->info('18 sesi demo berhasil dibuat.');
    }
}
