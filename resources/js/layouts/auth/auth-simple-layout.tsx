import { Link } from '@inertiajs/react';
import { home } from '@/routes';
import type { AuthLayoutProps } from '@/types';

export default function AuthSimpleLayout({
    children,
    title,
    description,
}: AuthLayoutProps) {
    return (
        <div className="flex min-h-svh flex-col items-center justify-center gap-6 bg-zinc-50 p-6 text-zinc-900 md:p-10">
            <div className="w-full max-w-sm">
                <div className="flex flex-col gap-8">
                    <div className="flex flex-col items-center gap-4">
                        <Link
                            href={home()}
                            className="flex items-center gap-2 text-lg font-semibold tracking-tight text-zinc-900"
                        >
                            <span>TechMind</span>
                            <span className="rounded-md bg-brand/10 px-2 py-0.5 text-[11px] font-semibold text-brand">
                                Admin
                            </span>
                        </Link>

                        <div className="space-y-2 text-center">
                            <h1 className="text-xl font-semibold text-zinc-900">{title}</h1>
                            <p className="text-center text-sm text-zinc-600">{description}</p>
                        </div>
                    </div>

                    <div className="rounded-xl border border-zinc-200 bg-white p-6">
                        {children}
                    </div>

                    <Link
                        href={home()}
                        className="text-center text-sm font-medium text-brand hover:underline"
                    >
                        Kembali ke halaman utama
                    </Link>
                </div>
            </div>
        </div>
    );
}
