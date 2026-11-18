import { Compass, Lightbulb, Route } from 'lucide-react';
import { SectionHeading } from './section-heading';

const items = [
    {
        number: '01',
        icon: Compass,
        title: 'Mengenali minat',
        description:
            'Pengguna menjawab pertanyaan singkat tentang preferensi dan kecenderungan kemampuan.',
        accent: true,
    },
    {
        number: '02',
        icon: Lightbulb,
        title: 'Memahami karakteristik',
        description:
            'Jawaban digunakan untuk memetakan kecenderungan diri secara sederhana dan transparan.',
        accent: false,
    },
    {
        number: '03',
        icon: Route,
        title: 'Mengeksplorasi pilihan',
        description:
            'Hasil membantu pengguna melihat jalur karier IT yang layak dipertimbangkan.',
        accent: false,
    },
];

export function ValueProp() {
    return (
        <section
            id="tentang"
            className="scroll-mt-20 border-t border-zinc-200/80 bg-zinc-50/80"
        >
            <div className="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:py-24">
                <SectionHeading
                    eyebrow="Tentang TechMind"
                    title="Apa itu TechMind?"
                    description="TechMind membantu mahasiswa mengenali potensi diri dan mengeksplorasi pilihan karier di bidang teknologi informasi dengan pendekatan yang sederhana dan mudah dipahami."
                />

                <div className="mt-10 grid gap-4 md:grid-cols-12">
                    {items.map((item, index) => (
                        <article
                            key={item.title}
                            className={`group relative overflow-hidden rounded-[22px] border border-zinc-200/80 bg-white p-5 shadow-[0_1px_2px_rgba(15,23,42,0.04),0_12px_22px_-20px_rgba(15,23,42,0.3)] transition-all duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-[0_1px_2px_rgba(79,70,229,0.08),0_18px_26px_-22px_rgba(79,70,229,0.35)] sm:p-6 ${
                                index === 0
                                    ? 'md:col-span-7'
                                    : index === 1
                                      ? 'md:col-span-5'
                                      : 'md:col-span-12'
                            }`}
                        >
                            {item.accent ? (
                                <div
                                    aria-hidden="true"
                                    className="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-brand/60 to-transparent"
                                />
                            ) : null}
                            <div className="flex items-start justify-between gap-4">
                                <span className="text-[11px] font-semibold uppercase tracking-[0.12em] text-zinc-400">
                                    {item.number}
                                </span>
                                <span className="flex size-10 items-center justify-center rounded-xl border border-zinc-200 bg-zinc-50 text-brand transition-colors duration-200 group-hover:border-indigo-200 group-hover:bg-indigo-50">
                                    <item.icon className="size-4" aria-hidden="true" />
                                </span>
                            </div>
                            <h3 className="mt-5 text-lg font-semibold tracking-[-0.03em] text-zinc-950">
                                {item.title}
                            </h3>
                            <p className="mt-2 max-w-2xl text-sm leading-relaxed text-zinc-600">
                                {item.description}
                            </p>
                        </article>
                    ))}
                </div>
            </div>
        </section>
    );
}
