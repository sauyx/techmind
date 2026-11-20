import { Head, Link, router } from '@inertiajs/react';
import AdminLayout from '@/layouts/admin-layout';
import { Button } from '@/components/ui/button';

type SessionRow = {
    id: number;
    token: string;
    visitor_name: string | null;
    created_at: string;
    results?: { career_name: string; rank: number; match_percentage: string; is_tied?: boolean }[];
    aiExplanation?: { status: string } | null;
    feedback?: { score: number } | null;
};

type Props = { sessions: { data: SessionRow[]; links: any[] }; filters: any };

export default function SessionsIndex({ sessions, filters }: Props) {
    return (
        <>
            <Head title="Sesi Tes" />
            <AdminLayout>
                <div className="space-y-6">
                    <h1 className="text-2xl font-semibold text-zinc-950">Sesi Tes</h1>
                    <div className="overflow-x-auto rounded-xl border border-zinc-200/80 bg-white">
                        <table className="w-full min-w-[720px] text-sm">
                            <thead className="bg-zinc-50 text-xs uppercase text-zinc-500">
                                <tr>
                                    <th className="px-4 py-3 text-left">Tanggal</th>
                                    <th className="px-4 py-3 text-left">Pengunjung</th>
                                    <th className="px-4 py-3 text-left">Karier #1</th>
                                    <th className="px-4 py-3 text-left">AI</th>
                                    <th className="px-4 py-3 text-left">Feedback</th>
                                    <th className="px-4 py-3 text-left">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                {sessions.data.map((s) => {
                                    const top = s.results?.find((r) => r.rank === 1);
                                    return (
                                        <tr key={s.id} className="border-t border-zinc-100">
                                            <td className="px-4 py-3">{new Date(s.created_at).toLocaleString('id-ID')}</td>
                                            <td className="px-4 py-3">{s.visitor_name || 'Anonim'}</td>
                                            <td className="px-4 py-3">
                                                {top ? `${top.career_name}${top.is_tied ? ' (Setara)' : ''} ${top.match_percentage}%` : '-'}
                                            </td>
                                            <td className="px-4 py-3">{s.aiExplanation?.status ?? '-'}</td>
                                            <td className="px-4 py-3">{s.feedback ? `${s.feedback.score}/100` : '-'}</td>
                                            <td className="px-4 py-3">
                                                <div className="flex gap-2">
                                                    <Button asChild size="sm" variant="outline">
                                                        <Link href={`/admin/sesi/${s.id}`}>Detail</Link>
                                                    </Button>
                                                    <Button asChild size="sm" variant="outline">
                                                        <a href={`/hasil/${s.token}`} target="_blank" rel="noreferrer">Lihat</a>
                                                    </Button>
                                                    <Button
                                                        size="sm"
                                                        variant="outline"
                                                        onClick={() => {
                                                            if (window.confirm('Hapus sesi ini?')) {
                                                                router.delete(`/admin/sesi/${s.id}`);
                                                            }
                                                        }}
                                                    >
                                                        Hapus
                                                    </Button>
                                                </div>
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>
                </div>
            </AdminLayout>
        </>
    );
}
