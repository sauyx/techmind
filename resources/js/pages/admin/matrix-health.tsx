import { Head } from '@inertiajs/react';
import AdminLayout from '@/layouts/admin-layout';

type Health = {
    self_consistency: { career_code: string; rank: number; margin: number; runner_up: string }[];
    nearest_neighbors: { career_code: string; nearest: { career_code: string; distance: number }[] }[];
    robustness: { career_code: string; percent: number }[];
    tie_cases: { max_top_tie_count: number; total_cases: number };
};

type Props = {
    health: Health;
    healthy: boolean;
    samples: number;
    seed: number;
};

export default function MatrixHealth({ health, healthy, samples, seed }: Props) {
    return (
        <>
            <Head title="Kesehatan Matriks" />
            <AdminLayout>
                <div className="space-y-6">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-[-0.025em] text-zinc-950">
                            Kesehatan Matriks
                        </h1>
                        <p className="mt-1 text-sm text-zinc-600">
                            Status: {healthy ? 'Matriks sehat' : 'Perlu perhatian'} (sampel {samples}, seed {seed})
                        </p>
                    </div>

                    <section className="rounded-xl border border-zinc-200/80 bg-white p-4 sm:p-6">
                        <h2 className="text-base font-semibold text-zinc-950">
                            Self-consistency
                        </h2>
                        <table className="mt-3 w-full text-sm">
                            <thead>
                                <tr className="text-left text-zinc-500">
                                    <th className="py-2">Karier</th>
                                    <th className="py-2">Rank</th>
                                    <th className="py-2">Margin</th>
                                    <th className="py-2">Runner-up</th>
                                </tr>
                            </thead>
                            <tbody>
                                {health.self_consistency.map((row) => (
                                    <tr key={row.career_code} className="border-t border-zinc-100">
                                        <td className="py-2">{row.career_code}</td>
                                        <td className="py-2">{row.rank}</td>
                                        <td className={`py-2 ${row.margin * 100 < 2 ? 'text-red-600' : ''}`}>
                                            {(row.margin * 100).toFixed(2)}
                                        </td>
                                        <td className="py-2">{row.runner_up}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </section>

                    <section className="rounded-xl border border-zinc-200/80 bg-white p-4 sm:p-6">
                        <h2 className="text-base font-semibold text-zinc-950">
                            Robustness
                        </h2>
                        <table className="mt-3 w-full text-sm">
                            <thead>
                                <tr className="text-left text-zinc-500">
                                    <th className="py-2">Karier</th>
                                    <th className="py-2">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                {health.robustness.map((row) => (
                                    <tr key={row.career_code} className="border-t border-zinc-100">
                                        <td className="py-2">{row.career_code}</td>
                                        <td className={`py-2 ${row.percent < 80 ? 'text-red-600' : ''}`}>
                                            {row.percent.toFixed(2)}%
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </section>

                    <section className="rounded-xl border border-zinc-200/80 bg-white p-4 sm:p-6">
                        <h2 className="text-base font-semibold text-zinc-950">
                            Jarak terdekat
                        </h2>
                        <table className="mt-3 w-full text-sm">
                            <thead>
                                <tr className="text-left text-zinc-500">
                                    <th className="py-2">Karier</th>
                                    <th className="py-2">Tetangga</th>
                                    <th className="py-2">Jarak</th>
                                </tr>
                            </thead>
                            <tbody>
                                {health.nearest_neighbors.map((row) =>
                                    row.nearest.slice(0, 1).map((n) => (
                                        <tr key={row.career_code + n.career_code} className="border-t border-zinc-100">
                                            <td className="py-2">{row.career_code}</td>
                                            <td className="py-2">{n.career_code}</td>
                                            <td className={`py-2 ${n.distance < 3 ? 'text-red-600' : ''}`}>
                                                {n.distance.toFixed(3)}
                                            </td>
                                        </tr>
                                    )),
                                )}
                            </tbody>
                        </table>
                    </section>

                    <section className="rounded-xl border border-zinc-200/80 bg-white p-4 sm:p-6">
                        <p className="text-sm text-zinc-600">
                            Tie cases: maks seri rank #1 = {health.tie_cases.max_top_tie_count} dari {health.tie_cases.total_cases} kasus.
                        </p>
                    </section>
                </div>
            </AdminLayout>
        </>
    );
}
