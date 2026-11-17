import { type ReactNode } from 'react';

export default function PublicLayout({ children }: { children: ReactNode }) {
    return (
        <div className="min-h-screen bg-background text-foreground">
            <header className="border-b border-border">
                <div className="mx-auto flex h-14 max-w-5xl items-center px-4">
                    <span className="text-lg font-semibold tracking-tight">TechMind</span>
                </div>
            </header>
            <main className="mx-auto max-w-5xl px-4 py-8">{children}</main>
        </div>
    );
}
