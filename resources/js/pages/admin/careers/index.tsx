import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AdminLayout from '@/layouts/admin-layout';
import { Button } from '@/components/ui/button';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';

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
    profiles: Record<string, number>;
};

type Criterion = { id: number; code: string; name: string; display_order: number };

type Props = {
    careers: Career[];
    standard_total_score: number;
    max_total_score: number;
    criteria: Criterion[];
};

export default function CareersIndex({
    careers,
    standard_total_score,
    max_total_score,
    criteria,
}: Props) {
    const { flash } = usePage().props as {
        flash?: { success?: string | null };
    };

    const [open, setOpen] = useState(false);
    const [toggleId, setToggleId] = useState<number | null>(null);
    const [toggleTarget, setToggleTarget] = useState<Career | null>(null);
    const [impact, setImpact] = useState<any>(null);
    const [checkingImpact, setCheckingImpact] = useState(false);

    const onToggle = (career: Career) => {
        setToggleId(career.id);
        setOpen(true);
        void checkToggleImpact(career);
    };

    const onConfirm = () => {
        if (toggleId !== null) {
            router.patch(`/admin/careers/${toggleId}/toggle`);
        }
        setOpen(false);
    };

    const checkToggleImpact = async (career: Career) => {
        setToggleTarget(career);
        setCheckingImpact(true);
        try {
            const careersPayload = careers
                .map((item) => {
                    const scores: Record<string, number> = {};
                    criteria.forEach((criterion) => {
                        scores[criterion.code] = Number(
                            item.profiles[String(criterion.id)] ?? 3,
                        );
                    });
                    return {
                        code: item.code,
                        name: item.name,
                        sort_order: item.sort_order,
                        scores,
                        is_active:
                            item.id === career.id
                                ? !item.is_active
                                : item.is_active,
                    };
                })
                .filter((item) => item.is_active);

            const response = await fetch('/admin/kesehatan-matriks/preview', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN':
                        document.querySelector('meta[name="csrf-token"]')
                            ?.getAttribute('content') ?? '',
                    Accept: 'application/json',
                },
                body: JSON.stringify({
                    criteria: criteria.map((criterion) => ({
                        code: criterion.code,
                        multiplier: 1,
                        sort_order: criterion.display_order,
                        name: criterion.name,
                    })),
                    careers: careersPayload,
                    samples: 300,
                    seed: 42,
                }),
            });
            setImpact(await response.json());
        } finally {
            setCheckingImpact(false);
        }
    };

    return (
        <>
            <Head title="Karier & Matriks" />
            <AdminLayout>
                <div className="space-y-6">
                    <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p className="text-[11px] font-semibold tracking-[0.08em] text-brand uppercase">
                                Referensi
                            </p>
                            <h1 className="mt-2 text-2xl font-semibold tracking-[-0.025em] text-zinc-950">
                                Karier & Matriks
                            </h1>
                            <p className="mt-1 text-sm leading-relaxed text-zinc-600">
                                Kelola daftar karier, status validasi, dan
                                matriks skor profil kriteria.
                            </p>
                        </div>
                        <Button asChild>
                            <Link href="/admin/careers/create">
                                Tambah Karier
                            </Link>
                        </Button>
                    </div>

                    {flash?.success ? (
                        <p className="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-800">
                            {flash.success}
                        </p>
                    ) : null}

                    <div className="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-900">
                        Standar total skor profil karier adalah{' '}
                        {standard_total_score} (12 kriteria x 5). Perhatikan
                        indikator total skor saat melakukan perubahan.
                    </div>

                    <section className="overflow-hidden rounded-xl border border-zinc-200/80 bg-white shadow-[0_1px_2px_rgba(0,0,0,0.03),0_1px_3px_rgba(0,0,0,0.02)]">
                        <div className="overflow-x-auto">
                            <table className="w-full min-w-[760px] text-left text-sm">
                                <thead className="bg-zinc-50/80 text-xs tracking-wide text-zinc-500 uppercase">
                                    <tr>
                                        <th className="px-4 py-3.5 font-medium">
                                            Karier
                                        </th>
                                        <th className="px-4 py-3.5 font-medium">
                                            Status
                                        </th>
                                        <th className="px-4 py-3.5 font-medium">
                                            Validasi
                                        </th>
                                        <th className="px-4 py-3.5 font-medium">
                                            Total Skor Profil
                                        </th>
                                        <th className="px-4 py-3.5 font-medium">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {careers.map((career) => (
                                        <tr
                                            key={career.id}
                                            className="border-t border-zinc-100 align-top transition-colors hover:bg-zinc-50/60"
                                        >
                                            <td className="px-4 py-3.5">
                                                <p className="font-medium text-zinc-950">
                                                    {career.name}
                                                </p>
                                                <p className="mt-1 text-xs text-zinc-500">
                                                    {career.code}
                                                </p>
                                                {career.summary ? (
                                                    <p className="mt-2 text-xs leading-relaxed text-zinc-600">
                                                        {career.summary}
                                                    </p>
                                                ) : null}
                                            </td>
                                            <td className="px-4 py-3.5">
                                                <span
                                                    className={`rounded-full px-2.5 py-1 text-xs font-medium ${career.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-zinc-100 text-zinc-500'}`}
                                                >
                                                    {career.is_active
                                                        ? 'Aktif'
                                                        : 'Nonaktif'}
                                                </span>
                                            </td>
                                            <td className="px-4 py-3.5">
                                                <span
                                                    className={`rounded-full px-2.5 py-1 text-xs font-medium ${career.is_validated ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-600/15 ring-inset' : 'bg-zinc-100 text-zinc-500'}`}
                                                >
                                                    {career.is_validated
                                                        ? 'Tervalidasi'
                                                        : 'Belum Tervalidasi'}
                                                </span>
                                            </td>
                                            <td className="px-4 py-3.5">
                                                <span
                                                    className={`rounded-full px-2.5 py-1 text-xs font-medium tabular-nums ${career.total_score === standard_total_score ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'}`}
                                                >
                                                    {career.total_score} /{' '}
                                                    {max_total_score}
                                                </span>
                                            </td>
                                            <td className="px-4 py-3.5">
                                                <div className="flex flex-wrap gap-2">
                                                    <Button
                                                        type="button"
                                                        variant="outline"
                                                        size="sm"
                                                        onClick={() =>
                                                            onToggle(career)
                                                        }
                                                    >
                                                        {career.is_active
                                                            ? 'Nonaktifkan'
                                                            : 'Aktifkan'}
                                                    </Button>
                                                    <Button
                                                        asChild
                                                        variant="outline"
                                                        size="sm"
                                                    >
                                                        <Link
                                                            href={`/admin/careers/${career.id}/edit`}
                                                        >
                                                            Edit
                                                        </Link>
                                                    </Button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <AlertDialog open={open} onOpenChange={setOpen}>
                        <AlertDialogContent>
                            <AlertDialogHeader>
                                <AlertDialogTitle>
                                    Konfirmasi Aksi
                                </AlertDialogTitle>
                                <AlertDialogDescription>
                                    Apakah Anda yakin ingin{' '}
                                    {toggleId !== null &&
                                        (careers.find(
                                            (career) => career.id === toggleId,
                                        )?.is_active
                                            ? 'menonaktifkan'
                                            : 'mengaktifkan')}{' '}
                                    karier ini?
                                    {checkingImpact ? (
                                        <p className="mt-2 animate-pulse text-sm text-zinc-500">
                                            Memeriksa dampak perubahan...
                                        </p>
                                    ) : impact ? (
                                        <div className={`mt-2 rounded-lg p-3 text-sm ${impact.healthy ? 'bg-emerald-50 text-emerald-800' : 'bg-red-50 text-red-800'}`}>
                                            {impact.healthy ? 'Cek Dampak: matriks tetap sehat.' : 'Cek Dampak: toggle berisiko mengganggu kesehatan matriks.'}
                                        </div>
                                    ) : null}
                                </AlertDialogDescription>
                            </AlertDialogHeader>
                            <AlertDialogFooter>
                                <AlertDialogCancel
                                    onClick={() => setOpen(false)}
                                >
                                    Batal
                                </AlertDialogCancel>
                                <AlertDialogAction
                                    onClick={onConfirm}
                                    className="bg-red-500 hover:bg-red-600 focus:ring-red-500"
                                >
                                    Ya, {toggleId !== null && (careers.find(
                                            (career) => career.id === toggleId,
                                        )?.is_active
                                            ? 'Nonaktifkan'
                                            : 'Aktifkan')}
                                </AlertDialogAction>
                            </AlertDialogFooter>
                        </AlertDialogContent>
                    </AlertDialog>
                </div>
            </AdminLayout>
        </>
    );
}
