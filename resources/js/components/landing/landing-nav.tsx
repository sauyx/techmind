import { Link } from '@inertiajs/react';
import { Menu, X } from 'lucide-react';
import { useEffect, useState } from 'react';
import { TechMindMark } from '@/components/techmind-mark';

const links = [
    { href: '#beranda', label: 'Beranda' },
    { href: '#tentang', label: 'Tentang' },
    { href: '#cara-kerja', label: 'Cara Kerja' },
    { href: '#faq', label: 'FAQ' },
];

export function LandingNav() {
    const [open, setOpen] = useState(false);
    const [activeSection, setActiveSection] = useState('beranda');

    useEffect(() => {
        const sections = links
            .map((link) => document.getElementById(link.href.replace('#', '')))
            .filter((section): section is HTMLElement => section !== null);

        if (!sections.length) {
            return undefined;
        }

        const observer = new IntersectionObserver(
            (entries) => {
                const visible = entries
                    .filter((entry) => entry.isIntersecting)
                    .sort((a, b) => b.intersectionRatio - a.intersectionRatio);

                if (visible[0]) {
                    setActiveSection(visible[0].target.id);
                }
            },
            {
                threshold: [0.25, 0.45, 0.7],
                rootMargin: '-12% 0px -52% 0px',
            },
        );

        sections.forEach((section) => observer.observe(section));

        return () => observer.disconnect();
    }, []);

    useEffect(() => {
        if (!open) {
            return undefined;
        }

        const handleKeyDown = (event: KeyboardEvent) => {
            if (event.key === 'Escape') {
                setOpen(false);
            }
        };

        window.addEventListener('keydown', handleKeyDown);

        return () => window.removeEventListener('keydown', handleKeyDown);
    }, [open]);

    const isActive = (href: string) => activeSection === href.replace('#', '');

    return (
        <header className="sticky top-0 z-50 border-b border-zinc-200/80 bg-white/75 backdrop-blur-xl transition-all duration-300 supports-[backdrop-filter]:bg-white/70">
            <div className="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6">
                <Link
                    href="/"
                    className="group flex items-center gap-2.5 rounded-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand/50 focus-visible:ring-offset-2"
                >
                    <TechMindMark className="size-7 text-brand transition-transform duration-200 group-hover:-rotate-3" />
                    <span className="text-lg font-semibold tracking-[-0.04em] text-zinc-950">
                        TechMind
                    </span>
                    <span className="hidden rounded-full border border-zinc-200 bg-zinc-50 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.1em] text-zinc-500 sm:inline">
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
                            aria-current={isActive(link.href) ? 'page' : undefined}
                            className={`rounded-lg px-3 py-2 transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand/50 ${
                                isActive(link.href)
                                    ? 'bg-indigo-50 text-brand shadow-[inset_0_0_0_1px_rgba(79,70,229,0.12)]'
                                    : 'hover:bg-zinc-100 hover:text-zinc-950'
                            }`}
                        >
                            {link.label}
                        </a>
                    ))}
                </nav>

                <div className="hidden md:block">
                    <Link
                        href="/tes"
                        className="inline-flex h-10 items-center justify-center rounded-xl bg-gradient-to-b from-brand to-indigo-700 px-4 text-sm font-medium text-white shadow-[0_1px_2px_rgba(79,70,229,0.28),0_10px_24px_-16px_rgba(79,70,229,0.9)] transition-all duration-200 hover:-translate-y-0.5 hover:shadow-[0_1px_2px_rgba(79,70,229,0.32),0_14px_28px_-16px_rgba(79,70,229,0.9)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand/50 focus-visible:ring-offset-2"
                    >
                        Mulai Tes Sekarang
                    </Link>
                </div>

                <button
                    type="button"
                    className="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-zinc-200 bg-white text-zinc-700 transition-colors hover:border-zinc-300 hover:text-zinc-950 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand/50 md:hidden"
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
                    className="border-t border-zinc-200/80 bg-white/95 backdrop-blur-xl md:hidden"
                >
                    <nav
                        className="mx-auto flex max-w-6xl flex-col gap-1 px-4 py-3"
                        aria-label="Navigasi mobile"
                    >
                        {links.map((link) => (
                            <a
                                key={link.href}
                                href={link.href}
                                aria-current={isActive(link.href) ? 'page' : undefined}
                                className={`rounded-lg px-3 py-2.5 text-sm font-medium transition-colors ${
                                    isActive(link.href)
                                        ? 'bg-indigo-50 text-brand'
                                        : 'text-zinc-700 hover:bg-zinc-100 hover:text-zinc-950'
                                }`}
                                onClick={() => setOpen(false)}
                            >
                                {link.label}
                            </a>
                        ))}
                        <Link
                            href="/tes"
                            className="mt-2 inline-flex h-11 items-center justify-center rounded-xl bg-gradient-to-b from-brand to-indigo-700 px-4 text-sm font-medium text-white"
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
