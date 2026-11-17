import { Link } from '@inertiajs/react';
import { Menu, X } from 'lucide-react';
import { useState } from 'react';
import { TechMindMark } from '@/components/techmind-mark';

const links = [
    { href: '#beranda', label: 'Beranda' },
    { href: '#tentang', label: 'Tentang' },
    { href: '#cara-kerja', label: 'Cara Kerja' },
    { href: '#faq', label: 'FAQ' },
];

export function LandingNav() {
    const [open, setOpen] = useState(false);

    return (
        <header className="sticky top-0 z-50 border-b border-zinc-200/70 bg-white/80 backdrop-blur-md supports-[backdrop-filter]:bg-white/70">
            <div className="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6">
                <Link
                    href="/"
                    className="group flex items-center gap-2.5 rounded-lg focus-visible:ring-2 focus-visible:ring-indigo-500/40 focus-visible:ring-offset-2 focus-visible:outline-none"
                >
                    <TechMindMark className="size-7 text-brand transition-transform duration-200 group-hover:-rotate-3" />
                    <span className="text-lg font-semibold tracking-[-0.025em] text-zinc-950">
                        TechMind
                    </span>
                    <span className="hidden rounded-full border border-zinc-200/80 bg-zinc-50 px-2 py-0.5 text-[10px] font-semibold tracking-[0.08em] text-zinc-500 uppercase sm:inline">
                        Karier IT
                    </span>
                </Link>

                <nav
                    className="hidden items-center gap-1 text-sm font-medium text-zinc-600 md:flex"
                    aria-label="Navigasi utama"
                >
                    {links.map((link) => (
                        <a
                            key={link.href}
                            href={link.href}
                            className="rounded-lg px-3 py-2 transition-colors hover:bg-zinc-100/80 hover:text-zinc-950 focus-visible:ring-2 focus-visible:ring-indigo-500/40 focus-visible:outline-none"
                        >
                            {link.label}
                        </a>
                    ))}
                </nav>

                <div className="hidden md:block">
                    <Link
                        href="/tes"
                        className="inline-flex h-9 items-center justify-center rounded-lg bg-zinc-950 px-4 text-sm font-medium text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)] transition-all duration-200 hover:bg-zinc-800 focus-visible:ring-2 focus-visible:ring-indigo-500/40 focus-visible:ring-offset-2 focus-visible:outline-none"
                    >
                        Mulai Tes Sekarang
                    </Link>
                </div>

                <button
                    type="button"
                    className="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-zinc-200 text-zinc-700 transition-colors hover:border-zinc-300 hover:text-zinc-950 focus-visible:ring-2 focus-visible:ring-indigo-500/40 focus-visible:outline-none md:hidden"
                    aria-expanded={open}
                    aria-controls="mobile-menu"
                    aria-label={open ? 'Tutup menu' : 'Buka menu'}
                    onClick={() => setOpen((value) => !value)}
                >
                    {open ? (
                        <X className="h-4 w-4" />
                    ) : (
                        <Menu className="h-4 w-4" />
                    )}
                </button>
            </div>

            {open ? (
                <div
                    id="mobile-menu"
                    className="border-t border-zinc-200/70 bg-white/95 backdrop-blur md:hidden"
                >
                    <nav
                        className="mx-auto flex max-w-6xl flex-col gap-1 px-4 py-3"
                        aria-label="Navigasi mobile"
                    >
                        {links.map((link) => (
                            <a
                                key={link.href}
                                href={link.href}
                                className="rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-100 hover:text-zinc-950 focus-visible:ring-2 focus-visible:ring-indigo-500/40 focus-visible:outline-none"
                                onClick={() => setOpen(false)}
                            >
                                {link.label}
                            </a>
                        ))}
                        <Link
                            href="/tes"
                            className="mt-2 inline-flex h-10 items-center justify-center rounded-lg bg-zinc-950 px-4 text-sm font-medium text-white"
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
