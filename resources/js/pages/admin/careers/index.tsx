import { Head, Link, usePage } from '@inertiajs/react';
import AdminLayout from '@/layouts/admin-layout';
import { Button } from '@/components/ui/button';

type Career = {
    id: number;
    code: string;
    name: string;
    summary: string | null;
    description: string | null;
    skills: string[];
    tools: string[];
    roadmap: string[];
    sort_order: number;
    is_active: boolean;
    is_validated: boolean;
    source_note: string | null;
    total_score: number;
};

type Props = {
    careers: Career[];
    standard_total_score: number;
    max_total_score: number;
};

export default function CareersIndex({ careers, standard_total_score, max_total_score }: Props) {
    const { flash } = usePage().props as { flash?: { success?: string | null } };

    return (
        <>
            <Head title="Karier & Matriks" />
            <AdminLayout>
                <div className="space-y-6">
                    <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1 className="text-2xl font-semibold tracking-tight text-zinc-950">Karier & Matriks</h1>
                            <p className="mt-1 text-sm text-zinc-600">
                                Kelola daftar karier, status validasi, dan matriks skor profil kriteria.
                            </p>
                        </div>
                        <Button asChild>
                            <Link href="/admin/careers/create">Tambah Karier</Link>
                        </Button>
                    </div>

                    {flash?.success ? (
                        <p className="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-800">
                            {flash.success}
                        </p>
                    ) : null}

                    <div className="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-900">
                        Standar total skor profil karier adalah {standard_total_score} (12 kriteria x 5). Perhatikan indikator total skor saat melakukan perubahan.
                    </div>

                    <section className="overflow-hidden rounded-xl border border-zinc-200 bg-white">
                        <div className="overflow-x-auto">
                            <table className="w-full min-w-[760px] text-left text-sm">
                                <thead className="bg-zinc-50 text-xs uppercase tracking-wide text-zinc-500">
                                    <tr>
                                        <th className="px-4 py-3 font-medium">Karier</th>
                                        <th className="px-4 py-3 font-medium">Status</th>
                                        <th className="px-4 py-3 font-medium">Validasi</th>
                                        <th className="px-4 py-3 font-medium">Total Skor Profil</th>
                                        <th className="px-4 py-3 font-medium">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {careers.map((career) => (
                                        <tr key={career.id} className="border-t border-zinc-100 align-top">
                                            <td className="px-4 py-4">
                                                <p className="font-medium text-zinc-950">{career.name}</p>
                                                <p className="mt-1 text-xs text-zinc-500">{career.code}</p>
                                                {career.summary ? (
                                                    <p className="mt-2 text-xs text-zinc-600">{career.summary}</p>
                                                ) : null}
                                            </td>
                                            <td className="px-4 py-4">
                                                <span
                                                    className={`rounded-md px-2 py-1 text-xs font-medium ${career.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-zinc-100 text-zinc-500'}`}
                                                >
                                                    {career.is_active ? 'Aktif' : 'Nonaktif'}
                                                </span>
                                            </td>
                                            <td className="px-4 py-4">
                                                <span
                                                    className={`rounded-md px-2 py-1 text-xs font-medium ${career.is_validated ? 'bg-brand/10 text-brand' : 'bg-zinc-100 text-zinc-500'}`}
                                                >
                                                    {career.is_validated ? 'Tervalidasi' : 'Belum Tervalidasi'}
                                                </span>
                                            </td>
                                            <td className="px-4 py-4">
                                                <span
                                                    className={`rounded-md px-2 py-1 text-xs font-medium ${career.total_score === standard_total_score ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'}`}
                                                >
                                                    {career.total_score} / {max_total_score}
                                                </span>
                                            </td>
                                            <td className="px-4 py-4">
                                                <Button asChild variant="outline" size="sm">
                                                    <Link href={`/admin/careers/${career.id}/edit`}>Edit</Link>
                                                </Button>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
            </AdminLayout>
        </>
    );
}
