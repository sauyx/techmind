import { Link, usePage } from '@inertiajs/react';
import { type ReactNode } from 'react';
import { cn } from '@/lib/utils';

const navigation = [
    { href: '/admin', label: 'Dashboard', exact: true },
    { href: '/admin/criteria', label: 'Kriteria & Bobot', exact: false },
    { href: '/admin/careers', label: 'Karier & Matriks', exact: false },
];

export default function AdminLayout({ children }: { children: ReactNode }) {
    const { url } = usePage();

    function isActive(href: string, exact: boolean) {
        if (exact) {
            return url === href;
        }

        return url.startsWith(href);
    }

    return (
        <div className="min-h-screen bg-background text-foreground">
            <header className="border-b border-zinc-200 bg-white">
                <div className="mx-auto max-w-6xl px-4">
                    <div className="flex min-h-14 flex-col gap-3 py-3 sm:flex-row sm:items-center sm:justify-between sm:py-0">
                        <Link href="/admin" className="text-lg font-semibold tracking-tight text-zinc-950">
                            TechMind Admin
                        </Link>
                        <nav className="flex flex-wrap items-center gap-1">
                            {navigation.map((item) => (
                                <Link
                                    key={item.href}
                                    href={item.href}
                                    className={cn(
                                        'rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                                        isActive(item.href, item.exact)
                                            ? 'bg-brand/10 text-brand'
                                            : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950',
                                    )}
                                >
                                    {item.label}
                                </Link>
                            ))}
                            <Link
                                href="/"
                                className="rounded-lg px-3 py-2 text-sm font-medium text-zinc-600 transition-colors hover:bg-zinc-100 hover:text-zinc-950"
                            >
                                Halaman Publik
                            </Link>
                        </nav>
                    </div>
                </div>
            </header>
            <main className="mx-auto max-w-6xl px-4 py-8">{children}</main>
        </div>
    );
}
