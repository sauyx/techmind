import { SectionHeading } from './section-heading';

export function Trust() {
    return (
        <section id="metode" className="border-t border-zinc-200/80 bg-white">
            <div className="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:py-24">
                <SectionHeading
                    eyebrow="Metode"
                    title="Bagaimana hasil rekomendasi disusun"
                    description="TechMind menggunakan metode Simple Additive Weighting (SAW) untuk menyusun urutan rekomendasi dari jawaban pengguna."
                />

                <div className="mt-10 grid gap-4 sm:grid-cols-3">
                    <article className="rounded-[22px] border border-zinc-200/80 bg-white p-6 shadow-[0_1px_2px_rgba(15,23,42,0.04),0_12px_26px_-24px_rgba(15,23,42,0.28)] transition-all duration-200 hover:-translate-y-0.5 hover:border-zinc-300">
                        <p className="text-[11px] font-semibold uppercase tracking-[0.12em] text-zinc-400">
                            Input
                        </p>
                        <h3 className="mt-3 text-lg font-semibold tracking-[-0.03em] text-zinc-950">
                            Berdasarkan jawaban
                        </h3>
                        <p className="mt-2 text-sm leading-relaxed text-zinc-600">
                            Setiap rekomendasi berasal dari jawaban yang diberikan
                            pengguna pada kuesioner.
                        </p>
                    </article>

                    <article className="rounded-[22px] border border-zinc-200/80 bg-white p-6 shadow-[0_1px_2px_rgba(15,23,42,0.04),0_12px_26px_-24px_rgba(15,23,42,0.28)] transition-all duration-200 hover:-translate-y-0.5 hover:border-zinc-300">
                        <p className="text-[11px] font-semibold uppercase tracking-[0.12em] text-zinc-400">
                            Pembanding
                        </p>
                        <h3 className="mt-3 text-lg font-semibold tracking-[-0.03em] text-zinc-950">
                            Berdasarkan profil karier
                        </h3>
                        <p className="mt-2 text-sm leading-relaxed text-zinc-600">
                            Jawaban dibandingkan dengan profil kriteria pada
                            berbagai jalur karier IT.
                        </p>
                    </article>

                    <article className="rounded-[22px] border border-indigo-200 bg-gradient-to-b from-indigo-50/70 to-white p-6 shadow-[0_1px_2px_rgba(79,70,229,0.08),0_14px_28px_-24px_rgba(79,70,229,0.45)] transition-all duration-200 hover:-translate-y-0.5 hover:border-indigo-300">
                        <p className="text-[11px] font-semibold uppercase tracking-[0.12em] text-brand">
                            Tujuan
                        </p>
                        <h3 className="mt-3 text-lg font-semibold tracking-[-0.03em] text-zinc-950">
                            Untuk eksplorasi
                        </h3>
                        <p className="mt-2 text-sm leading-relaxed text-zinc-600">
                            Hasil membantu pengguna mempertimbangkan pilihan,
                            bukan menggantikan keputusan pribadi atau konsultasi
                            karier.
                        </p>
                    </article>
                </div>
            </div>
        </section>
    );
}
