import { TechMindMark } from '@/components/techmind-mark';

const links = [
    { href: '#beranda', label: 'Beranda' },
    { href: '#tentang', label: 'Tentang' },
    { href: '#cara-kerja', label: 'Cara Kerja' },
    { href: '#faq', label: 'FAQ' },
];

export function Footer() {
    return (
        <footer className="border-t border-zinc-200/80 bg-white">
            <div className="mx-auto max-w-6xl px-4 py-12 sm:px-6">
                <div className="flex flex-col gap-8 sm:flex-row sm:items-start sm:justify-between">
                    <div className="max-w-sm">
                        <div className="flex items-center gap-2.5">
                            <TechMindMark className="size-6 text-brand" />
                            <p className="text-lg font-semibold tracking-[-0.025em] text-zinc-950">
                                TechMind
                            </p>
                        </div>
                        <p className="mt-3 text-sm leading-relaxed text-zinc-600">
                            Aplikasi yang membantu mahasiswa mengenali potensi
                            dan mengeksplorasi pilihan karier di bidang
                            teknologi informasi.
                        </p>
                    </div>

                    <nav
                        className="flex flex-wrap gap-x-6 gap-y-2 text-sm text-zinc-600"
                        aria-label="Navigasi footer"
                    >
                        {links.map((link) => (
                            <a
                                key={link.href}
                                href={link.href}
                                className="rounded transition-colors hover:text-zinc-950 focus-visible:ring-2 focus-visible:ring-indigo-500/40 focus-visible:ring-offset-2 focus-visible:outline-none"
                            >
                                {link.label}
                            </a>
                        ))}
                    </nav>
                </div>

                <div className="mt-8 flex flex-col gap-2 border-t border-zinc-200/80 pt-6 text-sm text-zinc-500 sm:flex-row sm:items-center sm:justify-between">
                    <p>
                        © 2026 TechMind. Rekomendasi merupakan alat bantu
                        eksplorasi, bukan jaminan karier.
                    </p>
                    <p className="text-[11px] font-medium tracking-[0.08em] text-zinc-400 uppercase">
                        Light mode · Indigo #4F46E5
                    </p>
                </div>
            </div>
        </footer>
    );
}
