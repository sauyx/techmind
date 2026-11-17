import { Link, router, usePage } from '@inertiajs/react';
import {
    Briefcase,
    ExternalLink,
    LayoutDashboard,
    LogOut,
    Menu,
    QrCode,
    SlidersHorizontal,
    X,
} from 'lucide-react';
import { useState, type ReactNode } from 'react';
import { cn } from '@/lib/utils';

const navigation = [
    { href: '/admin', label: 'Dashboard', icon: LayoutDashboard, exact: true },
    { href: '/admin/criteria', label: 'Kriteria & Bobot', icon: SlidersHorizontal, exact: false },
    { href: '/admin/careers', label: 'Karier & Matriks', icon: Briefcase, exact: false },
    { href: '/admin/booth', label: 'QR & Booth', icon: QrCode, exact: false },
];

export default function AdminLayout({ children }: { children: ReactNode }) {
    const { url, props } = usePage();
    const [sidebarOpen, setSidebarOpen] = useState(false);

    const auth = (props.auth ?? {}) as { user?: { name?: string } | null };
    const userName = auth.user?.name ?? 'Admin TechMind';
    const currentLabel =
        navigation.find((item) =>
            item.exact ? url === item.href : url.startsWith(item.href),
        )?.label ?? 'Dashboard';

    function isActive(href: string, exact: boolean) {
        return exact ? url === href : url.startsWith(href);
    }

    return (
        <div className="min-h-screen bg-zinc-50 text-zinc-900">
            {sidebarOpen ? (
                <button
                    type="button"
                    aria-label="Tutup menu"
                    onClick={() => setSidebarOpen(false)}
                    className="fixed inset-0 z-40 bg-zinc-900/40 lg:hidden"
                />
            ) : null}

            <aside
                className={cn(
                    'fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-zinc-200 bg-white transition-transform duration-200 ease-out lg:translate-x-0',
                    sidebarOpen ? 'translate-x-0' : '-translate-x-full',
                )}
            >
                <div className="flex h-16 items-center justify-between border-b border-zinc-200 px-4">
                    <Link
                        href="/admin"
                        onClick={() => setSidebarOpen(false)}
                        className="flex min-w-0 items-center gap-2"
                    >
                        <span className="truncate text-lg font-semibold tracking-tight text-zinc-900">
                            TechMind
                        </span>
                        <span className="rounded-md bg-brand/10 px-2 py-0.5 text-[11px] font-semibold text-brand">
                            Admin
                        </span>
                    </Link>
                    <button
                        type="button"
                        aria-label="Tutup menu"
                        onClick={() => setSidebarOpen(false)}
                        className="rounded-md p-1.5 text-zinc-500 transition-colors hover:bg-zinc-100 hover:text-zinc-900 lg:hidden"
                    >
                        <X className="size-5" />
                    </button>
                </div>

                <nav className="flex-1 space-y-1 p-3">
                    {navigation.map((item) => {
                        const Icon = item.icon;

                        return (
                            <Link
                                key={item.href}
                                href={item.href}
                                onClick={() => setSidebarOpen(false)}
                                aria-current={isActive(item.href, item.exact) ? 'page' : undefined}
                                className={cn(
                                    'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors',
                                    isActive(item.href, item.exact)
                                        ? 'bg-brand/10 text-brand'
                                        : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900',
                                )}
                            >
                                <Icon className="size-4 shrink-0" />
                                <span className="truncate">{item.label}</span>
                            </Link>
                        );
                    })}
                </nav>

                <div className="space-y-3 border-t border-zinc-200 p-3">
                    <Link
                        href="/"
                        className="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-zinc-600 transition-colors hover:bg-zinc-100 hover:text-zinc-900"
                    >
                        <ExternalLink className="size-4 shrink-0" />
                        <span className="truncate">Lihat Halaman Publik</span>
                    </Link>

                    <div className="rounded-lg border border-zinc-200 bg-zinc-50 p-3">
                        <p className="text-[11px] font-medium uppercase tracking-wide text-zinc-500">
                            Akun Admin
                        </p>
                        <p className="mt-1 truncate text-sm font-semibold text-zinc-900">{userName}</p>
                        <button
                            type="button"
                            onClick={() => router.post('/logout')}
                            className="mt-2 flex w-full items-center justify-center gap-2 rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-100"
                        >
                            <LogOut className="size-4" />
                            Keluar
                        </button>
                    </div>
                </div>
            </aside>

            <div className="min-h-screen lg:pl-64">
                <header className="sticky top-0 z-30 flex h-16 items-center gap-3 border-b border-zinc-200 bg-white px-4 sm:px-6">
                    <button
                        type="button"
                        aria-label="Buka menu"
                        onClick={() => setSidebarOpen(true)}
                        className="rounded-md p-2 text-zinc-600 transition-colors hover:bg-zinc-100 hover:text-zinc-900 lg:hidden"
                    >
                        <Menu className="size-5" />
                    </button>
                    <div className="min-w-0">
                        <p className="text-[11px] font-medium uppercase tracking-wide text-zinc-500">
                            Admin Panel
                        </p>
                        <p className="truncate text-sm font-semibold text-zinc-900">{currentLabel}</p>
                    </div>
                </header>

                <main className="mx-auto w-full max-w-6xl px-4 py-6 sm:px-6 sm:py-8">
                    {children}
                </main>
            </div>
        </div>
    );
}
