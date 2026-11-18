import { ArrowRight, BadgeCheck } from 'lucide-react';
import { Link } from '@inertiajs/react';
import { HeroVisual } from './hero-visual';

const signals = [
    '12 kriteria',
    '3 langkah singkat',
    'Tanpa login',
];

export function Hero() {
    return (
        <section id="beranda" className="relative scroll-mt-20 overflow-hidden">
            <div
                aria-hidden="true"
                className="pointer-events-none absolute inset-0 -z-20 bg-[radial-gradient(circle_at_top,_rgba(99,102,241,0.14),transparent_42%),radial-gradient(circle_at_bottom_right,_rgba(15,23,42,0.04),transparent_32%)]"
            />
            <div
                aria-hidden="true"
                className="pointer-events-none absolute inset-0 -z-10 opacity-60"
                style={{
                    backgroundImage:
                        'radial-gradient(circle, rgba(148,163,184,0.22) 1px, transparent 1px)',
                    backgroundSize: '24px 24px',
                    maskImage:
                        'radial-gradient(ellipse 70% 60% at 50% 30%, black, transparent 78%)',
                    WebkitMaskImage:
                        'radial-gradient(ellipse 70% 60% at 50% 30%, black, transparent 78%)',
                }}
            />

            <div className="mx-auto grid max-w-6xl gap-12 px-4 py-16 sm:px-6 lg:grid-cols-12 lg:items-center lg:gap-10 lg:py-24">
                <div className="lg:col-span-6">
                    <p className="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand">
                        Eksplorasi karier IT
                    </p>
                    <h1 className="mt-4 max-w-xl text-balance text-4xl font-semibold tracking-[-0.06em] text-zinc-950 sm:text-5xl lg:text-[4rem] lg:leading-[0.98]">
                        Kenali potensi Anda,{' '}
                        <span className="inline-block bg-gradient-to-r from-brand via-indigo-600 to-indigo-500 bg-clip-text text-transparent">
                            temukan jalur karier IT
                        </span>{' '}
                        yang tepat.
                    </h1>
                    <p className="mt-5 max-w-xl text-pretty text-lg leading-relaxed text-zinc-600">
                        TechMind membantu mahasiswa mengenali minat, kecenderungan
                        kemampuan, dan pilihan karier yang paling sesuai berdasarkan
                        profil yang mereka miliki.
                    </p>

                    <div className="mt-8 flex flex-col gap-3 sm:flex-row">
                        <Link
                            href="/tes"
                            className="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-gradient-to-b from-brand to-indigo-700 px-5 text-sm font-medium text-white shadow-[0_1px_2px_rgba(79,70,229,0.28),0_16px_36px_-22px_rgba(79,70,229,0.9)] transition-all duration-200 hover:-translate-y-0.5 hover:shadow-[0_1px_2px_rgba(79,70,229,0.36),0_18px_38px_-20px_rgba(79,70,229,0.95)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand/50 focus-visible:ring-offset-2"
                        >
                            Mulai Tes Sekarang
                            <ArrowRight className="size-4" aria-hidden="true" />
                        </Link>
                        <a
                            href="#tentang"
                            className="inline-flex h-11 items-center justify-center rounded-xl border border-zinc-200 bg-white/80 px-5 text-sm font-medium text-zinc-800 shadow-[0_1px_2px_rgba(15,23,42,0.04)] transition-all duration-200 hover:border-zinc-300 hover:text-zinc-950 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand/50 focus-visible:ring-offset-2"
                        >
                            Pelajari TechMind
                        </a>
                    </div>

                    <ul className="mt-7 flex flex-wrap items-center gap-x-5 gap-y-2.5">
                        {signals.map((signal) => (
                            <li
                                key={signal}
                                className="flex items-center gap-2 text-xs font-medium text-zinc-500"
                            >
                                <span className="flex size-5 items-center justify-center rounded-full bg-indigo-50 text-brand">
                                    <BadgeCheck className="size-3.5" aria-hidden="true" />
                                </span>
                                {signal}
                            </li>
                        ))}
                    </ul>

                    <p className="mt-5 max-w-lg text-xs leading-relaxed text-zinc-500">
                        Hasil rekomendasi merupakan indeks kecocokan, bukan
                        jaminan karier atau probabilitas keberhasilan.
                    </p>
                </div>

                <div className="lg:col-span-6">
                    <HeroVisual />
                </div>
            </div>
        </section>
    );
}
