import { Compass, Lightbulb, Route } from 'lucide-react';

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
            <div className="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:py-20">
                <div className="max-w-2xl">
                    <p className="text-[11px] font-semibold tracking-[0.08em] text-brand uppercase">
                        Tentang TechMind
                    </p>
                    <h2 className="mt-3 text-3xl font-semibold tracking-[-0.025em] text-zinc-950 sm:text-4xl">
                        Apa itu TechMind?
                    </h2>
                    <p className="mt-4 text-lg leading-relaxed text-zinc-600">
                        TechMind adalah aplikasi yang membantu mahasiswa
                        mengenali potensi diri dan mengeksplorasi pilihan karier
                        di bidang teknologi informasi. Sistem ini menggunakan
                        jawaban pengguna dan profil kriteria karier untuk
                        menyusun urutan rekomendasi yang dapat dipahami.
                    </p>
                </div>

                <div className="mt-10 grid gap-3 md:grid-cols-12">
                    {items.map((item, index) => (
                        <article
                            key={item.title}
                            className={`group relative overflow-hidden rounded-xl border border-zinc-200/80 bg-white p-5 shadow-[0_1px_2px_rgba(0,0,0,0.03),0_1px_3px_rgba(0,0,0,0.02)] transition-all duration-200 hover:border-zinc-300 sm:p-6 ${
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
                                    className="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-indigo-500/50 to-transparent"
                                />
                            ) : null}
                            <div className="flex items-start justify-between gap-4">
                                <span className="text-[11px] font-semibold tracking-[0.08em] text-zinc-400 uppercase">
                                    {item.number}
                                </span>
                                <span className="flex size-9 items-center justify-center rounded-lg border border-zinc-200/80 bg-zinc-50 text-brand transition-colors duration-200 group-hover:border-indigo-200 group-hover:bg-indigo-50/60">
                                    <item.icon
                                        className="size-4"
                                        aria-hidden="true"
                                    />
                                </span>
                            </div>
                            <h3 className="mt-5 text-base font-semibold tracking-[-0.015em] text-zinc-950">
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
