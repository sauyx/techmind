export function Trust() {
    return (
        <section id="metode" className="border-t border-zinc-200">
            <div className="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:py-20">
                <div className="max-w-2xl">
                    <h2 className="text-3xl font-semibold tracking-tight text-zinc-950 sm:text-4xl">
                        Bagaimana hasil rekomendasi disusun
                    </h2>
                    <p className="mt-4 text-lg leading-8 text-zinc-600">
                        TechMind menggunakan metode Simple Additive Weighting (SAW)
                        untuk menyusun urutan rekomendasi dari jawaban pengguna.
                    </p>
                </div>

                <div className="mt-10 grid gap-4 sm:grid-cols-3">
                    <article className="rounded-xl border border-zinc-200 p-6">
                        <h3 className="text-base font-semibold text-zinc-950">
                            Berdasarkan jawaban
                        </h3>
                        <p className="mt-2 text-sm leading-6 text-zinc-600">
                            Setiap rekomendasi berasal dari jawaban yang diberikan
                            pengguna pada kuesioner.
                        </p>
                    </article>
                    <article className="rounded-xl border border-zinc-200 p-6">
                        <h3 className="text-base font-semibold text-zinc-950">
                            Berdasarkan profil karier
                        </h3>
                        <p className="mt-2 text-sm leading-6 text-zinc-600">
                            Jawaban dibandingkan dengan profil kriteria pada
                            berbagai jalur karier IT.
                        </p>
                    </article>
                    <article className="rounded-xl border border-zinc-200 p-6">
                        <h3 className="text-base font-semibold text-zinc-950">
                            Untuk eksplorasi
                        </h3>
                        <p className="mt-2 text-sm leading-6 text-zinc-600">
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
