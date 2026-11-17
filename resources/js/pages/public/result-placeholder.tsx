import { Head } from '@inertiajs/react';
import PublicLayout from '@/layouts/public-layout';

type TopResult = {
    rank: number;
    career_code: string;
    career_name: string;
    match_percentage: number;
};

type Props = {
    session: {
        token: string;
        visitor_name: string | null;
        completed_at: string | null;
    };
    top_results: TopResult[];
};

export default function ResultPlaceholder({ session, top_results }: Props) {
    return (
        <>
            <Head title="Hasil Analisis" />
            <PublicLayout>
                <div className="mx-auto max-w-2xl">
                    <p className="text-sm font-medium text-brand">Hasil analisis</p>
                    <h1 className="mt-2 text-3xl font-semibold tracking-tight text-zinc-950">
                        Terima kasih{session.visitor_name ? `, ${session.visitor_name}` : ''}
                    </h1>
                    <p className="mt-2 text-sm leading-6 text-zinc-600">
                        Jawabanmu sudah tersimpan. Berikut tiga karier teratas berdasarkan snapshot hasil tes.
                    </p>

                    <div className="mt-6 space-y-3">
                        {top_results.map((result) => (
                            <div key={result.career_code} className="flex items-center justify-between rounded-xl border border-zinc-200 bg-white px-4 py-4">
                                <div>
                                    <p className="text-xs font-semibold text-zinc-500">Peringkat {result.rank}</p>
                                    <p className="text-base font-semibold text-zinc-950">{result.career_name}</p>
                                </div>
                                <p className="text-sm font-medium text-brand">{result.match_percentage}%</p>
                            </div>
                        ))}
                    </div>

                    <p className="mt-6 text-xs text-zinc-500">
                        Token hasil: {session.token}
                    </p>
                    <p className="mt-2 text-sm text-zinc-600">
                        Halaman hasil lengkap dengan radar, gap analysis, dan penjelasan akan tersedia pada sesi berikutnya.
                    </p>
                </div>
            </PublicLayout>
        </>
    );
}
