import { Link, router, usePage } from '@inertiajs/react';
import {
    Briefcase,
    ExternalLink,
    LayoutDashboard,
    LogOut,
    Menu,
    QrCode,
    Activity,
    Settings,
    SlidersHorizontal,
    X,
} from 'lucide-react';
import { useState, type ReactNode } from 'react';
import { TechMindMark } from '@/components/techmind-mark';
import { cn } from '@/lib/utils';

const navigation = [
    { href: '/admin', label: 'Dashboard', icon: LayoutDashboard, exact: true },
    {
        href: '/admin/criteria',
        label: 'Kriteria & Bobot',
        icon: SlidersHorizontal,
        exact: false,
    },
    {
        href: '/admin/careers',
        label: 'Karier & Matriks',
        icon: Briefcase,
        exact: false,
    },
    { href: '/admin/booth', label: 'QR & Booth', icon: QrCode, exact: false },
    { href: '/admin/kesehatan-matriks', label: 'Kesehatan Matriks', icon: Activity, exact: false },
    { href: '/settings/profile', label: 'Pengaturan Akun', icon: Settings, exact: false },
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
        <div className="min-h-screen bg-zinc-50/80 text-zinc-900">
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
                    'fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-zinc-200/80 bg-white transition-transform duration-200 ease-out lg:translate-x-0 print:hidden',
                    sidebarOpen ? 'translate-x-0' : '-translate-x-full',
                )}
            >
                <div className="flex h-16 items-center justify-between border-b border-zinc-200/80 px-4">
                    <Link
                        href="/admin"
                        onClick={() => setSidebarOpen(false)}
                        className="flex min-w-0 items-center gap-2.5 rounded-lg focus-visible:ring-2 focus-visible:ring-indigo-500/40 focus-visible:ring-offset-2 focus-visible:outline-none"
                    >
                        <TechMindMark className="size-6 text-brand" />
                        <span className="truncate text-base font-semibold tracking-[-0.025em] text-zinc-950">
                            TechMind
                        </span>
                        <span className="rounded-full bg-indigo-50 px-2 py-0.5 text-[10px] font-semibold tracking-[0.08em] text-indigo-700 uppercase">
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

                <nav
                    className="flex-1 space-y-1 p-3"
                    aria-label="Navigasi admin"
                >
                    {navigation.map((item) => {
                        const Icon = item.icon;
                        const active = isActive(item.href, item.exact);

                        return (
                            <Link
                                key={item.href}
                                href={item.href}
                                onClick={() => setSidebarOpen(false)}
                                aria-current={active ? 'page' : undefined}
                                className={cn(
                                    'relative flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition-colors duration-150 focus-visible:ring-2 focus-visible:ring-indigo-500/40 focus-visible:outline-none',
                                    active
                                        ? 'bg-indigo-50/80 font-medium text-indigo-700'
                                        : 'font-medium text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900',
                                )}
                            >
                                {active ? (
                                    <span
                                        aria-hidden="true"
                                        className="absolute top-1/2 left-0 h-5 w-0.5 -translate-y-1/2 rounded-full bg-indigo-600"
                                    />
                                ) : null}
                                <Icon
                                    className={cn(
                                        'size-4 shrink-0',
                                        active
                                            ? 'text-indigo-600'
                                            : 'text-zinc-400',
                                    )}
                                />
                                <span className="truncate">{item.label}</span>
                            </Link>
                        );
                    })}
                </nav>

                <div className="space-y-2 border-t border-zinc-200/80 p-3">
                    <Link
                        href="/"
                        className="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-zinc-600 transition-colors hover:bg-zinc-100 hover:text-zinc-900 focus-visible:ring-2 focus-visible:ring-indigo-500/40 focus-visible:outline-none"
                    >
                        <ExternalLink className="size-4 shrink-0 text-zinc-400" />
                        <span className="truncate">Lihat Halaman Publik</span>
                    </Link>

                    <div className="rounded-lg border border-zinc-200/80 bg-zinc-50/80 p-3">
                        <p className="text-[11px] font-semibold tracking-[0.08em] text-zinc-500 uppercase">
                            Akun Admin
                        </p>
                        <p className="mt-1 truncate text-sm font-semibold text-zinc-900">
                            {userName}
                        </p>
                        <button
                            type="button"
                            onClick={() => router.post('/logout')}
                            className="mt-2 flex w-full items-center justify-center gap-2 rounded-md border border-zinc-300/80 bg-white px-3 py-2 text-sm font-medium text-zinc-700 shadow-[0_1px_2px_rgba(0,0,0,0.03)] transition-colors hover:bg-zinc-50 focus-visible:ring-2 focus-visible:ring-indigo-500/40 focus-visible:outline-none"
                        >
                            <LogOut className="size-4" />
                            Keluar
                        </button>
                    </div>
                </div>
            </aside>

            <div className="min-h-screen lg:pl-64 print:pl-0">
                <header className="sticky top-0 z-30 flex h-16 items-center gap-3 border-b border-zinc-200/80 bg-white/80 px-4 backdrop-blur-md supports-[backdrop-filter]:bg-white/70 sm:px-6 print:hidden">
                    <button
                        type="button"
                        aria-label="Buka menu"
                        onClick={() => setSidebarOpen(true)}
                        className="rounded-md p-2 text-zinc-600 transition-colors hover:bg-zinc-100 hover:text-zinc-900 lg:hidden"
                    >
                        <Menu className="size-5" />
                    </button>
                    <div className="min-w-0">
                        <p className="text-[11px] font-semibold tracking-[0.08em] text-zinc-500 uppercase">
                            Admin Panel
                        </p>
                        <p className="truncate text-sm font-semibold tracking-[-0.015em] text-zinc-900">
                            {currentLabel}
                        </p>
                    </div>
                </header>

                <main className="mx-auto w-full max-w-6xl px-4 py-6 sm:px-6 sm:py-8">
                    {children}
                </main>
            </div>
        </div>
    );
}
