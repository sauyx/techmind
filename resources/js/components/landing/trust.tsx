export function Trust() {
    return (
        <section id="metode" className="border-t border-zinc-200/80 bg-white">
            <div className="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:py-20">
                <div className="max-w-2xl">
                    <p className="text-[11px] font-semibold tracking-[0.08em] text-brand uppercase">
                        Metode
                    </p>
                    <h2 className="mt-3 text-3xl font-semibold tracking-[-0.025em] text-zinc-950 sm:text-4xl">
                        Bagaimana hasil rekomendasi disusun
                    </h2>
                    <p className="mt-4 text-lg leading-relaxed text-zinc-600">
                        TechMind menggunakan metode Simple Additive Weighting
                        (SAW) untuk menyusun urutan rekomendasi dari jawaban
                        pengguna.
                    </p>
                </div>

                <div className="mt-10 grid gap-3 sm:grid-cols-3">
                    <article className="rounded-xl border border-zinc-200/80 bg-white p-6 shadow-[0_1px_2px_rgba(0,0,0,0.03),0_1px_3px_rgba(0,0,0,0.02)] transition-all duration-200 hover:border-zinc-300">
                        <p className="text-[11px] font-semibold tracking-[0.08em] text-zinc-400 uppercase">
                            Input
                        </p>
                        <h3 className="mt-3 text-base font-semibold tracking-[-0.015em] text-zinc-950">
                            Berdasarkan jawaban
                        </h3>
                        <p className="mt-2 text-sm leading-relaxed text-zinc-600">
                            Setiap rekomendasi berasal dari jawaban yang
                            diberikan pengguna pada kuesioner.
                        </p>
                    </article>
                    <article className="rounded-xl border border-zinc-200/80 bg-white p-6 shadow-[0_1px_2px_rgba(0,0,0,0.03),0_1px_3px_rgba(0,0,0,0.02)] transition-all duration-200 hover:border-zinc-300">
                        <p className="text-[11px] font-semibold tracking-[0.08em] text-zinc-400 uppercase">
                            Pembanding
                        </p>
                        <h3 className="mt-3 text-base font-semibold tracking-[-0.015em] text-zinc-950">
                            Berdasarkan profil karier
                        </h3>
                        <p className="mt-2 text-sm leading-relaxed text-zinc-600">
                            Jawaban dibandingkan dengan profil kriteria pada
                            berbagai jalur karier IT.
                        </p>
                    </article>
                    <article className="rounded-xl border border-indigo-600/15 bg-gradient-to-b from-indigo-50/60 to-white p-6 shadow-[0_1px_2px_rgba(0,0,0,0.03),0_1px_3px_rgba(0,0,0,0.02)] transition-all duration-200 hover:border-indigo-600/25">
                        <p className="text-[11px] font-semibold tracking-[0.08em] text-indigo-600 uppercase">
                            Tujuan
                        </p>
                        <h3 className="mt-3 text-base font-semibold tracking-[-0.015em] text-zinc-950">
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
