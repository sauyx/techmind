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

type Career = {
    name: string;
    category: string;
    icon: LucideIcon;
};

const careers: Career[] = [
    { name: 'Web Developer', category: 'Engineering', icon: Code2 },
    { name: 'Mobile Developer', category: 'Engineering', icon: Smartphone },
    { name: 'Backend Developer', category: 'Engineering', icon: Server },
    { name: 'Data Analyst', category: 'Data & AI', icon: BarChart3 },
    { name: 'UI/UX Designer', category: 'Design & Product', icon: Palette },
    { name: 'QA Engineer', category: 'Engineering', icon: Bug },
    { name: 'DevOps Engineer', category: 'Infra & Security', icon: Server },
    { name: 'Network Engineer', category: 'Infra & Security', icon: Network },
    {
        name: 'Cybersecurity Analyst',
        category: 'Infra & Security',
        icon: ShieldCheck,
    },
    { name: 'System Analyst', category: 'Design & Product', icon: Workflow },
];

export function CareerGrid() {
    return (
        <section className="border-t border-zinc-200/80 bg-white">
            <div className="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:py-20">
                <div className="max-w-2xl">
                    <p className="text-[11px] font-semibold tracking-[0.08em] text-brand uppercase">
                        Cakupan jalur
                    </p>
                    <h2 className="mt-3 text-3xl font-semibold tracking-[-0.025em] text-zinc-950 sm:text-4xl">
                        10 jalur karier IT yang didukung
                    </h2>
                    <p className="mt-4 text-lg leading-relaxed text-zinc-600">
                        TechMind menyediakan representasi bidang karier yang
                        dapat dieksplorasi berdasarkan jawaban pengguna.
                    </p>
                </div>

                <div className="mt-10 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    {careers.map((career, index) => (
                        <article
                            key={career.name}
                            className={`group relative flex flex-col justify-between overflow-hidden rounded-xl border border-zinc-200/80 bg-white shadow-[0_1px_2px_rgba(0,0,0,0.03),0_1px_3px_rgba(0,0,0,0.02)] transition-all duration-200 hover:border-zinc-300 ${
                                index === 0
                                    ? 'p-6 sm:col-span-2 lg:col-span-3'
                                    : 'p-4'
                            }`}
                        >
                            {index === 0 ? (
                                <div
                                    aria-hidden="true"
                                    className="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-indigo-500/40 to-transparent"
                                />
                            ) : null}
                            <div className="flex items-start justify-between gap-3">
                                <span className="flex size-9 items-center justify-center rounded-lg border border-zinc-200/80 bg-zinc-50 text-brand transition-colors duration-200 group-hover:border-indigo-200 group-hover:bg-indigo-50/60">
                                    <career.icon
                                        className={
                                            index === 0 ? 'size-5' : 'size-4'
                                        }
                                        aria-hidden="true"
                                    />
                                </span>
                                <span className="rounded-full border border-zinc-200/80 bg-zinc-50/80 px-2.5 py-1 text-[10px] font-semibold tracking-[0.06em] text-zinc-500 uppercase">
                                    {career.category}
                                </span>
                            </div>
                            <div>
                                <h3
                                    className={`font-semibold tracking-[-0.015em] text-zinc-950 ${
                                        index === 0
                                            ? 'mt-6 text-xl'
                                            : 'mt-4 text-sm'
                                    }`}
                                >
                                    {career.name}
                                </h3>
                                {index === 0 ? (
                                    <p className="mt-2 max-w-xl text-sm leading-relaxed text-zinc-600">
                                        Jalur yang menggabungkan antarmuka,
                                        layanan backend, dan proses rilis
                                        aplikasi web.
                                    </p>
                                ) : null}
                            </div>
                        </article>
                    ))}
                </div>
            </div>
        </section>
    );
}
