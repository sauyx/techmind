import { Head, Link } from '@inertiajs/react';
import AdminLayout from '@/layouts/admin-layout';

type Props = { session: any };

export default function SessionShow({ session }: Props) {
    return (
        <>
            <Head title="Detail Sesi" />
            <AdminLayout>
                <div className="space-y-6">
                    <h1 className="text-2xl font-semibold text-zinc-950">Detail Sesi</h1>
                    <p className="text-sm text-zinc-600">Token: {session.token}</p>
                    <p className="text-sm text-zinc-600">Pengunjung: {session.visitor_name || 'Anonim'}</p>

                    <section className="rounded-xl border border-zinc-200/80 bg-white p-4">
                        <h2 className="font-semibold text-zinc-950">Jawaban</h2>
                        <ul className="mt-2 space-y-1 text-sm text-zinc-600">
                            {session.answers?.map((a: any) => (
                                <li key={a.id}>{a.criterion_name}: {a.rating}</li>
                            ))}
                        </ul>
                    </section>

                    <section className="rounded-xl border border-zinc-200/80 bg-white p-4">
                        <h2 className="font-semibold text-zinc-950">Ranking</h2>
                        <ol className="mt-2 space-y-1 text-sm text-zinc-600">
                            {session.results?.map((r: any) => (
                                <li key={r.id}>#{r.rank} {r.career_name} ({r.match_percentage}%) {r.is_tied ? '(Setara)' : ''}</li>
                            ))}
                        </ol>
                    </section>

                    <section className="rounded-xl border border-zinc-200/80 bg-white p-4">
                        <h2 className="font-semibold text-zinc-950">Penjelasan AI</h2>
                        <p className="mt-2 text-sm text-zinc-600">
                            Status: {session.aiExplanation?.status ?? '-'} (attempts {session.aiExplanation?.attempts ?? '-'})
                        </p>
                        <p className="mt-2 whitespace-pre-line text-sm text-zinc-700">{session.aiExplanation?.content}</p>
                        {session.aiExplanation?.error_message ? (
                            <p className="mt-2 text-xs text-red-600">{session.aiExplanation.error_message}</p>
                        ) : null}
                    </section>

                    {session.feedback ? (
                        <section className="rounded-xl border border-zinc-200/80 bg-white p-4">
                            <h2 className="font-semibold text-zinc-950">Feedback</h2>
                            <p className="mt-2 text-sm text-zinc-600">Skor: {session.feedback.score}/100</p>
                            <p className="mt-1 text-sm text-zinc-600">{session.feedback.comment}</p>
                            <p className="mt-1 text-sm text-zinc-600">{session.feedback.suggestion}</p>
                        </section>
                    ) : null}

                    <Link href="/admin/sesi" className="text-sm text-brand">Kembali</Link>
                </div>
            </AdminLayout>
        </>
    );
}
