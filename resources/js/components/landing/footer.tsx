const links = [
    { href: '#beranda', label: 'Beranda' },
    { href: '#tentang', label: 'Tentang' },
    { href: '#cara-kerja', label: 'Cara Kerja' },
    { href: '#faq', label: 'FAQ' },
];

export function Footer() {
    return (
        <footer className="border-t border-zinc-200">
            <div className="mx-auto max-w-6xl px-4 py-12 sm:px-6">
                <div className="flex flex-col gap-8 sm:flex-row sm:items-start sm:justify-between">
                    <div className="max-w-sm">
                        <p className="text-lg font-semibold tracking-tight text-zinc-950">
                            TechMind
                        </p>
                        <p className="mt-3 text-sm leading-6 text-zinc-600">
                            Aplikasi yang membantu mahasiswa mengenali potensi dan
                            mengeksplorasi pilihan karier di bidang teknologi
                            informasi.
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
                                className="transition-colors hover:text-zinc-950"
                            >
                                {link.label}
                            </a>
                        ))}
                    </nav>
                </div>

                <div className="mt-8 border-t border-zinc-200 pt-6 text-sm text-zinc-500">
                    <p>
                        © 2026 TechMind. Rekomendasi merupakan alat bantu
                        eksplorasi, bukan jaminan karier.
                    </p>
                </div>
            </div>
        </footer>
    );
}
