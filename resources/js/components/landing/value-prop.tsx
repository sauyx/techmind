import { Compass, Lightbulb, Route } from 'lucide-react';

const items = [
    {
        icon: Compass,
        title: 'Mengenali minat',
        description:
            'Pengguna menjawab pertanyaan singkat tentang preferensi dan kecenderungan kemampuan.',
    },
    {
        icon: Lightbulb,
        title: 'Memahami karakteristik',
        description:
            'Jawaban digunakan untuk memetakan kecenderungan diri secara sederhana dan transparan.',
    },
    {
        icon: Route,
        title: 'Mengeksplorasi pilihan',
        description:
            'Hasil membantu pengguna melihat jalur karier IT yang layak dipertimbangkan.',
    },
];

export function ValueProp() {
    return (
        <section id="tentang" className="scroll-mt-20 border-t border-zinc-200 bg-zinc-50">
            <div className="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:py-20">
                <div className="max-w-2xl">
                    <h2 className="text-3xl font-semibold tracking-tight text-zinc-950 sm:text-4xl">
                        Apa itu TechMind?
                    </h2>
                    <p className="mt-4 text-lg leading-8 text-zinc-600">
                        TechMind adalah aplikasi yang membantu mahasiswa mengenali
                        potensi diri dan mengeksplorasi pilihan karier di bidang
                        teknologi informasi. Sistem ini menggunakan jawaban
                        pengguna dan profil kriteria karier untuk menyusun urutan
                        rekomendasi yang dapat dipahami.
                    </p>
                </div>

                <div className="mt-10 grid gap-3 sm:grid-cols-6">
                    {items.map((item, index) => (
                        <article
                            key={item.title}
                            className={`rounded-xl border border-zinc-200/80 bg-white p-5 sm:p-6 ${
                                index === 0
                                    ? 'sm:col-span-4'
                                    : index === 1
                                      ? 'sm:col-span-2'
                                      : 'sm:col-span-6'
                            }`}
                        >
                            <div className="flex items-start gap-4">
                                <item.icon className="mt-0.5 h-5 w-5 shrink-0 text-brand" aria-hidden="true" />
                                <div>
                                    <h3 className="text-base font-semibold tracking-tight text-zinc-950">
                                        {item.title}
                                    </h3>
                                    <p className="mt-2 text-sm leading-6 text-zinc-600">
                                        {item.description}
                                    </p>
                                </div>
                            </div>
                        </article>
                    ))}
                </div>
            </div>
        </section>
    );
}
