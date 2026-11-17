import { ClipboardList, Cpu, Compass } from 'lucide-react';

const steps = [
    {
        number: '01',
        icon: ClipboardList,
        title: 'Kenali dirimu',
        description:
            'Pengguna menjawab pertanyaan mengenai minat dan kecenderungan kemampuan.',
    },
    {
        number: '02',
        icon: Cpu,
        title: 'Analisis kecocokan',
        description:
            'Sistem menganalisis jawaban menggunakan metode Simple Additive Weighting.',
    },
    {
        number: '03',
        icon: Compass,
        title: 'Eksplorasi hasil',
        description:
            'Pengguna melihat urutan rekomendasi dan alasan kecocokan secara transparan.',
    },
];

export function HowItWorks() {
    return (
        <section id="cara-kerja" className="scroll-mt-20 border-t border-zinc-200 bg-zinc-50">
            <div className="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:py-20">
                <div className="max-w-2xl">
                    <h2 className="text-3xl font-semibold tracking-tight text-zinc-950 sm:text-4xl">
                        Cara kerja TechMind
                    </h2>
                    <p className="mt-4 text-lg leading-8 text-zinc-600">
                        Proses dirancang sederhana agar pengguna dapat memahami
                        hasil rekomendasi tanpa perlu latar belakang teknis yang
                        mendalam.
                    </p>
                </div>

                <div className="mt-10 grid gap-4 lg:grid-cols-3">
                    {steps.map((step) => (
                        <article
                            key={step.number}
                            className="rounded-xl border border-zinc-200 bg-white p-6"
                        >
                            <div className="flex items-center justify-between">
                                <span className="text-sm font-semibold text-brand">
                                    {step.number}
                                </span>
                                <step.icon
                                    className="h-5 w-5 text-zinc-500"
                                    aria-hidden="true"
                                />
                            </div>
                            <h3 className="mt-4 text-base font-semibold text-zinc-950">
                                {step.title}
                            </h3>
                            <p className="mt-2 text-sm leading-6 text-zinc-600">
                                {step.description}
                            </p>
                        </article>
                    ))}
                </div>

                <p className="mt-8 max-w-3xl text-sm leading-6 text-zinc-500">
                    Hasil yang ditampilkan merupakan indeks kecocokan berdasarkan
                    jawaban dan profil kriteria karier. Hasil ini membantu
                    eksplorasi, bukan menentukan masa depan atau menjamin
                    keberhasilan karier tertentu.
                </p>
            </div>
        </section>
    );
}
