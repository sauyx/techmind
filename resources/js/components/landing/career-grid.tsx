import {
    BarChart3,
    Bug,
    Code2,
    Network,
    Palette,
    Server,
    ShieldCheck,
    Smartphone,
    Workflow,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';

const careers: { name: string; icon: LucideIcon }[] = [
    { name: 'Web Developer', icon: Code2 },
    { name: 'Mobile Developer', icon: Smartphone },
    { name: 'Backend Developer', icon: Server },
    { name: 'Data Analyst', icon: BarChart3 },
    { name: 'UI/UX Designer', icon: Palette },
    { name: 'QA Engineer', icon: Bug },
    { name: 'DevOps Engineer', icon: Server },
    { name: 'Network Engineer', icon: Network },
    { name: 'Cybersecurity Analyst', icon: ShieldCheck },
    { name: 'System Analyst', icon: Workflow },
];

export function CareerGrid() {
    return (
        <section className="border-t border-zinc-200">
            <div className="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:py-20">
                <div className="max-w-2xl">
                    <h2 className="text-3xl font-semibold tracking-tight text-zinc-950 sm:text-4xl">
                        10 jalur karier IT yang didukung
                    </h2>
                    <p className="mt-4 text-lg leading-8 text-zinc-600">
                        TechMind menyediakan representasi bidang karier yang dapat
                        dieksplorasi berdasarkan jawaban pengguna.
                    </p>
                </div>

                <div className="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                    {careers.map((career) => (
                        <article
                            key={career.name}
                            className="group rounded-xl border border-zinc-200 bg-white p-5 transition-colors hover:border-zinc-300"
                        >
                            <career.icon
                                className="h-6 w-6 text-brand"
                                aria-hidden="true"
                            />
                            <h3 className="mt-4 text-sm font-semibold text-zinc-950">
                                {career.name}
                            </h3>
                        </article>
                    ))}
                </div>
            </div>
        </section>
    );
}
