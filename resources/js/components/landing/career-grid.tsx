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

                <div className="mt-10 grid grid-cols-2 gap-3 sm:grid-cols-6">
                    {careers.map((career, index) => (
                        <article
                            key={career.name}
                            className={`group rounded-xl border border-zinc-200/80 bg-white transition-colors hover:border-zinc-300 ${
                                index === 0
                                    ? 'col-span-2 row-span-2 flex flex-col justify-between p-6'
                                    : 'col-span-1 p-4'
                            }`}
                        >
                            <career.icon
                                className={index === 0 ? 'h-7 w-7 text-brand' : 'h-5 w-5 text-brand'}
                                aria-hidden="true"
                            />
                            <div>
                                <h3
                                    className={`font-semibold tracking-tight text-zinc-950 ${
                                        index === 0 ? 'mt-6 text-lg' : 'mt-4 text-sm'
                                    }`}
                                >
                                    {career.name}
                                </h3>
                                {index === 0 ? (
                                    <p className="mt-2 text-sm leading-6 text-zinc-600">
                                        Jalur yang menggabungkan antarmuka, layanan backend, dan proses rilis aplikasi web.
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
