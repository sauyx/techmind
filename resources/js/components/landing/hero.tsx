import { Link } from '@inertiajs/react';
import { HeroVisual } from './hero-visual';

const signals = ['12 kriteria', '3 langkah singkat', 'Tanpa login'];

export function Hero() {
    return (
        <section id="beranda" className="relative scroll-mt-20 overflow-hidden">
            <div
                aria-hidden="true"
                className="pointer-events-none absolute inset-0 -z-10 opacity-[0.55]"
                style={{
                    backgroundImage:
                        'radial-gradient(circle, rgb(212 212 216 / 0.55) 1px, transparent 1px)',
                    backgroundSize: '24px 24px',
                    maskImage:
                        'radial-gradient(ellipse 70% 60% at 50% 35%, black, transparent 80%)',
                    WebkitMaskImage:
                        'radial-gradient(ellipse 70% 60% at 50% 35%, black, transparent 80%)',
                }}
            />
            <div className="mx-auto grid max-w-6xl gap-12 px-4 py-16 sm:px-6 lg:grid-cols-12 lg:items-center lg:gap-8 lg:py-24">
                <div className="lg:col-span-7">
                    <p className="text-[11px] font-semibold tracking-[0.08em] text-brand uppercase">
                        Eksplorasi karier IT
                    </p>
                    <h1 className="mt-4 max-w-2xl text-4xl font-semibold tracking-[-0.025em] text-zinc-950 sm:text-5xl lg:text-[3.5rem] lg:leading-[1.05]">
                        Kenali potensimu, temukan jalur karier IT yang sesuai.
                    </h1>
                    <p className="mt-5 max-w-xl text-lg leading-relaxed text-zinc-600">
                        TechMind membantu mahasiswa mengenali minat dan
                        kecenderungan kemampuan, lalu mengeksplorasi pilihan
                        karier di bidang teknologi informasi secara lebih
                        terarah.
                    </p>
                    <div className="mt-8 flex flex-col gap-3 sm:flex-row">
                        <Link
                            href="/tes"
                            className="inline-flex h-11 items-center justify-center rounded-lg bg-brand px-5 text-sm font-medium text-white shadow-[0_1px_2px_rgba(79,70,229,0.35),0_8px_24px_-12px_rgba(79,70,229,0.6)] transition-all duration-200 hover:bg-indigo-700 focus-visible:ring-2 focus-visible:ring-indigo-500/40 focus-visible:ring-offset-2 focus-visible:outline-none"
                        >
                            Mulai Tes Sekarang
                        </Link>
                        <a
                            href="#tentang"
                            className="inline-flex h-11 items-center justify-center rounded-lg border border-zinc-300/80 bg-white px-5 text-sm font-medium text-zinc-800 shadow-[0_1px_2px_rgba(0,0,0,0.03)] transition-all duration-200 hover:border-zinc-400 hover:text-zinc-950 focus-visible:ring-2 focus-visible:ring-indigo-500/40 focus-visible:ring-offset-2 focus-visible:outline-none"
                        >
                            Pelajari TechMind
                        </a>
                    </div>
                    <ul className="mt-7 flex flex-wrap items-center gap-x-5 gap-y-2">
                        {signals.map((signal) => (
                            <li
                                key={signal}
                                className="flex items-center gap-2 text-xs font-medium text-zinc-500"
                            >
                                <span className="size-1.5 rounded-full bg-brand/70" />
                                {signal}
                            </li>
                        ))}
                    </ul>
                    <p className="mt-4 max-w-lg text-xs leading-relaxed text-zinc-500">
                        Hasil rekomendasi merupakan indeks kecocokan, bukan
                        jaminan karier atau probabilitas keberhasilan.
                    </p>
                </div>
                <div className="lg:col-span-5">
                    <HeroVisual />
                </div>
            </div>
        </section>
    );
}
