import { Head, usePage } from '@inertiajs/react';
import {
    Bar,
    BarChart,
    CartesianGrid,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';
import AdminLayout from '@/layouts/admin-layout';
import { Button } from '@/components/ui/button';

type TopCareer = {
    career_name: string;
    count: number;
} | null;

type DistributionItem = {
    career_name: string;
    count: number;
};

type RecentFeedback = {
    id: number;
    visitor_name: string;
    career_name: string | null;
    match_percentage: string | null;
    score: number;
    comment: string | null;
    suggestion: string | null;
    created_at: string | null;
};

type Props = {
    kpi: {
        total_sessions: number;
        total_feedbacks: number;
        average_feedback_score: string | null;
        top_career: TopCareer;
    };
    career_distribution: DistributionItem[];
    recent_feedbacks: RecentFeedback[];
};

export default function AdminDashboard({
    kpi,
    career_distribution,
    recent_feedbacks,
}: Props) {
    const { flash } = usePage().props as {
        flash?: { success?: string | null };
    };

    return (
        <>
            <Head title="Dashboard Admin" />
            <AdminLayout>
                <div className="space-y-6">
                    <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p className="text-[11px] font-semibold tracking-[0.08em] text-brand uppercase">
                                Ringkasan
                            </p>
                            <h1 className="mt-2 text-2xl font-semibold tracking-[-0.025em] text-zinc-950">
                                Dashboard Admin
                            </h1>
                            <p className="mt-1 text-sm leading-relaxed text-zinc-600">
                                Ringkasan penggunaan TechMind dan tanggapan
                                pengunjung terbaru.
                            </p>
                        </div>
                        {flash?.success ? (
                            <p className="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-800">
                                {flash.success}
                            </p>
                        ) : null}
                    </div>

                    <section className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <KpiCard
                            label="Total Tes"
                            value={kpi.total_sessions.toLocaleString('id-ID')}
                            hint="Sesi selesai"
                        />
                        <KpiCard
                            label="Karier Terpopuler"
                            value={
                                kpi.top_career
                                    ? kpi.top_career.career_name
                                    : 'Belum ada'
                            }
                            hint={
                                kpi.top_career
                                    ? `${kpi.top_career.count} sesi peringkat 1`
                                    : undefined
                            }
                        />
                        <KpiCard
                            label="Rata-rata Penilaian"
                            value={
                                kpi.average_feedback_score === null
                                    ? 'Belum ada'
                                    : `${Number(kpi.average_feedback_score).toFixed(1)} / 100`
                            }
                            hint="Skor pengunjung"
                        />
                        <KpiCard
                            label="Total Tanggapan"
                            value={kpi.total_feedbacks.toLocaleString('id-ID')}
                            hint="Feedback tersimpan"
                        />
                    </section>

                    <section className="rounded-xl border border-zinc-200/80 bg-white p-4 shadow-[0_1px_2px_rgba(0,0,0,0.03),0_1px_3px_rgba(0,0,0,0.02)] sm:p-6">
                        <p className="text-[11px] font-semibold tracking-[0.08em] text-brand uppercase">
                            Distribusi
                        </p>
                        <h2 className="mt-1 text-base font-semibold tracking-[-0.015em] text-zinc-950">
                            Karier Rank #1
                        </h2>
                        <p className="mt-1 text-sm leading-relaxed text-zinc-600">
                            Sepuluh karier yang paling sering menjadi
                            rekomendasi utama.
                        </p>
                        <div className="mt-4 h-72">
                            {career_distribution.length === 0 ? (
                                <div className="flex h-full items-center justify-center rounded-lg border border-dashed border-zinc-200 text-sm text-zinc-500">
                                    Belum ada hasil tes yang tersimpan.
                                </div>
                            ) : (
                                <ResponsiveContainer width="100%" height="100%">
                                    <BarChart
                                        data={career_distribution}
                                        layout="vertical"
                                        margin={{ left: 8, right: 16 }}
                                    >
                                        <CartesianGrid
                                            strokeDasharray="3 3"
                                            stroke="#E4E4E7"
                                        />
                                        <XAxis
                                            type="number"
                                            allowDecimals={false}
                                            tick={{ fontSize: 11 }}
                                        />
                                        <YAxis
                                            type="category"
                                            dataKey="career_name"
                                            width={120}
                                            tick={{ fontSize: 11 }}
                                        />
                                        <Tooltip />
                                        <Bar
                                            dataKey="count"
                                            name="Jumlah Rank #1"
                                            fill="#4F46E5"
                                            radius={[0, 4, 4, 0]}
                                        />
                                    </BarChart>
                                </ResponsiveContainer>
                            )}
                        </div>
                    </section>

                    <section className="rounded-xl border border-zinc-200/80 bg-white shadow-[0_1px_2px_rgba(0,0,0,0.03),0_1px_3px_rgba(0,0,0,0.02)]">
                        <div className="flex flex-col gap-3 border-b border-zinc-200/80 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-6">
                            <div>
                                <p className="text-[11px] font-semibold tracking-[0.08em] text-brand uppercase">
                                    Tanggapan
                                </p>
                                <h2 className="mt-1 text-base font-semibold tracking-[-0.015em] text-zinc-950">
                                    Tanggapan Terbaru
                                </h2>
                                <p className="mt-1 text-sm leading-relaxed text-zinc-600">
                                    Umpan balik pengunjung setelah melihat
                                    hasil.
                                </p>
                            </div>
                            <Button
                                asChild
                                variant="outline"
                                className="shrink-0"
                            >
                                <a href="/admin/feedbacks/export">
                                    Unduh CSV Tanggapan
                                </a>
                            </Button>
                        </div>
                        <div className="overflow-x-auto">
                            <table className="w-full min-w-[720px] text-left text-sm">
                                <thead className="bg-zinc-50/80 text-xs tracking-wide text-zinc-500 uppercase">
                                    <tr>
                                        <th className="px-4 py-3.5 font-medium">
                                            Pengunjung
                                        </th>
                                        <th className="px-4 py-3.5 font-medium">
                                            Karier #1
                                        </th>
                                        <th className="px-4 py-3.5 font-medium">
                                            Nilai
                                        </th>
                                        <th className="px-4 py-3.5 font-medium">
                                            Tanggapan
                                        </th>
                                        <th className="px-4 py-3.5 font-medium">
                                            Saran
                                        </th>
                                        <th className="px-4 py-3.5 font-medium">
                                            Tanggal
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {recent_feedbacks.length === 0 ? (
                                        <tr>
                                            <td
                                                colSpan={6}
                                                className="px-4 py-10 text-center text-zinc-500"
                                            >
                                                Belum ada tanggapan pengunjung.
                                            </td>
                                        </tr>
                                    ) : (
                                        recent_feedbacks.map((feedback) => (
                                            <tr
                                                key={feedback.id}
                                                className="border-t border-zinc-100 align-top transition-colors hover:bg-zinc-50/60"
                                            >
                                                <td className="px-4 py-3.5 font-medium text-zinc-950">
                                                    {feedback.visitor_name}
                                                </td>
                                                <td className="px-4 py-3.5 text-zinc-700">
                                                    {feedback.career_name ??
                                                        '-'}
                                                    {feedback.match_percentage ? (
                                                        <span className="ml-1 text-xs text-zinc-500 tabular-nums">
                                                            (
                                                            {
                                                                feedback.match_percentage
                                                            }
                                                            %)
                                                        </span>
                                                    ) : null}
                                                </td>
                                                <td className="px-4 py-3.5">
                                                    <span className="rounded-md bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-700 tabular-nums ring-1 ring-indigo-600/15 ring-inset">
                                                        {feedback.score}/100
                                                    </span>
                                                </td>
                                                <td className="max-w-[220px] px-4 py-3.5 text-zinc-600">
                                                    {feedback.comment || '-'}
                                                </td>
                                                <td className="max-w-[220px] px-4 py-3.5 text-zinc-600">
                                                    {feedback.suggestion || '-'}
                                                </td>
                                                <td className="px-4 py-3.5 whitespace-nowrap text-zinc-500 tabular-nums">
                                                    {formatDate(
                                                        feedback.created_at,
                                                    )}
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
            </AdminLayout>
        </>
    );
}

function KpiCard({
    label,
    value,
    hint,
}: {
    label: string;
    value: string;
    hint?: string;
}) {
    return (
        <div className="rounded-xl border border-zinc-200/80 bg-white p-5 shadow-[0_1px_2px_rgba(0,0,0,0.03),0_1px_3px_rgba(0,0,0,0.02)] transition-all duration-200 hover:border-zinc-300">
            <p className="text-[11px] font-semibold tracking-[0.08em] text-zinc-500 uppercase">
                {label}
            </p>
            <p className="mt-2 truncate text-2xl font-semibold tracking-[-0.025em] text-zinc-950 tabular-nums">
                {value}
            </p>
            {hint ? <p className="mt-1 text-xs text-zinc-500">{hint}</p> : null}
        </div>
    );
}

function formatDate(value: string | null) {
    if (!value) {
        return '-';
    }

    return new Intl.DateTimeFormat('id-ID', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}
