import { Head, Link, useForm } from '@inertiajs/react';
import { type FormEvent, type ReactNode } from 'react';
import AdminLayout from '@/layouts/admin-layout';
import { Button } from '@/components/ui/button';

type Criterion = {
    id: number;
    code: string;
    name: string;
    step_group: number;
    display_order: number;
};

type CareerDetail = {
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
    profiles: Record<string, number>;
    total_score: number;
};

type Props = {
    career: CareerDetail | null;
    criteria: Criterion[];
    standard_total_score: number;
};

type ListField = 'skills' | 'tools' | 'roadmap';

export default function CareerForm({
    career,
    criteria,
    standard_total_score,
}: Props) {
    const form = useForm({
        code: career?.code ?? '',
        name: career?.name ?? '',
        summary: career?.summary ?? '',
        description: career?.description ?? '',
        skills: career?.skills ?? [''],
        tools: career?.tools ?? [''],
        roadmap: career?.roadmap ?? [''],
        sort_order: career?.sort_order ?? 0,
        is_active: career?.is_active ?? true,
        is_validated: career?.is_validated ?? false,
        source_note: career?.source_note ?? '',
        profiles: criteria.reduce<Record<string, number>>(
            (accumulator, criterion) => {
                accumulator[String(criterion.id)] =
                    career?.profiles?.[String(criterion.id)] ?? 3;

                return accumulator;
            },
            {},
        ),
    });

    const totalScore = Object.values(form.data.profiles).reduce(
        (sum, score) => sum + Number(score || 0),
        0,
    );

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        if (career) {
            form.put(`/admin/careers/${career.id}`);
        } else {
            form.post('/admin/careers');
        }
    }

    function updateListItem(field: ListField, index: number, value: string) {
        const next = [...form.data[field]];
        next[index] = value;
        form.setData(field, next);
    }

    function addListItem(field: ListField) {
        form.setData(field, [...form.data[field], '']);
    }

    function removeListItem(field: ListField, index: number) {
        form.setData(
            field,
            form.data[field].filter((_, itemIndex) => itemIndex !== index),
        );
    }

    function updateProfile(criterionId: number, score: number) {
        form.setData('profiles', {
            ...form.data.profiles,
            [String(criterionId)]: score,
        });
    }

    return (
        <>
            <Head title={career ? `Edit ${career.name}` : 'Tambah Karier'} />
            <AdminLayout>
                <form onSubmit={submit} className="space-y-6">
                    <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p className="text-[11px] font-semibold tracking-[0.08em] text-brand uppercase">
                                {career ? 'Edit referensi' : 'Tambah referensi'}
                            </p>
                            <h1 className="mt-2 text-2xl font-semibold tracking-[-0.025em] text-zinc-950">
                                {career
                                    ? `Edit Karier: ${career.name}`
                                    : 'Tambah Karier Baru'}
                            </h1>
                            <p className="mt-1 text-sm leading-relaxed text-zinc-600">
                                Lengkapi informasi karier dan matriks skor
                                profil 1-5 untuk seluruh kriteria aktif.
                            </p>
                        </div>
                        <Button asChild variant="outline">
                            <Link href="/admin/careers">Kembali</Link>
                        </Button>
                    </div>

                    <section className="rounded-xl border border-zinc-200/80 bg-white p-4 shadow-[0_1px_2px_rgba(0,0,0,0.03),0_1px_3px_rgba(0,0,0,0.02)] sm:p-6">
                        <h2 className="text-base font-semibold tracking-[-0.015em] text-zinc-950">
                            A. Informasi Karier
                        </h2>
                        <div className="mt-4 grid gap-4 sm:grid-cols-2">
                            <Field label="Kode karier" error={form.errors.code}>
                                <input
                                    value={form.data.code}
                                    onChange={(event) =>
                                        form.setData('code', event.target.value)
                                    }
                                    className="h-10 w-full rounded-lg border border-zinc-300 bg-white px-3 text-sm text-zinc-950 transition outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                                />
                            </Field>
                            <Field label="Nama karier" error={form.errors.name}>
                                <input
                                    value={form.data.name}
                                    onChange={(event) =>
                                        form.setData('name', event.target.value)
                                    }
                                    className="h-10 w-full rounded-lg border border-zinc-300 bg-white px-3 text-sm text-zinc-950 transition outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                                />
                            </Field>
                            <Field
                                label="Ringkasan"
                                error={form.errors.summary}
                            >
                                <textarea
                                    rows={3}
                                    value={form.data.summary}
                                    onChange={(event) =>
                                        form.setData(
                                            'summary',
                                            event.target.value,
                                        )
                                    }
                                    className="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-950 transition outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                                />
                            </Field>
                            <Field
                                label="Deskripsi peran"
                                error={form.errors.description}
                            >
                                <textarea
                                    rows={3}
                                    value={form.data.description}
                                    onChange={(event) =>
                                        form.setData(
                                            'description',
                                            event.target.value,
                                        )
                                    }
                                    className="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-950 transition outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                                />
                            </Field>
                            <Field
                                label="Urutan tampil"
                                error={form.errors.sort_order}
                            >
                                <input
                                    type="number"
                                    min={0}
                                    value={form.data.sort_order}
                                    onChange={(event) =>
                                        form.setData(
                                            'sort_order',
                                            Number(event.target.value),
                                        )
                                    }
                                    className="h-10 w-full rounded-lg border border-zinc-300 bg-white px-3 text-sm text-zinc-950 transition outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                                />
                            </Field>
                            <Field
                                label="Catatan sumber"
                                error={form.errors.source_note}
                            >
                                <input
                                    value={form.data.source_note}
                                    onChange={(event) =>
                                        form.setData(
                                            'source_note',
                                            event.target.value,
                                        )
                                    }
                                    className="h-10 w-full rounded-lg border border-zinc-300 bg-white px-3 text-sm text-zinc-950 transition outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                                />
                            </Field>
                        </div>
                        <div className="mt-4 flex flex-wrap gap-6">
                            <label className="flex items-center gap-2 text-sm font-medium text-zinc-950">
                                <input
                                    type="checkbox"
                                    checked={form.data.is_active}
                                    onChange={(event) =>
                                        form.setData(
                                            'is_active',
                                            event.target.checked,
                                        )
                                    }
                                    className="size-4 rounded border-zinc-300 text-brand focus:ring-brand"
                                />
                                Aktif
                            </label>
                            <label className="flex items-center gap-2 text-sm font-medium text-zinc-950">
                                <input
                                    type="checkbox"
                                    checked={form.data.is_validated}
                                    onChange={(event) =>
                                        form.setData(
                                            'is_validated',
                                            event.target.checked,
                                        )
                                    }
                                    className="size-4 rounded border-zinc-300 text-brand focus:ring-brand"
                                />
                                Tervalidasi
                            </label>
                        </div>
                    </section>

                    <section className="rounded-xl border border-zinc-200/80 bg-white p-4 shadow-[0_1px_2px_rgba(0,0,0,0.03),0_1px_3px_rgba(0,0,0,0.02)] sm:p-6">
                        <h2 className="text-base font-semibold tracking-[-0.015em] text-zinc-950">
                            B. Skill, Tools, dan Roadmap
                        </h2>
                        <div className="mt-4 grid gap-6 lg:grid-cols-3">
                            <ListEditor
                                title="Skills"
                                items={form.data.skills}
                                error={form.errors['skills']}
                                onChange={(index, value) =>
                                    updateListItem('skills', index, value)
                                }
                                onAdd={() => addListItem('skills')}
                                onRemove={(index) =>
                                    removeListItem('skills', index)
                                }
                            />
                            <ListEditor
                                title="Tools"
                                items={form.data.tools}
                                error={form.errors['tools']}
                                onChange={(index, value) =>
                                    updateListItem('tools', index, value)
                                }
                                onAdd={() => addListItem('tools')}
                                onRemove={(index) =>
                                    removeListItem('tools', index)
                                }
                            />
                            <ListEditor
                                title="Roadmap Belajar"
                                items={form.data.roadmap}
                                error={form.errors['roadmap']}
                                onChange={(index, value) =>
                                    updateListItem('roadmap', index, value)
                                }
                                onAdd={() => addListItem('roadmap')}
                                onRemove={(index) =>
                                    removeListItem('roadmap', index)
                                }
                            />
                        </div>
                    </section>

                    <section className="rounded-xl border border-zinc-200/80 bg-white p-4 shadow-[0_1px_2px_rgba(0,0,0,0.03),0_1px_3px_rgba(0,0,0,0.02)] sm:p-6">
                        <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 className="text-base font-semibold tracking-[-0.015em] text-zinc-950">
                                    C. Editor Matriks Profil Karier
                                </h2>
                                <p className="mt-1 text-sm leading-relaxed text-zinc-600">
                                    Pilih skor 1-5 untuk setiap kriteria aktif.
                                </p>
                            </div>
                            <span
                                className={`rounded-full px-3 py-1.5 text-sm font-semibold tabular-nums ${totalScore === standard_total_score ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'}`}
                            >
                                Total Skor: {totalScore} / {criteria.length * 5}
                            </span>
                        </div>
                        {totalScore !== standard_total_score ? (
                            <p className="mt-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-2 text-sm text-amber-900">
                                Total skor menyimpang dari standar keseimbangan
                                matriks ({standard_total_score}). Periksa
                                kembali distribusi skor.
                            </p>
                        ) : null}
                        {form.errors['profiles'] ? (
                            <p className="mt-3 text-sm text-red-600">
                                {form.errors['profiles']}
                            </p>
                        ) : null}
                        <div className="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            {criteria.map((criterion) => (
                                <div
                                    key={criterion.id}
                                    className="rounded-lg border border-zinc-200/80 bg-zinc-50/60 p-3 transition-colors hover:border-zinc-300"
                                >
                                    <p className="text-sm font-medium text-zinc-950">
                                        {criterion.name}
                                    </p>
                                    <p className="mt-0.5 text-xs text-zinc-500">
                                        Langkah {criterion.step_group} |{' '}
                                        {criterion.code}
                                    </p>
                                    <div className="mt-2 flex gap-1.5">
                                        {[1, 2, 3, 4, 5].map((score) => (
                                            <button
                                                key={score}
                                                type="button"
                                                onClick={() =>
                                                    updateProfile(
                                                        criterion.id,
                                                        score,
                                                    )
                                                }
                                                className={`h-9 flex-1 rounded-md border text-sm font-medium tabular-nums transition-all duration-150 focus-visible:ring-2 focus-visible:ring-indigo-500/40 focus-visible:outline-none ${
                                                    Number(
                                                        form.data.profiles[
                                                            String(criterion.id)
                                                        ],
                                                    ) === score
                                                        ? 'border-indigo-600/30 bg-indigo-600 text-white shadow-sm'
                                                        : 'border-zinc-300 bg-white text-zinc-700 hover:border-zinc-400 hover:text-zinc-950'
                                                }`}
                                            >
                                                {score}
                                            </button>
                                        ))}
                                    </div>
                                </div>
                            ))}
                        </div>
                    </section>

                    <div className="flex flex-wrap gap-3">
                        <Button type="submit" disabled={form.processing}>
                            {form.processing
                                ? 'Menyimpan...'
                                : career
                                  ? 'Simpan Perubahan'
                                  : 'Buat Karier'}
                        </Button>
                        <Button asChild variant="outline">
                            <Link href="/admin/careers">Batal</Link>
                        </Button>
                    </div>
                </form>
            </AdminLayout>
        </>
    );
}

function Field({
    label,
    error,
    children,
}: {
    label: string;
    error?: string;
    children: ReactNode;
}) {
    return (
        <label className="block">
            <span className="text-sm font-medium text-zinc-950">{label}</span>
            <div className="mt-1">{children}</div>
            {error ? (
                <p className="mt-1 text-xs text-red-600">{error}</p>
            ) : null}
        </label>
    );
}

function ListEditor({
    title,
    items,
    error,
    onChange,
    onAdd,
    onRemove,
}: {
    title: string;
    items: string[];
    error?: string;
    onChange: (index: number, value: string) => void;
    onAdd: () => void;
    onRemove: (index: number) => void;
}) {
    return (
        <div>
            <div className="flex items-center justify-between">
                <p className="text-sm font-medium text-zinc-950">{title}</p>
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    onClick={onAdd}
                >
                    Tambah
                </Button>
            </div>
            <div className="mt-2 space-y-2">
                {items.length === 0 ? (
                    <p className="text-xs text-zinc-500">Belum ada item.</p>
                ) : (
                    items.map((item, index) => (
                        <div key={`${title}-${index}`} className="flex gap-2">
                            <input
                                value={item}
                                onChange={(event) =>
                                    onChange(index, event.target.value)
                                }
                                className="h-9 w-full rounded-lg border border-zinc-300 bg-white px-3 text-sm text-zinc-950 transition outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                            />
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                onClick={() => onRemove(index)}
                            >
                                Hapus
                            </Button>
                        </div>
                    ))
                )}
            </div>
            {error ? (
                <p className="mt-1 text-xs text-red-600">{error}</p>
            ) : null}
        </div>
    );
}
