import { ClipboardList, Compass, Cpu } from 'lucide-react';
import { SectionHeading } from './section-heading';

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
            <div className="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:py-24">
                <SectionHeading
                    eyebrow="Alur kerja"
                    title="Cara kerja TechMind"
                    description="Proses dirancang sederhana agar pengguna dapat memahami hasil rekomendasi tanpa perlu latar belakang teknis yang mendalam."
                />

                <div className="relative mt-10 grid gap-4 lg:grid-cols-3">
                    <div className="pointer-events-none absolute inset-x-16 top-9 hidden h-px bg-gradient-to-r from-zinc-200 via-zinc-300 to-zinc-200 lg:block" />

                    {steps.map((step, index) => (
                        <article
                            key={step.number}
                            className="group relative overflow-hidden rounded-[22px] border border-zinc-200/80 bg-white p-6 shadow-[0_1px_2px_rgba(15,23,42,0.04),0_12px_22px_-20px_rgba(15,23,42,0.26)] transition-all duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-[0_1px_2px_rgba(79,70,229,0.08),0_18px_30px_-24px_rgba(79,70,229,0.35)]"
                        >
                            <div className="flex items-start justify-between gap-4">
                                <span className="text-3xl font-semibold tracking-[-0.06em] tabular-nums text-zinc-200 transition-colors duration-200 group-hover:text-indigo-200">
                                    {step.number}
                                </span>
                                <span className="flex size-10 items-center justify-center rounded-xl border border-zinc-200 bg-zinc-50 text-zinc-600 transition-colors duration-200 group-hover:border-indigo-200 group-hover:bg-indigo-50 group-hover:text-brand">
                                    <step.icon className="size-4" aria-hidden="true" />
                                </span>
                            </div>

                            <h3 className="mt-6 text-lg font-semibold tracking-[-0.03em] text-zinc-950">
                                {step.title}
                            </h3>
                            <p className="mt-2 text-sm leading-relaxed text-zinc-600">
                                {step.description}
                            </p>

                            {index < steps.length - 1 ? (
                                <span
                                    aria-hidden="true"
                                    className="absolute right-[-12px] top-12 hidden h-6 w-6 rounded-full border border-zinc-200 bg-white lg:block"
                                />
                            ) : null}
                        </article>
                    ))}
                </div>

                <p className="mt-8 max-w-3xl text-sm leading-relaxed text-zinc-500">
                    Hasil yang ditampilkan merupakan indeks kecocokan berdasarkan
                    jawaban dan profil kriteria karier. Hasil ini membantu
                    eksplorasi, bukan menentukan masa depan atau menjamin
                    keberhasilan karier tertentu.
                </p>
            </div>
        </section>
    );
}
