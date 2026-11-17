import { Menu, X } from 'lucide-react';
import { Link } from '@inertiajs/react';
import { useState } from 'react';

const links = [
    { href: '#beranda', label: 'Beranda' },
    { href: '#tentang', label: 'Tentang' },
    { href: '#cara-kerja', label: 'Cara Kerja' },
    { href: '#faq', label: 'FAQ' },
];

export function LandingNav() {
    const [open, setOpen] = useState(false);

    return (
        <header className="sticky top-0 z-40 border-b border-zinc-200/80 bg-white/80 backdrop-blur">
            <div className="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6">
                <a
                    href="#beranda"
                    className="text-lg font-semibold tracking-tight text-zinc-950 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2"
                >
                    TechMind
                </a>

                <nav
                    className="hidden items-center gap-6 text-sm font-medium text-zinc-600 md:flex"
                    aria-label="Navigasi utama"
                >
                    {links.map((link) => (
                        <a
                            key={link.href}
                            href={link.href}
                            className="transition-colors hover:text-zinc-950 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2"
                        >
                            {link.label}
                        </a>
                    ))}
                </nav>

                <div className="hidden md:block">
                    <Link
                        href="/tes"
                        method="post"
                        as="button"
                        className="inline-flex h-9 items-center justify-center rounded-lg bg-brand px-4 text-sm font-medium text-white transition-opacity hover:opacity-90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2"
                    >
                        Mulai Tes Sekarang
                    </Link>
                </div>

                <button
                    type="button"
                    className="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-zinc-200 text-zinc-700 md:hidden"
                    aria-expanded={open}
                    aria-controls="mobile-menu"
                    aria-label={open ? 'Tutup menu' : 'Buka menu'}
                    onClick={() => setOpen((value) => !value)}
                >
                    {open ? <X className="h-4 w-4" /> : <Menu className="h-4 w-4" />}
                </button>
            </div>

            {open ? (
                <div
                    id="mobile-menu"
                    className="border-t border-zinc-200 bg-white md:hidden"
                >
                    <nav
                        className="mx-auto flex max-w-6xl flex-col gap-1 px-4 py-3"
                        aria-label="Navigasi mobile"
                    >
                        {links.map((link) => (
                            <a
                                key={link.href}
                                href={link.href}
                                className="rounded-md px-3 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-100 hover:text-zinc-950"
                                onClick={() => setOpen(false)}
                            >
                                {link.label}
                            </a>
                        ))}
                        <Link
                            href="/tes"
                            method="post"
                            as="button"
                            className="mt-2 inline-flex h-9 items-center justify-center rounded-lg bg-brand px-4 text-sm font-medium text-white"
                            onClick={() => setOpen(false)}
                        >
                            Mulai Tes Sekarang
                        </Link>
                    </nav>
                </div>
            ) : null}
        </header>
    );
}
