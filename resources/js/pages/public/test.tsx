import { Head, useForm, usePage } from '@inertiajs/react';
import { AlertTriangle } from 'lucide-react';
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

const demoNames = ['Alya', 'Bagas', 'Citra'];

const demoPatterns: Record<string, number>[] = [
    {
        logic_problem_solving: 5,
        data_analysis: 4,
        math_statistics: 3,
        attention_detail: 4,
        visual_design: 2,
        creativity: 3,
        user_empathy: 2,
        communication: 3,
        coding: 5,
        network_infra: 4,
        security_awareness: 3,
        business_process: 2,
    },
    {
        logic_problem_solving: 4,
        data_analysis: 5,
        math_statistics: 5,
        attention_detail: 5,
        visual_design: 2,
        creativity: 2,
        user_empathy: 2,
        communication: 3,
        coding: 3,
        network_infra: 3,
        security_awareness: 4,
        business_process: 4,
    },
    {
        logic_problem_solving: 3,
        data_analysis: 3,
        math_statistics: 2,
        attention_detail: 3,
        visual_design: 5,
        creativity: 5,
        user_empathy: 5,
        communication: 5,
        coding: 3,
        network_infra: 1,
        security_awareness: 2,
        business_process: 4,
    },
];

export default function PublicTest({ criteria }: Props) {
    const { demo_mode } = usePage().props as { demo_mode?: boolean };
    const [demoIndex, setDemoIndex] = useState(0);
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
    const answeredCount = Object.keys(data.ratings).length;
    const progressPercent =
        criteria.length > 0
            ? Math.round((answeredCount / criteria.length) * 100)
            : 0;
    const lowVariance =
        answeredCount === criteria.length &&
        new Set(Object.values(data.ratings)).size < 2;

    function setRating(code: string, value: number) {
        setData('ratings', { ...data.ratings, [code]: value });
    }

    function submit() {
        post(store.url());
    }

    function fillDemo() {
        const pattern = demoPatterns[demoIndex % demoPatterns.length];
        setDemoIndex((value) => value + 1);
        setData('ratings', { ...pattern });

        if (!data.visitor_name) {
            setData('visitor_name', demoNames[demoIndex % demoNames.length]);
        }
    }

    return (
        <>
            <Head title="Mulai Tes Karier" />
            <PublicLayout>
                <div className="mx-auto max-w-2xl">
                    <p className="text-[11px] font-semibold tracking-[0.08em] text-brand uppercase">
                        Kuesioner TechMind
                    </p>
                    <h1 className="mt-2 text-3xl font-semibold tracking-[-0.025em] text-zinc-950">
                        Kenali kecenderunganmu
                    </h1>
                    <p className="mt-2 text-sm leading-relaxed text-zinc-600">
                        Jawab setiap pernyataan sesuai dengan dirimu. Tidak ada
                        jawaban benar atau salah.
                    </p>

                    {demo_mode && criteria.length > 0 ? (
                        <div className="mt-5 flex flex-col gap-3 rounded-xl border border-indigo-600/15 bg-gradient-to-b from-indigo-50/60 to-white px-4 py-3 shadow-[0_1px_2px_rgba(0,0,0,0.03)] sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p className="text-sm font-semibold text-zinc-950">
                                    Mode Demo Pameran
                                </p>
                                <p className="mt-0.5 text-xs leading-5 text-zinc-600">
                                    Isi 12 kriteria dengan pola persona contoh,
                                    lalu lanjutkan seperti biasa.
                                </p>
                            </div>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={fillDemo}
                                className="shrink-0"
                            >
                                Isi Simulasi Cepat (Demo)
                            </Button>
                        </div>
                    ) : null}

                    {lowVariance ? (
                        <div
                            role="alert"
                            className="mt-3 flex items-start gap-3 rounded-xl border border-amber-300/70 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-900"
                        >
                            <AlertTriangle
                                className="mt-0.5 size-4 shrink-0 text-amber-600"
                                aria-hidden="true"
                            />
                            <p>
                                Semua jawabanmu memiliki nilai yang sama. Coba
                                bedakan minimal beberapa kriteria yang paling
                                menonjol agar hasil rekomendasi lebih akurat.
                            </p>
                        </div>
                    ) : null}

                    {criteria.length === 0 ? (
                        <div
                            role="alert"
                            className="mt-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-900"
                        >
                            Kriteria tes belum tersedia. Hubungi admin TechMind
                            agar kuesioner dapat diaktifkan.
                        </div>
                    ) : (
                        <>
                            <div className="mt-6 rounded-xl border border-zinc-200/80 bg-white p-4 shadow-[0_1px_2px_rgba(0,0,0,0.03),0_1px_3px_rgba(0,0,0,0.02)] transition-all duration-200 hover:border-zinc-300 sm:p-5">
                                <label
                                    className="text-sm font-medium text-zinc-950"
                                    htmlFor="visitor_name"
                                >
                                    Nama Panggilan
                                </label>
                                <input
                                    id="visitor_name"
                                    type="text"
                                    value={data.visitor_name}
                                    onChange={(event) =>
                                        setData(
                                            'visitor_name',
                                            event.target.value,
                                        )
                                    }
                                    placeholder="Misal: Budi (opsional)"
                                    className="mt-2 h-11 w-full rounded-lg border border-zinc-300 bg-white px-3 text-sm text-zinc-950 transition outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                                />
                            </div>

                            <div className="sticky top-16 z-20 mt-8 rounded-xl border border-zinc-200/80 bg-white/90 p-4 shadow-[0_1px_2px_rgba(0,0,0,0.03)] backdrop-blur-md">
                                <div className="flex items-center justify-between gap-3 text-sm">
                                    <div className="flex min-w-0 items-center gap-2.5">
                                        <span className="rounded-md bg-indigo-50 px-2 py-1 text-[11px] font-semibold text-indigo-700 tabular-nums ring-1 ring-indigo-600/15 ring-inset">
                                            Langkah {step + 1} dari 3
                                        </span>
                                        <span className="truncate font-medium text-zinc-950">
                                            {current.title}
                                        </span>
                                    </div>
                                    <span className="shrink-0 text-xs font-medium text-zinc-500 tabular-nums">
                                        {answeredCount}/{criteria.length}{' '}
                                        terjawab
                                    </span>
                                </div>
                                <div className="mt-2.5 h-1.5 overflow-hidden rounded-full bg-zinc-200/80">
                                    <div
                                        className="h-1.5 rounded-full bg-indigo-600 transition-all duration-300"
                                        style={{ width: `${progressPercent}%` }}
                                    />
                                </div>
                            </div>

                            <div className="mt-6 space-y-4">
                                {current.criteria.map((criterion) => {
                                    const globalIndex = criteria.findIndex(
                                        (item) => item.code === criterion.code,
                                    );
                                    const selected =
                                        data.ratings[criterion.code];
                                    const answered = selected !== undefined;

                                    return (
                                        <article
                                            key={criterion.code}
                                            className={`rounded-xl border bg-white p-4 shadow-[0_1px_2px_rgba(0,0,0,0.03),0_1px_3px_rgba(0,0,0,0.02)] transition-all duration-200 sm:p-5 ${
                                                answered
                                                    ? 'border-zinc-300 ring-1 ring-indigo-600/10'
                                                    : 'border-zinc-200/80 hover:border-zinc-300'
                                            }`}
                                        >
                                            <div className="flex items-start justify-between gap-3">
                                                <div className="flex min-w-0 items-center gap-3">
                                                    <span className="text-sm font-semibold text-zinc-400 tabular-nums">
                                                        {String(
                                                            globalIndex + 1,
                                                        ).padStart(2, '0')}
                                                    </span>
                                                    <h2 className="truncate text-sm font-semibold tracking-[-0.015em] text-zinc-950">
                                                        {criterion.name}
                                                    </h2>
                                                </div>
                                                {answered ? (
                                                    <span className="inline-flex shrink-0 items-center gap-1.5 rounded-full border border-indigo-600/15 bg-indigo-50/70 px-2 py-0.5 text-[11px] font-medium text-indigo-700">
                                                        <span className="size-1.5 rounded-full bg-indigo-600" />
                                                        Terjawab
                                                    </span>
                                                ) : null}
                                            </div>
                                            <p className="mt-2 text-sm leading-relaxed text-zinc-600">
                                                {criterion.description}
                                            </p>
                                            <div className="mt-4 grid grid-cols-5 gap-1 rounded-xl border border-zinc-200/80 bg-zinc-50/60 p-1">
                                                {scaleLabels.map(
                                                    (label, index) => {
                                                        const value = index + 1;
                                                        const active =
                                                            selected === value;

                                                        return (
                                                            <button
                                                                key={value}
                                                                type="button"
                                                                onClick={() =>
                                                                    setRating(
                                                                        criterion.code,
                                                                        value,
                                                                    )
                                                                }
                                                                aria-pressed={
                                                                    active
                                                                }
                                                                aria-label={`${criterion.name}: ${value} - ${label}`}
                                                                className={`flex min-h-11 flex-col items-center justify-center rounded-lg text-base font-semibold tabular-nums transition-all duration-150 focus-visible:ring-2 focus-visible:ring-indigo-500/40 focus-visible:outline-none focus-visible:ring-inset ${
                                                                    active
                                                                        ? 'bg-gradient-to-b from-indigo-50/80 to-white text-indigo-700 shadow-sm ring-1 ring-indigo-600/20 ring-inset'
                                                                        : 'border border-transparent text-zinc-600 hover:border-zinc-200/80 hover:bg-white hover:text-zinc-950'
                                                                }`}
                                                            >
                                                                {value}
                                                            </button>
                                                        );
                                                    },
                                                )}
                                            </div>
                                            <div className="mt-2 flex items-center justify-between gap-3 text-[11px] text-zinc-500">
                                                <span>
                                                    1 — Sangat Tidak Sesuai
                                                </span>
                                                {answered ? (
                                                    <span className="text-center font-medium text-indigo-700">
                                                        {
                                                            scaleLabels[
                                                                selected - 1
                                                            ]
                                                        }
                                                    </span>
                                                ) : (
                                                    <span className="hidden text-zinc-400 sm:inline">
                                                        Pilih 1–5
                                                    </span>
                                                )}
                                                <span>5 — Sangat Sesuai</span>
                                            </div>
                                        </article>
                                    );
                                })}
                            </div>

                            {errors.ratings ? (
                                <p className="mt-4 text-sm text-red-600">
                                    {errors.ratings}
                                </p>
                            ) : null}

                            <div className="mt-8 flex items-center justify-between gap-3">
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={() =>
                                        setStep((value) =>
                                            Math.max(0, value - 1),
                                        )
                                    }
                                    disabled={step === 0 || processing}
                                >
                                    Kembali
                                </Button>
                                {step < 2 ? (
                                    <Button
                                        type="button"
                                        onClick={() =>
                                            setStep((value) => value + 1)
                                        }
                                        disabled={
                                            !currentComplete || processing
                                        }
                                    >
                                        Lanjut
                                    </Button>
                                ) : (
                                    <Button
                                        type="button"
                                        onClick={submit}
                                        disabled={
                                            !currentComplete ||
                                            lowVariance ||
                                            processing
                                        }
                                    >
                                        {processing
                                            ? 'Memproses...'
                                            : 'Lihat Hasil Analisis'}
                                    </Button>
                                )}
                            </div>
                        </>
                    )}
                </div>
            </PublicLayout>
        </>
    );
}
