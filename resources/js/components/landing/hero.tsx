import { Link } from '@inertiajs/react';
import { HeroVisual } from './hero-visual';

export function Hero() {
    return (
        <section id="beranda" className="scroll-mt-20">
            <div className="mx-auto grid max-w-6xl gap-10 px-4 py-16 sm:px-6 lg:grid-cols-2 lg:items-center lg:py-24">
                <div>
                    <p className="text-sm font-medium uppercase tracking-wide text-brand">
                        Eksplorasi karier IT
                    </p>
                    <h1 className="mt-4 text-4xl font-semibold tracking-tight text-zinc-950 sm:text-5xl lg:text-6xl">
                        Kenali potensimu, temukan jalur karier IT yang sesuai.
                    </h1>
                    <p className="mt-5 max-w-xl text-lg leading-8 text-zinc-600">
                        TechMind membantu mahasiswa mengenali minat dan kecenderungan
                        kemampuan, lalu mengeksplorasi pilihan karier di bidang
                        teknologi informasi secara lebih terarah.
                    </p>
                    <div className="mt-8 flex flex-col gap-3 sm:flex-row">
                        <Link
                            href="/tes"
                            method="post"
                            as="button"
                            className="inline-flex h-11 items-center justify-center rounded-lg bg-brand px-5 text-sm font-medium text-white transition-opacity hover:opacity-90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2"
                        >
                            Mulai Tes Sekarang
                        </Link>
                        <a
                            href="#tentang"
                            className="inline-flex h-11 items-center justify-center rounded-lg border border-zinc-300 px-5 text-sm font-medium text-zinc-800 transition-colors hover:border-zinc-400 hover:text-zinc-950 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2"
                        >
                            Pelajari TechMind
                        </a>
                    </div>
                    <p className="mt-4 text-sm text-zinc-500">
                        Hasil rekomendasi merupakan indeks kecocokan, bukan jaminan
                        karier atau probabilitas keberhasilan.
                    </p>
                </div>
                <HeroVisual />
            </div>
        </section>
    );
}
