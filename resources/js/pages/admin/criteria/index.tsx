import { Head, useForm, usePage } from '@inertiajs/react';
import { useState, type FormEvent } from 'react';
import AdminLayout from '@/layouts/admin-layout';
import { Button } from '@/components/ui/button';

type Criterion = {
    id: number;
    code: string;
    name: string;
    description: string | null;
    weight: number;
    step_group: number;
    display_order: number;
    is_active: boolean;
};

type Props = {
    criteria: Criterion[];
};

const stepTitles: Record<number, string> = {
    1: 'Cara Berpikir',
    2: 'Kreasi & Interaksi',
    3: 'Teknis & Bisnis',
};

export default function CriteriaIndex({ criteria }: Props) {
    const { flash } = usePage().props as { flash?: { success?: string | null } };
    const groups = [1, 2, 3].map((step) => ({
        step,
        title: stepTitles[step],
        items: criteria.filter((criterion) => criterion.step_group === step),
    }));

    return (
        <>
            <Head title="Kriteria & Bobot" />
            <AdminLayout>
                <div className="space-y-6">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight text-zinc-950">Kriteria & Bobot</h1>
                        <p className="mt-1 text-sm text-zinc-600">
                            Kelola 12 kriteria penilaian dan pengali bobot untuk perhitungan SAW.
                        </p>
                    </div>

                    {flash?.success ? (
                        <p className="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-800">
                            {flash.success}
                        </p>
                    ) : null}

                    <div className="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-900">
                        Perubahan bobot dan kriteria hanya berlaku untuk sesi tes baru. Hasil tes pengunjung sebelumnya tetap tersimpan sesuai snapshot.
                    </div>

                    {groups.map((group) => (
                        <section key={group.step} className="rounded-xl border border-zinc-200 bg-white">
                            <div className="border-b border-zinc-200 px-4 py-3 sm:px-6">
                                <h2 className="text-base font-semibold text-zinc-950">
                                    Langkah {group.step}: {group.title}
                                </h2>
                                <p className="text-xs text-zinc-500">{group.items.length} kriteria</p>
                            </div>
                            <div className="divide-y divide-zinc-100">
                                {group.items.map((criterion) => (
                                    <CriterionRow key={criterion.id} criterion={criterion} />
                                ))}
                            </div>
                        </section>
                    ))}
                </div>
            </AdminLayout>
        </>
    );
}

function CriterionRow({ criterion }: { criterion: Criterion }) {
    const [editing, setEditing] = useState(false);

    const form = useForm({
        name: criterion.name,
        description: criterion.description ?? '',
        weight: criterion.weight,
        step_group: criterion.step_group,
        display_order: criterion.display_order,
        is_active: criterion.is_active,
    });

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        form.put(`/admin/criteria/${criterion.id}`, {
            preserveScroll: true,
            onSuccess: () => setEditing(false),
        });
    }

    if (!editing) {
        return (
            <div className="flex flex-col gap-3 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div className="min-w-0">
                    <div className="flex flex-wrap items-center gap-2">
                        <p className="text-sm font-medium text-zinc-950">{criterion.name}</p>
                        <span className="rounded-md border border-zinc-200 px-2 py-0.5 text-[11px] text-zinc-500">
                            {criterion.code}
                        </span>
                        <span
                            className={`rounded-md px-2 py-0.5 text-[11px] font-medium ${criterion.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-zinc-100 text-zinc-500'}`}
                        >
                            {criterion.is_active ? 'Aktif' : 'Nonaktif'}
                        </span>
                    </div>
                    <p className="mt-1 text-sm text-zinc-600">{criterion.description || 'Tanpa deskripsi.'}</p>
                    <p className="mt-1 text-xs text-zinc-500">
                        Bobot x{criterion.weight.toFixed(2)} | Urutan {criterion.display_order}
                    </p>
                </div>
                <Button type="button" variant="outline" onClick={() => setEditing(true)}>
                    Edit
                </Button>
            </div>
        );
    }

    return (
        <form onSubmit={submit} className="space-y-4 px-4 py-4 sm:px-6">
            <div className="grid gap-4 sm:grid-cols-2">
                <div>
                    <label className="text-sm font-medium text-zinc-950" htmlFor={`name-${criterion.id}`}>
                        Nama kriteria
                    </label>
                    <input
                        id={`name-${criterion.id}`}
                        value={form.data.name}
                        onChange={(event) => form.setData('name', event.target.value)}
                        className="mt-1 h-10 w-full rounded-lg border border-zinc-300 px-3 text-sm text-zinc-950 outline-none focus:border-brand focus:ring-2 focus:ring-brand/20"
                    />
                    {form.errors.name ? <p className="mt-1 text-xs text-red-600">{form.errors.name}</p> : null}
                </div>
                <div>
                    <label className="text-sm font-medium text-zinc-950" htmlFor={`weight-${criterion.id}`}>
                        Pengali bobot (0.50 - 2.00)
                    </label>
                    <input
                        id={`weight-${criterion.id}`}
                        type="number"
                        min={0.5}
                        max={2}
                        step={0.05}
                        value={form.data.weight}
                        onChange={(event) => form.setData('weight', Number(event.target.value))}
                        className="mt-1 h-10 w-full rounded-lg border border-zinc-300 px-3 text-sm text-zinc-950 outline-none focus:border-brand focus:ring-2 focus:ring-brand/20"
                    />
                    {form.errors.weight ? <p className="mt-1 text-xs text-red-600">{form.errors.weight}</p> : null}
                </div>
            </div>

            <div>
                <label className="text-sm font-medium text-zinc-950" htmlFor={`description-${criterion.id}`}>
                    Deskripsi
                </label>
                <textarea
                    id={`description-${criterion.id}`}
                    rows={2}
                    value={form.data.description}
                    onChange={(event) => form.setData('description', event.target.value)}
                    className="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm text-zinc-950 outline-none focus:border-brand focus:ring-2 focus:ring-brand/20"
                />
                {form.errors.description ? (
                    <p className="mt-1 text-xs text-red-600">{form.errors.description}</p>
                ) : null}
            </div>

            <div className="grid gap-4 sm:grid-cols-3">
                <div>
                    <label className="text-sm font-medium text-zinc-950" htmlFor={`step-${criterion.id}`}>
                        Langkah
                    </label>
                    <select
                        id={`step-${criterion.id}`}
                        value={form.data.step_group}
                        onChange={(event) => form.setData('step_group', Number(event.target.value))}
                        className="mt-1 h-10 w-full rounded-lg border border-zinc-300 px-3 text-sm text-zinc-950 outline-none focus:border-brand focus:ring-2 focus:ring-brand/20"
                    >
                        <option value={1}>Langkah 1</option>
                        <option value={2}>Langkah 2</option>
                        <option value={3}>Langkah 3</option>
                    </select>
                    {form.errors.step_group ? (
                        <p className="mt-1 text-xs text-red-600">{form.errors.step_group}</p>
                    ) : null}
                </div>
                <div>
                    <label className="text-sm font-medium text-zinc-950" htmlFor={`order-${criterion.id}`}>
                        Urutan tampil
                    </label>
                    <input
                        id={`order-${criterion.id}`}
                        type="number"
                        min={0}
                        value={form.data.display_order}
                        onChange={(event) => form.setData('display_order', Number(event.target.value))}
                        className="mt-1 h-10 w-full rounded-lg border border-zinc-300 px-3 text-sm text-zinc-950 outline-none focus:border-brand focus:ring-2 focus:ring-brand/20"
                    />
                    {form.errors.display_order ? (
                        <p className="mt-1 text-xs text-red-600">{form.errors.display_order}</p>
                    ) : null}
                </div>
                <label className="flex items-center gap-2 self-end pb-2 text-sm font-medium text-zinc-950">
                    <input
                        type="checkbox"
                        checked={form.data.is_active}
                        onChange={(event) => form.setData('is_active', event.target.checked)}
                        className="size-4 rounded border-zinc-300 text-brand focus:ring-brand"
                    />
                    Kriteria aktif
                </label>
            </div>

            <div className="flex flex-wrap gap-2">
                <Button type="submit" disabled={form.processing}>
                    {form.processing ? 'Menyimpan...' : 'Simpan perubahan'}
                </Button>
                <Button type="button" variant="outline" onClick={() => setEditing(false)}>
                    Batal
                </Button>
            </div>
        </form>
    );
}
