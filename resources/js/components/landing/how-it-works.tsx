import { ClipboardList, Compass, Cpu } from 'lucide-react';

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
        <section
            id="cara-kerja"
            className="scroll-mt-20 border-t border-zinc-200/80 bg-zinc-50/80"
        >
            <div className="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:py-20">
                <div className="max-w-2xl">
                    <p className="text-[11px] font-semibold tracking-[0.08em] text-brand uppercase">
                        Alur kerja
                    </p>
                    <h2 className="mt-3 text-3xl font-semibold tracking-[-0.025em] text-zinc-950 sm:text-4xl">
                        Cara kerja TechMind
                    </h2>
                    <p className="mt-4 text-lg leading-relaxed text-zinc-600">
                        Proses dirancang sederhana agar pengguna dapat memahami
                        hasil rekomendasi tanpa perlu latar belakang teknis yang
                        mendalam.
                    </p>
                </div>

                <div className="mt-10 grid gap-3 lg:grid-cols-3">
                    {steps.map((step, index) => (
                        <article
                            key={step.number}
                            className="group relative overflow-hidden rounded-xl border border-zinc-200/80 bg-white p-6 shadow-[0_1px_2px_rgba(0,0,0,0.03),0_1px_3px_rgba(0,0,0,0.02)] transition-all duration-200 hover:border-zinc-300"
                        >
                            <div className="flex items-start justify-between">
                                <span className="text-3xl font-semibold tracking-[-0.03em] text-zinc-200 tabular-nums transition-colors duration-200 group-hover:text-indigo-200">
                                    {step.number}
                                </span>
                                <span className="flex size-9 items-center justify-center rounded-lg border border-zinc-200/80 bg-zinc-50 text-zinc-500 transition-colors duration-200 group-hover:border-indigo-200 group-hover:text-brand">
                                    <step.icon
                                        className="size-4"
                                        aria-hidden="true"
                                    />
                                </span>
                            </div>
                            <h3 className="mt-5 text-base font-semibold tracking-[-0.015em] text-zinc-950">
                                {step.title}
                            </h3>
                            <p className="mt-2 text-sm leading-relaxed text-zinc-600">
                                {step.description}
                            </p>
                            {index < steps.length - 1 ? (
                                <span
                                    aria-hidden="true"
                                    className="absolute inset-x-6 bottom-0 h-px bg-gradient-to-r from-zinc-200/80 to-transparent"
                                />
                            ) : null}
                        </article>
                    ))}
                </div>

                <p className="mt-8 max-w-3xl text-sm leading-relaxed text-zinc-500">
                    Hasil yang ditampilkan merupakan indeks kecocokan
                    berdasarkan jawaban dan profil kriteria karier. Hasil ini
                    membantu eksplorasi, bukan menentukan masa depan atau
                    menjamin keberhasilan karier tertentu.
                </p>
            </div>
        </section>
    );
}
