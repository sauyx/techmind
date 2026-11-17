import { Head, useForm } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import { store } from '@/routes/tests';
import PublicLayout from '@/layouts/public-layout';
import { Button } from '@/components/ui/button';

type Criterion = {
    code: string;
    name: string;
    description: string;
    step_group: number;
    display_order: number;
};

type Props = { criteria: Criterion[] };

const stepTitles = ['Cara Berpikir', 'Kreasi & Interaksi', 'Teknis & Bisnis'];
const scaleLabels = [
    'Sangat Tidak Sesuai',
    'Kurang Sesuai',
    'Cukup Sesuai',
    'Sesuai',
    'Sangat Sesuai',
];

export default function PublicTest({ criteria }: Props) {
    const [step, setStep] = useState(0);
    const { data, setData, post, processing, errors } = useForm<{
        visitor_name: string;
        ratings: Record<string, number>;
    }>({
        visitor_name: '',
        ratings: {},
    });

    const steps = useMemo(
        () =>
            [1, 2, 3].map((group) => ({
                title: stepTitles[group - 1],
                criteria: criteria
                    .filter((criterion) => criterion.step_group === group)
                    .sort((a, b) => a.display_order - b.display_order),
            })),
        [criteria],
    );

    const current = steps[step];
    const currentComplete = current.criteria.every(
        (criterion) => data.ratings[criterion.code] !== undefined,
    );
    const lowVariance = Object.keys(data.ratings).length === criteria.length && new Set(Object.values(data.ratings)).size < 2;

    function setRating(code: string, value: number) {
        setData('ratings', { ...data.ratings, [code]: value });
    }

    function submit() {
        post(store.url());
    }

    return (
        <>
            <Head title="Mulai Tes Karier" />
            <PublicLayout>
                <div className="mx-auto max-w-2xl">
                    <p className="text-sm font-medium text-brand">TechMind</p>
                    <h1 className="mt-2 text-3xl font-semibold tracking-tight text-zinc-950">
                        Kenali kecenderunganmu
                    </h1>
                    <p className="mt-2 text-sm leading-6 text-zinc-600">
                        Jawab setiap pernyataan sesuai dengan dirimu. Tidak ada jawaban benar atau salah.
                    </p>

                    <div className="mt-6 rounded-xl border border-zinc-200 bg-white p-4">
                        <label className="text-sm font-medium text-zinc-950" htmlFor="visitor_name">
                            Nama Panggilan
                        </label>
                        <input
                            id="visitor_name"
                            type="text"
                            value={data.visitor_name}
                            onChange={(event) => setData('visitor_name', event.target.value)}
                            placeholder="Misal: Budi (opsional)"
                            className="mt-2 h-11 w-full rounded-lg border border-zinc-300 px-3 text-sm text-zinc-950 outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/20"
                        />
                    </div>

                    <div className="mt-8">
                        <div className="flex items-center justify-between text-sm">
                            <span className="font-medium text-zinc-950">Langkah {step + 1} dari 3</span>
                            <span className="text-zinc-500">{current.title}</span>
                        </div>
                        <div className="mt-2 h-2 rounded-full bg-zinc-200">
                            <div className="h-2 rounded-full bg-brand transition-all" style={{ width: `${((step + 1) / 3) * 100}%` }} />
                        </div>
                    </div>

                    <div className="mt-6 space-y-4">
                        {current.criteria.map((criterion) => (
                            <article key={criterion.code} className="rounded-xl border border-zinc-200 bg-white p-4">
                                <h2 className="text-sm font-semibold text-zinc-950">{criterion.name}</h2>
                                <p className="mt-1 text-sm leading-6 text-zinc-600">{criterion.description}</p>
                                <div className="mt-4 grid grid-cols-5 gap-2">
                                    {scaleLabels.map((label, index) => {
                                        const value = index + 1;
                                        const selected = data.ratings[criterion.code] === value;
                                        return (
                                            <button
                                                key={value}
                                                type="button"
                                                onClick={() => setRating(criterion.code, value)}
                                                aria-pressed={selected}
                                                aria-label={`${criterion.name}: ${value} - ${label}`}
                                                className={`min-h-11 rounded-lg border px-1 text-sm font-semibold transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2 ${selected ? 'border-brand bg-brand text-white' : 'border-zinc-300 text-zinc-700 hover:border-zinc-400'}`}
                                            >
                                                {value}
                                            </button>
                                        );
                                    })}
                                </div>
                                <div className="mt-2 flex justify-between text-[11px] text-zinc-500">
                                    <span>1 — Sangat Tidak Sesuai</span>
                                    <span>5 — Sangat Sesuai</span>
                                </div>
                            </article>
                        ))}
                    </div>

                    {lowVariance && step === 2 ? (
                        <div className="mt-5 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-900" role="alert">
                            Semua jawabanmu memiliki nilai yang sama. Coba bedakan minimal beberapa kriteria yang paling menonjol agar hasil rekomendasi lebih akurat.
                        </div>
                    ) : null}

                    {errors.ratings ? <p className="mt-4 text-sm text-red-600">{errors.ratings}</p> : null}

                    <div className="mt-8 flex items-center justify-between gap-3">
                        <Button type="button" variant="outline" onClick={() => setStep((value) => Math.max(0, value - 1))} disabled={step === 0 || processing}>
                            Kembali
                        </Button>
                        {step < 2 ? (
                            <Button type="button" onClick={() => setStep((value) => value + 1)} disabled={!currentComplete || processing}>
                                Lanjut
                            </Button>
                        ) : (
                            <Button type="button" onClick={submit} disabled={!currentComplete || lowVariance || processing}>
                                {processing ? 'Memproses...' : 'Lihat Hasil Analisis'}
                            </Button>
                        )}
                    </div>
                </div>
            </PublicLayout>
        </>
    );
}
