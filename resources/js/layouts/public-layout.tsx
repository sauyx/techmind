import { Link } from '@inertiajs/react';
import { type ReactNode } from 'react';
import { TechMindMark } from '@/components/techmind-mark';

export default function PublicLayout({ children }: { children: ReactNode }) {
    return (
        <div className="min-h-screen bg-zinc-50/80 text-zinc-950">
            <header className="sticky top-0 z-40 border-b border-zinc-200/70 bg-white/80 backdrop-blur-md supports-[backdrop-filter]:bg-white/70">
                <div className="mx-auto flex h-14 max-w-6xl items-center justify-between px-4 sm:px-6">
                    <Link
                        href="/"
                        className="flex items-center gap-2.5 rounded-lg focus-visible:ring-2 focus-visible:ring-indigo-500/40 focus-visible:ring-offset-2 focus-visible:outline-none"
                    >
                        <TechMindMark className="size-6 text-brand" />
                        <span className="text-base font-semibold tracking-[-0.025em] text-zinc-950">
                            TechMind
                        </span>
                    </Link>
                    <Link
                        href="/"
                        className="rounded-lg px-3 py-1.5 text-xs font-medium text-zinc-600 transition-colors hover:bg-zinc-100/80 hover:text-zinc-950 focus-visible:ring-2 focus-visible:ring-indigo-500/40 focus-visible:outline-none"
                    >
                        Beranda
                    </Link>
                </div>
            </header>
            <main className="mx-auto max-w-6xl px-4 py-8 sm:px-6">
                {children}
            </main>
        </div>
    );
}
