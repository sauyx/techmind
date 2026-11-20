import { Head } from '@inertiajs/react';
import AdminLayout from '@/layouts/admin-layout';

type Props = { feedbacks: { data: any[] } };

export default function FeedbacksIndex({ feedbacks }: Props) {
    return (
        <>
            <Head title="Feedback" />
            <AdminLayout>
                <div className="space-y-6">
                    <h1 className="text-2xl font-semibold text-zinc-950">Feedback Pengunjung</h1>
                    <div className="overflow-x-auto rounded-xl border border-zinc-200/80 bg-white">
                        <table className="w-full min-w-[720px] text-sm">
                            <thead className="bg-zinc-50 text-xs uppercase text-zinc-500">
                                <tr>
                                    <th className="px-4 py-3 text-left">Tanggal</th>
                                    <th className="px-4 py-3 text-left">Pengunjung</th>
                                    <th className="px-4 py-3 text-left">Karier #1</th>
                                    <th className="px-4 py-3 text-left">Skor</th>
                                    <th className="px-4 py-3 text-left">Tanggapan</th>
                                    <th className="px-4 py-3 text-left">Saran</th>
                                </tr>
                            </thead>
                            <tbody>
                                {feedbacks.data.map((f: any) => {
                                    const top = f.session?.results?.find((r: any) => r.rank === 1);
                                    return (
                                        <tr key={f.id} className="border-t border-zinc-100">
                                            <td className="px-4 py-3">{new Date(f.created_at).toLocaleString('id-ID')}</td>
                                            <td className="px-4 py-3">{f.session?.visitor_name || 'Anonim'}</td>
                                            <td className="px-4 py-3">{top ? `${top.career_name} ${top.match_percentage}%` : '-'}</td>
                                            <td className="px-4 py-3">{f.score}/100</td>
                                            <td className="px-4 py-3">{f.comment || '-'}</td>
                                            <td className="px-4 py-3">{f.suggestion || '-'}</td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>
                    <a href="/admin/feedbacks/export" className="text-sm text-brand">Unduh CSV</a>
                </div>
            </AdminLayout>
        </>
    );
}
