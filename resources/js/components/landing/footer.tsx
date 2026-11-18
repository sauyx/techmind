import { ArrowRight } from 'lucide-react';
import { Link } from '@inertiajs/react';
import { TechMindMark } from '@/components/techmind-mark';

const links = [
    { href: '#beranda', label: 'Beranda' },
    { href: '#tentang', label: 'Tentang' },
    { href: '#cara-kerja', label: 'Cara Kerja' },
    { href: '#faq', label: 'FAQ' },
];

export function Footer() {
    return (
        <footer className="bg-zinc-950 text-zinc-200">
            <div className="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:py-16">
                <div className="rounded-[28px] border border-white/10 bg-white/3 p-6 shadow-[0_1px_2px_rgba(15,23,42,0.18)] sm:p-8">
                    <div className="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                        <div className="max-w-xl">
                            <p className="text-[11px] font-semibold uppercase tracking-[0.12em] text-indigo-200">
                                Siap mulai?
                            </p>
                            <h3 className="mt-3 max-w-md text-balance text-2xl font-semibold tracking-[-0.05em] text-white sm:text-3xl">
                                Temukan jalur karier IT yang paling sesuai dengan Anda.
                            </h3>
                        </div>

                        <Link
                            href="/tes"
                            className="inline-flex items-center justify-center gap-2 self-start rounded-xl bg-white px-4 py-3 text-sm font-medium text-zinc-950 transition-all duration-200 hover:-translate-y-0.5 hover:bg-zinc-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand/50 focus-visible:ring-offset-2 focus-visible:ring-offset-zinc-950"
                        >
                            Mulai Tes Sekarang
                            <ArrowRight className="size-4" aria-hidden="true" />
                        </Link>
                    </div>
                </div>

                <div className="mt-12 flex flex-col gap-8 sm:flex-row sm:items-start sm:justify-between">
                    <div className="max-w-sm">
                        <div className="flex items-center gap-2.5">
                            <TechMindMark className="size-6 text-brand" />
                            <p className="text-lg font-semibold tracking-[-0.04em] text-white">
                                TechMind
                            </p>
                        </div>
                        <p className="mt-3 text-sm leading-relaxed text-zinc-400">
                            Aplikasi yang membantu mahasiswa mengenali potensi dan
                            mengeksplorasi pilihan karier di bidang teknologi informasi.
                        </p>
                    </div>

                    <nav
                        className="flex flex-wrap gap-x-6 gap-y-2 text-sm text-zinc-400"
                        aria-label="Navigasi footer"
                    >
                        {links.map((link) => (
                            <a
                                key={link.href}
                                href={link.href}
                                className="rounded-md transition-colors hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand/50 focus-visible:ring-offset-2 focus-visible:ring-offset-zinc-950"
                            >
                                {link.label}
                            </a>
                        ))}
                    </nav>
                </div>

                <div className="mt-8 flex flex-col gap-2 border-t border-white/10 pt-6 text-sm text-zinc-500 sm:flex-row sm:items-center sm:justify-between">
                    <p>
                        © 2026 TechMind. Rekomendasi merupakan alat bantu
                        eksplorasi, bukan jaminan karier.
                    </p>
                    <p className="text-[11px] font-medium uppercase tracking-[0.12em] text-zinc-400">
                        Light mode · Indigo #4F46E5
                    </p>
                </div>
            </div>
        </footer>
    );
}
