import { Head, useForm } from '@inertiajs/react';
import { submit as submitRoute } from '@/routes/tests';
import { useState } from 'react';
import PublicLayout from '@/layouts/public-layout';
import { Button } from '@/components/ui/button';

type Criterion = {
    code: string;
    name: string;
    description: string;
    step_group: number;
    display_order: number;
};

type Props = {
    session: {
        token: string;
        visitor_name: string | null;
        status: string;
    };
    criteria: Criterion[];
};

const stepTitles = ['Cara Berpikir', 'Kreasi & Interaksi', 'Teknis & Bisnis'];

export default function TestShow({ session, criteria }: Props) {
    const [step, setStep] = useState(0);
    const { data, setData, post, processing, errors } = useForm({
        ratings: {} as Record<string, number>,
    });

    const steps = [1, 2, 3].map((group) => ({
        title: stepTitles[group - 1],
        criteria: criteria
            .filter((criterion) => criterion.step_group === group)
            .sort((a, b) => a.display_order - b.display_order),
    }));

    const current = steps[step];
    const currentComplete = current.criteria.every(
        (criterion) => (data.ratings[criterion.code] ?? 0) >= 1,
    );

    function setRating(code: string, value: number) {
        setData('ratings', { ...data.ratings, [code]: value });
    }

    function submit() {
        post(submitRoute.url({ token: session.token }));
    }

    return (
        <>
            <Head title="Tes Minat Karier" />
            <PublicLayout>
                <div className="mx-auto max-w-2xl">
                    <div className="mb-6">
                        <p className="text-sm font-medium text-brand">
                            Langkah {step + 1} dari {steps.length}
                        </p>
                        <div className="mt-2 h-2 rounded-full bg-zinc-200">
                            <div
                                className="h-2 rounded-full bg-brand transition-all"
                                style={{ width: `${((step + 1) / steps.length) * 100}%` }}
                            />
                        </div>
                    </div>

                    <h1 className="text-2xl font-semibold tracking-tight text-zinc-950">
                        {current.title}
                    </h1>
                    <p className="mt-2 text-sm text-zinc-600">
                        Pilih angka 1 sampai 5 untuk setiap pernyataan.
                    </p>

                    <div className="mt-6 space-y-4">
                        {current.criteria.map((criterion) => (
                            <div
                                key={criterion.code}
                                className="rounded-xl border border-zinc-200 bg-white p-4"
                            >
                                <p className="text-sm font-medium text-zinc-950">
                                    {criterion.name}
                                </p>
                                <p className="mt-1 text-sm text-zinc-600">
                                    {criterion.description}
                                </p>
                                <div className="mt-3 grid grid-cols-5 gap-2">
                                    {[1, 2, 3, 4, 5].map((value) => (
                                        <button
                                            key={value}
                                            type="button"
                                            onClick={() => setRating(criterion.code, value)}
                                            className={`h-10 rounded-lg border text-sm font-medium transition-colors ${
                                                data.ratings[criterion.code] === value
                                                    ? 'border-brand bg-brand text-white'
                                                    : 'border-zinc-300 text-zinc-700 hover:border-zinc-400'
                                            }`}
                                            aria-pressed={data.ratings[criterion.code] === value}
                                            aria-label={`${criterion.name}: ${value}`}
                                        >
                                            {value}
                                        </button>
                                    ))}
                                </div>
                            </div>
                        ))}
                    </div>

                    {errors.ratings ? (
                        <p className="mt-4 text-sm text-red-600">{errors.ratings}</p>
                    ) : null}

                    <div className="mt-8 flex items-center justify-between gap-3">
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => setStep((value) => Math.max(0, value - 1))}
                            disabled={step === 0 || processing}
                        >
                            Kembali
                        </Button>
                        {step < steps.length - 1 ? (
                            <Button
                                type="button"
                                onClick={() => setStep((value) => value + 1)}
                                disabled={!currentComplete || processing}
                            >
                                Lanjut
                            </Button>
                        ) : (
                            <Button
                                type="button"
                                onClick={submit}
                                disabled={!currentComplete || processing}
                            >
                                {processing ? 'Memproses...' : 'Lihat Hasil'}
                            </Button>
                        )}
                    </div>
                </div>
            </PublicLayout>
        </>
    );
}
