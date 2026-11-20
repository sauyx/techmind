import { Deferred, Head, Link, useForm } from '@inertiajs/react';
import { Check, Copy, Download, RefreshCw } from 'lucide-react';
import { useState, type FormEvent } from 'react';
import {
    PolarAngleAxis,
    PolarGrid,
    PolarRadiusAxis,
    Radar,
    RadarChart,
    ResponsiveContainer,
    Tooltip,
} from 'recharts';
import PublicLayout from '@/layouts/public-layout';
import { Button } from '@/components/ui/button';
import { formatPercent } from '@/lib/format';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

type Criterion = { code: string; name: string; short_label: string };

type ResultItem = {
    id: number;
    rank: number;
    rank_label: string;
    is_tied: boolean;
    career_code: string;
    career_name: string;
    saw_score: number;
    match_percentage: string;
    contribution_breakdown: Record<string, number>;
    gap_summary: {
        strengths: {
            criterion_code: string;
            rating: number;
            profile: number;
            sentence: string;
        }[];
        needs_strengthening: {
            criterion_code: string;
            rating: number;
            profile: number;
            sentence: string;
        }[];
        underused: {
            criterion_code: string;
            rating: number;
            profile: number;
            sentence: string;
        }[];
        narrative_strengths: string[];
        narrative_needs: string[];
        narrative_underused: string[];
    } | null;
};

type CareerDetail = {
    code: string;
    name: string;
    summary: string | null;
    description: string | null;
    skills: string[] | null;
    tools: string[] | null;
    roadmap: string[] | null;
    is_validated: boolean;
    source_note: string | null;
    profiles: Record<string, number>;
};

type AiExplanation = {
    content: string;
    format: 'text' | 'json';
    driver_used: string;
    is_fallback: boolean;
    source_label: string;
};

type VisitorFeedback = {
    score: number;
    comment: string | null;
    suggestion: string | null;
};

type Props = {
    session: {
        token: string;
        visitor_name: string | null;
        completed_at: string | null;
    };
    results: ResultItem[];
    ratings: Record<string, number>;
    criteria: Criterion[];
    careers: CareerDetail[];
    ai_explanation?: AiExplanation | null;
    visitor_feedback: VisitorFeedback | null;
};

type DetailTab = 'analysis' | 'description' | 'skills' | 'roadmap';

const detailTabs: { id: DetailTab; label: string }[] = [
    { id: 'analysis', label: 'Analisis Kecocokan' },
    { id: 'description', label: 'Deskripsi & Peran' },
    { id: 'skills', label: 'Skill & Tools' },
    { id: 'roadmap', label: 'Roadmap Belajar' },
];

function GaugeSegmented({
    value,
    segments = 24,
}: {
    value: number;
    segments?: number;
}) {
    const filled = Math.round((value / 100) * segments);

    return (
        <div
            className="flex w-40 gap-0.5"
            role="img"
            aria-label={`Skor kecocokan ${value}%`}
        >
            {Array.from({ length: segments }).map((_, index) => (
                <span
                    key={index}
                    className={`h-2 flex-1 rounded-full ${index < filled ? 'bg-indigo-600' : 'bg-zinc-200'}`}
                />
            ))}
        </div>
    );
}

function RadarTooltip({
    active,
    payload,
    label,
}: {
    active?: boolean;
    payload?: Array<{ name?: string; value?: number; color?: string }>;
    label?: string;
}) {
    if (!active || !payload || payload.length === 0) {
        return null;
    }

    return (
        <div className="rounded-lg border border-zinc-200/80 bg-white px-3 py-2 shadow-[0_1px_2px_rgba(0,0,0,0.05),0_8px_24px_-16px_rgba(24,24,27,0.25)]">
            <p className="text-xs font-semibold text-zinc-950">{label}</p>
            <div className="mt-1.5 space-y-1">
                {payload.map((entry) => (
                    <p
                        key={entry.name}
                        className="flex items-center gap-2 text-xs text-zinc-600"
                    >
                        <span
                            className="size-2 rounded-full"
                            style={{ backgroundColor: entry.color }}
                        />
                        <span>{entry.name}</span>
                        <span className="ml-auto pl-3 font-semibold text-zinc-950 tabular-nums">
                            {entry.value}
                        </span>
                    </p>
                ))}
            </div>
        </div>
    );
}

export default function ResultPage({
    session,
    results,
    ratings,
    criteria,
    careers,
    ai_explanation,
    visitor_feedback,
}: Props) {
    const topResult = results[0];
    const [selectedCode, setSelectedCode] = useState(
        topResult?.career_code ?? '',
    );
    const [copied, setCopied] = useState(false);
    const [activeTab, setActiveTab] = useState<DetailTab>('analysis');
    const feedbackForm = useForm({
        score: visitor_feedback?.score ?? 85,
        comment: visitor_feedback?.comment ?? '',
        suggestion: visitor_feedback?.suggestion ?? '',
    });
    const [feedbackSubmitted, setFeedbackSubmitted] = useState(
        visitor_feedback !== null,
    );

    function submitFeedback(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        feedbackForm.post(`/hasil/${session.token}/feedback`, {
            preserveScroll: true,
            onSuccess: () => setFeedbackSubmitted(true),
        });
    }

    const selectedResult =
        results.find((item) => item.career_code === selectedCode) ?? topResult;
    const selectedCareer =
        careers.find((career) => career.code === selectedCode) ?? careers[0];

    const radarData = criteria.map((criterion) => ({
        criterion: criterion.short_label,
        Anda: ratings[criterion.code] ?? 0,
        Karier: selectedCareer?.profiles?.[criterion.code] ?? 0,
    }));

    const topCareer = careers.find(
        (career) => career.code === topResult?.career_code,
    );

    const topTags = (topResult?.gap_summary?.strengths ?? [])
        .slice(0, 3)
        .map(
            (item) =>
                criteria.find(
                    (criterion) => criterion.code === item.criterion_code,
                )?.name ?? item.criterion_code,
        );

    async function copyLink() {
        try {
            await navigator.clipboard.writeText(window.location.href);
            setCopied(true);
            window.setTimeout(() => setCopied(false), 2000);
        } catch {
            setCopied(false);
        }
    }

    const gap = selectedResult?.gap_summary;

    const averageUser =
        criteria.length > 0
            ? criteria.reduce(
                  (sum, criterion) => sum + (ratings[criterion.code] ?? 0),
                  0,
              ) / criteria.length
            : 0;
    const averageIdeal =
        criteria.length > 0
            ? criteria.reduce(
                  (sum, criterion) =>
                      sum + (selectedCareer?.profiles?.[criterion.code] ?? 0),
                  0,
              ) / criteria.length
            : 0;

    function criterionName(code: string) {
        return (
            criteria.find((criterion) => criterion.code === code)?.name ?? code
        );
    }

    return (
        <>
            <Head title="Hasil Analisis" />
            <PublicLayout>
                <div className="mx-auto max-w-6xl space-y-6">
                    <section className="flex flex-col gap-4">
                        <div>
                            <p className="text-[11px] font-semibold tracking-[0.08em] text-brand uppercase">
                                Hasil analisis
                            </p>
                            <h1 className="mt-2 text-3xl font-semibold tracking-[-0.025em] text-zinc-950">
                                {session.visitor_name
                                    ? `Hasil Analisis Karier untuk ${session.visitor_name}`
                                    : 'Hasil Analisis Karier TechMind'}
                            </h1>
                        </div>
                        <div className="flex flex-wrap gap-2.5">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={copyLink}
                                className="gap-1.5"
                            >
                                {copied ? (
                                    <Check className="size-4" />
                                ) : (
                                    <Copy className="size-4" />
                                )}
                                {copied ? 'Link tersalin' : 'Salin Link Hasil'}
                            </Button>
                            <Button
                                asChild
                                variant="outline"
                                className="gap-1.5"
                            >
                                <Link href="/tes">
                                    <RefreshCw className="size-4" />
                                    Ulangi Tes
                                </Link>
                            </Button>
                            <Button asChild className="gap-1.5">
                                <a href={`/hasil/${session.token}/pdf`}>
                                    <Download className="size-4" />
                                    Unduh PDF
                                </a>
                            </Button>
                        </div>
                    </section>

                    {topResult ? (
                        <section className="relative overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-[0_1px_2px_rgba(0,0,0,0.03),0_1px_3px_rgba(0,0,0,0.02)]">
                            <div
                                aria-hidden="true"
                                className="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-indigo-500 via-indigo-600 to-indigo-500"
                            />
                            <div className="grid gap-6 p-6 sm:p-8 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center">
                                <div className="min-w-0">
                                    <span className="inline-flex items-center gap-1.5 rounded-full border border-indigo-600/15 bg-indigo-50/70 px-2.5 py-1 text-[11px] font-semibold tracking-[0.08em] text-indigo-700 uppercase">
                                        <span className="size-1.5 rounded-full bg-indigo-600" />
                                        Rekomendasi utama #1
                                    </span>
                                    <h2 className="mt-4 text-2xl font-semibold tracking-[-0.025em] text-zinc-950 sm:text-3xl">
                                        {topResult.career_name}
                                    </h2>
                                    {topResult.gap_summary
                                        ?.narrative_strengths?.[0] ? (
                                        <p className="mt-3 max-w-2xl text-sm leading-relaxed text-zinc-600">
                                            {
                                                topResult.gap_summary
                                                    .narrative_strengths[0]
                                            }
                                        </p>
                                    ) : null}
                                    {topCareer?.summary ? (
                                        <p className="mt-2 max-w-2xl text-sm leading-relaxed text-zinc-500">
                                            {topCareer.summary}
                                        </p>
                                    ) : null}
                                    {topTags.length > 0 ? (
                                        <div className="mt-4 flex flex-wrap gap-2">
                                            {topTags.map((tag) => (
                                                <span
                                                    key={tag}
                                                    className="inline-flex items-center rounded-full border border-indigo-600/15 bg-indigo-50/60 px-2.5 py-1 text-xs font-medium text-indigo-700"
                                                >
                                                    {tag}
                                                </span>
                                            ))}
                                        </div>
                                    ) : null}
                                </div>
                                <div className="flex shrink-0 items-center gap-6 rounded-xl border border-zinc-200/80 bg-zinc-50/60 p-5 lg:flex-col lg:items-end lg:gap-4 lg:text-right">
                                    <div>
                                        <p className="text-[11px] font-semibold tracking-[0.08em] text-zinc-500 uppercase">
                                            Skor kecocokan
                                        </p>
                                        <p className="mt-1 text-4xl font-semibold tracking-[-0.025em] text-zinc-950 tabular-nums">
                                            {topResult.match_percentage}
                                            <span className="text-lg text-zinc-500">
                                                %
                                            </span>
                                        </p>
                                        <p className="mt-1 text-xs text-zinc-500 tabular-nums">
                                            SAW{' '}
                                            {Number(
                                                topResult.saw_score,
                                            ).toFixed(4)}
                                        </p>
                                    </div>
                                    <GaugeSegmented
                                        value={parseFloat(
                                            topResult.match_percentage,
                                        )}
                                    />
                                </div>
                            </div>
                        </section>
                    ) : null}

                    {topResult?.is_tied ? (
                        <div
                            role="status"
                            className="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-900"
                        >
                            Beberapa karier memiliki tingkat kecocokan yang
                            sama karena jawaban Anda cenderung merata. Untuk
                            hasil yang lebih tajam, ulangi tes dan berikan
                            penilaian yang lebih tegas pada hal yang benar-benar
                            Anda minati atau tidak minati.
                        </div>
                    ) : null}

                    <div className="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_340px]">
                        <div className="min-w-0 space-y-6">
                            <section className="rounded-xl border border-zinc-200/80 bg-white p-5 shadow-[0_1px_2px_rgba(0,0,0,0.03),0_1px_3px_rgba(0,0,0,0.02)] sm:p-6">
                                <Deferred
                                    data="ai_explanation"
                                    fallback={
                                        <div className="space-y-3">
                                            <h2 className="text-base font-semibold tracking-[-0.015em] text-zinc-950">
                                                Ringkasan Analisis Personal
                                            </h2>
                                            <p className="text-sm text-zinc-500">
                                                Menyusun analisis untuk Anda...
                                            </p>
                                        </div>
                                    }
                                >
                                    {ai_explanation ? (
                                        <>
                                            <div className="flex flex-wrap items-center justify-between gap-3">
                                                <h2 className="text-base font-semibold tracking-[-0.015em] text-zinc-950">
                                                    Ringkasan Analisis Personal
                                                </h2>
                                                <span className="rounded-full border border-zinc-200/80 bg-zinc-50 px-2.5 py-1 text-[11px] font-medium text-zinc-600">
                                                    {ai_explanation.source_label}
                                                </span>
                                            </div>
                                            {(() => {
                                                let parsed: any = null;
                                                try {
                                                    parsed = JSON.parse(
                                                        ai_explanation.content,
                                                    );
                                                } catch {
                                                    parsed = null;
                                                }
                                                if (
                                                    parsed &&
                                                    typeof parsed ===
                                                        'object' &&
                                                    parsed.ringkasan
                                                ) {
                                                    return (
                                                        <div className="mt-4 space-y-4 text-sm text-zinc-700">
                                                            <div>
                                                                <p className="text-[11px] font-semibold tracking-[0.08em] text-brand uppercase">
                                                                    Ringkasan
                                                                </p>
                                                                <p className="mt-1 leading-7">
                                                                    {
                                                                        parsed.ringkasan
                                                                    }
                                                                </p>
                                                            </div>
                                                            {Array.isArray(
                                                                parsed.alasan,
                                                            ) &&
                                                            parsed.alasan
                                                                .length >
                                                                0 ? (
                                                                <div>
                                                                    <p className="text-[11px] font-semibold tracking-[0.08em] text-brand uppercase">
                                                                        Mengapa
                                                                        cocok
                                                                        untuk
                                                                        Anda
                                                                    </p>
                                                                    <ul className="mt-1 list-disc space-y-1 pl-5 leading-7">
                                                                        {parsed.alasan.map(
                                                                            (
                                                                                item: string,
                                                                                idx: number,
                                                                            ) => (
                                                                                <li
                                                                                    key={
                                                                                        idx
                                                                                    }
                                                                                >
                                                                                    {
                                                                                        item
                                                                                    }
                                                                                </li>
                                                                            ),
                                                                        )}
                                                                    </ul>
                                                                </div>
                                                            ) : null}
                                                            {Array.isArray(
                                                                parsed.area_pengembangan,
                                                            ) &&
                                                            parsed
                                                                .area_pengembangan
                                                                .length >
                                                                0 ? (
                                                                <div>
                                                                    <p className="text-[11px] font-semibold tracking-[0.08em] text-zinc-500 uppercase">
                                                                        Yang
                                                                        perlu
                                                                        dikembangkan
                                                                    </p>
                                                                    <ul className="mt-1 list-disc space-y-1 pl-5 leading-7">
                                                                        {parsed.area_pengembangan.map(
                                                                            (
                                                                                item: string,
                                                                                idx: number,
                                                                            ) => (
                                                                                <li
                                                                                    key={
                                                                                        idx
                                                                                    }
                                                                                >
                                                                                    {
                                                                                        item
                                                                                    }
                                                                                </li>
                                                                            ),
                                                                        )}
                                                                    </ul>
                                                                </div>
                                                            ) : null}
                                                            {Array.isArray(
                                                                parsed.langkah_awal,
                                                            ) &&
                                                            parsed.langkah_awal
                                                                .length >
                                                                0 ? (
                                                                <div>
                                                                    <p className="text-[11px] font-semibold tracking-[0.08em] text-brand uppercase">
                                                                        Langkah
                                                                        awal
                                                                        minggu
                                                                        ini
                                                                    </p>
                                                                    <ol className="mt-1 list-decimal space-y-1 pl-5 leading-7">
                                                                        {parsed.langkah_awal.map(
                                                                            (
                                                                                item: string,
                                                                                idx: number,
                                                                            ) => (
                                                                                <li
                                                                                    key={
                                                                                        idx
                                                                                    }
                                                                                >
                                                                                    {
                                                                                        item
                                                                                    }
                                                                                </li>
                                                                            ),
                                                                        )}
                                                                    </ol>
                                                                </div>
                                                            ) : null}
                                                            {parsed.alternatif ? (
                                                                <p className="text-sm leading-7 text-zinc-600">
                                                                    {
                                                                        parsed.alternatif
                                                                    }
                                                                </p>
                                                            ) : null}
                                                        </div>
                                                    );
                                                }
                                                return (
                                                    <p className="mt-4 text-sm leading-7 whitespace-pre-line text-zinc-700">
                                                        {
                                                            ai_explanation.content
                                                        }
                                                    </p>
                                                );
                                            })()}
                                            {ai_explanation.is_fallback ? (
                                                <p className="mt-4 rounded-lg border border-zinc-200/80 bg-zinc-50/80 px-3 py-2 text-xs leading-relaxed text-zinc-500">
                                                    Ringkasan disusun dari
                                                    template lokal karena layanan
                                                    AI tidak tersedia. Driver:{' '}
                                                    {ai_explanation.driver_used}.
                                                </p>
                                            ) : null}
                                        </>
                                    ) : null}
                                </Deferred>
                            </section>

                            <section className="rounded-xl border border-zinc-200/80 bg-white p-5 shadow-[0_1px_2px_rgba(0,0,0,0.03),0_1px_3px_rgba(0,0,0,0.02)] sm:p-6">
                                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <p className="text-[11px] font-semibold tracking-[0.08em] text-brand uppercase">
                                            Perbandingan profil
                                        </p>
                                        <h2 className="mt-1 text-base font-semibold tracking-[-0.015em] text-zinc-950">
                                            Profil Anda vs profil ideal
                                        </h2>
                                    </div>
                                    <div className="flex flex-col gap-1 sm:w-64">
                                        <label
                                            className="text-xs font-medium text-zinc-600"
                                            htmlFor="compare-career"
                                        >
                                            Bandingkan dengan
                                        </label>
                                        <Select
                                            value={selectedCode}
                                            onValueChange={setSelectedCode}
                                        >
                                            <SelectTrigger
                                                id="compare-career"
                                                className="h-10 w-full rounded-lg border-zinc-300/80 bg-white text-sm shadow-sm focus-visible:ring-2 focus-visible:ring-indigo-500/40 focus-visible:outline-none sm:w-64"
                                            >
                                                <SelectValue placeholder="Pilih karier pembanding" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {results.map((result) => (
                                                    <SelectItem
                                                        key={result.career_code}
                                                        value={
                                                            result.career_code
                                                        }
                                                    >
                                                        <span className="tabular-nums">
                                                            Rank #{result.rank}
                                                        </span>
                                                        : {result.career_name} (
                                                        <span className="tabular-nums">
                                                            {
                                                                result.match_percentage
                                                            }
                                                            %
                                                        </span>
                                                        )
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </div>
                                </div>

                                <div className="mt-5 h-72">
                                    <ResponsiveContainer
                                        width="100%"
                                        height="100%"
                                    >
                                        <RadarChart
                                            data={radarData}
                                            outerRadius="70%"
                                        >
                                            <PolarGrid stroke="#E4E4E7" />
                                            <PolarAngleAxis
                                                dataKey="criterion"
                                                tick={{
                                                    fontSize: 10,
                                                    fill: '#52525B',
                                                }}
                                            />
                                            <PolarRadiusAxis
                                                domain={[0, 5]}
                                                tickCount={6}
                                                tick={false}
                                                axisLine={false}
                                            />
                                            <Radar
                                                name="Profil Anda"
                                                dataKey="Anda"
                                                stroke="#4F46E5"
                                                fill="#4F46E5"
                                                fillOpacity={0.18}
                                            />
                                            <Radar
                                                name="Profil Ideal"
                                                dataKey="Karier"
                                                stroke="#64748B"
                                                fill="#64748B"
                                                fillOpacity={0.12}
                                                strokeDasharray="4 4"
                                            />
                                            <Tooltip
                                                content={<RadarTooltip />}
                                            />
                                        </RadarChart>
                                    </ResponsiveContainer>
                                </div>

                                <div className="mt-3 flex flex-wrap items-center gap-4 text-xs text-zinc-600">
                                    <span className="flex items-center gap-1.5">
                                        <span className="inline-block size-2 rounded-full bg-brand" />{' '}
                                        Profil Anda
                                    </span>
                                    <span className="flex items-center gap-1.5">
                                        <span className="inline-block size-2 rounded-full bg-slate-500" />{' '}
                                        Profil Ideal Karier
                                    </span>
                                    <span className="ml-auto font-medium text-zinc-500 tabular-nums">
                                        Rata-rata Anda{' '}
                                        <span className="text-zinc-950 tabular-nums">
                                            {averageUser.toFixed(1)}
                                        </span>{' '}
                                        · ideal{' '}
                                        <span className="text-zinc-950 tabular-nums">
                                            {averageIdeal.toFixed(1)}
                                        </span>
                                    </span>
                                </div>

                                <details className="mt-4 rounded-lg border border-zinc-200/80">
                                    <summary className="cursor-pointer px-4 py-3 text-sm font-medium text-zinc-950 focus-visible:ring-2 focus-visible:ring-indigo-500/40 focus-visible:outline-none">
                                        Lihat tabel ringkasan skor
                                    </summary>
                                    <div className="overflow-x-auto px-4 pb-4">
                                        <table className="w-full text-left text-xs text-zinc-700">
                                            <thead className="border-b border-zinc-200 text-zinc-500">
                                                <tr>
                                                    <th className="py-2 pr-3 font-medium">
                                                        Kriteria
                                                    </th>
                                                    <th className="py-2 pr-3 font-medium">
                                                        Profil Anda
                                                    </th>
                                                    <th className="py-2 font-medium">
                                                        Profil Ideal
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {criteria.map((criterion) => (
                                                    <tr
                                                        key={criterion.code}
                                                        className="border-b border-zinc-100 last:border-0"
                                                    >
                                                        <td className="py-2 pr-3">
                                                            {criterion.name}
                                                        </td>
                                                        <td className="py-2 pr-3 tabular-nums">
                                                            {ratings[
                                                                criterion.code
                                                            ] ?? 0}
                                                        </td>
                                                        <td className="py-2 tabular-nums">
                                                            {selectedCareer
                                                                ?.profiles?.[
                                                                criterion.code
                                                            ] ?? 0}
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                </details>
                                <p className="mt-3 text-xs leading-relaxed text-zinc-500">
                                    Skor merupakan indeks kecocokan relatif
                                    berbasis metode SAW. Data karier bersifat
                                    awal dan belum tervalidasi.
                                </p>
                            </section>

                            <section className="rounded-xl border border-zinc-200/80 bg-white shadow-[0_1px_2px_rgba(0,0,0,0.03),0_1px_3px_rgba(0,0,0,0.02)]">
                                <div className="flex flex-wrap items-center gap-1 border-b border-zinc-200/80 bg-zinc-50/60 px-3 py-2 sm:px-4">
                                    <div
                                        role="tablist"
                                        aria-label="Detail karier"
                                        className="flex w-full flex-wrap gap-1"
                                    >
                                        {detailTabs.map((tab) => (
                                            <button
                                                key={tab.id}
                                                type="button"
                                                role="tab"
                                                aria-selected={
                                                    activeTab === tab.id
                                                }
                                                onClick={() =>
                                                    setActiveTab(tab.id)
                                                }
                                                className={`rounded-lg px-3 py-1.5 text-xs font-medium transition-all duration-150 focus-visible:ring-2 focus-visible:ring-indigo-500/40 focus-visible:outline-none ${
                                                    activeTab === tab.id
                                                        ? 'bg-white text-zinc-950 shadow-sm ring-1 ring-zinc-200/80'
                                                        : 'text-zinc-600 hover:bg-zinc-100/80 hover:text-zinc-950'
                                                }`}
                                            >
                                                {tab.label}
                                            </button>
                                        ))}
                                    </div>
                                </div>

                                <div className="p-5 sm:p-6">
                                    {activeTab === 'analysis' ? (
                                        <div className="grid gap-6 md:grid-cols-2">
                                            <div>
                                                <p className="text-[11px] font-semibold tracking-[0.08em] text-brand uppercase">
                                                    Kekuatan selaras
                                                </p>
                                                <div className="mt-3 space-y-3">
                                                    {(gap?.strengths ?? [])
                                                        .length > 0 ? (
                                                        (gap?.strengths ?? [])
                                                            .slice(0, 5)
                                                            .map((item) => (
                                                                <div
                                                                    key={
                                                                        item.criterion_code
                                                                    }
                                                                    className="grid grid-cols-[minmax(0,120px)_1fr_36px] items-center gap-3"
                                                                >
                                                                    <span className="truncate text-xs font-medium text-zinc-700">
                                                                        {criterionName(
                                                                            item.criterion_code,
                                                                        )}
                                                                    </span>
                                                                    <div className="h-1.5 overflow-hidden rounded-full bg-zinc-100">
                                                                        <div
                                                                            className="h-1.5 rounded-full bg-indigo-500 transition-all duration-300"
                                                                            style={{
                                                                                width: `${(item.rating / 5) * 100}%`,
                                                                            }}
                                                                        />
                                                                    </div>
                                                                    <span className="text-right text-xs font-semibold text-zinc-950 tabular-nums">
                                                                        {
                                                                            item.rating
                                                                        }
                                                                        /5
                                                                    </span>
                                                                </div>
                                                            ))
                                                    ) : (
                                                        <p className="text-sm text-zinc-500">
                                                            Tidak ada kekuatan
                                                            dominan.
                                                        </p>
                                                    )}
                                                </div>
                                            </div>
                                            <div>
                                                <p className="text-[11px] font-semibold tracking-[0.08em] text-zinc-500 uppercase">
                                                    Area diperkuat
                                                </p>
                                                <div className="mt-3 space-y-3">
                                                    {(
                                                        gap?.needs_strengthening ??
                                                        []
                                                    ).length > 0 ? (
                                                        (
                                                            gap?.needs_strengthening ??
                                                            []
                                                        )
                                                            .slice(0, 5)
                                                            .map((item) => (
                                                                <div
                                                                    key={
                                                                        item.criterion_code
                                                                    }
                                                                    className="grid grid-cols-[minmax(0,120px)_1fr_36px] items-center gap-3"
                                                                >
                                                                    <span className="truncate text-xs font-medium text-zinc-700">
                                                                        {criterionName(
                                                                            item.criterion_code,
                                                                        )}
                                                                    </span>
                                                                    <div className="h-1.5 overflow-hidden rounded-full bg-zinc-100">
                                                                        <div
                                                                            className="h-1.5 rounded-full bg-zinc-400 transition-all duration-300"
                                                                            style={{
                                                                                width: `${(item.rating / 5) * 100}%`,
                                                                            }}
                                                                        />
                                                                    </div>
                                                                    <span className="text-right text-xs font-semibold text-zinc-950 tabular-nums">
                                                                        {
                                                                            item.rating
                                                                        }
                                                                        /5
                                                                    </span>
                                                                </div>
                                                            ))
                                                    ) : (
                                                        <p className="text-sm text-zinc-500">
                                                            Tidak ada gap besar.
                                                        </p>
                                                    )}
                                                </div>
                                            </div>
                                        </div>
                                    ) : null}

                                    {activeTab === 'analysis' &&
                                    (gap?.narrative_strengths ?? []).length >
                                        0 ? (
                                        <div className="mt-6 border-t border-zinc-100 pt-4">
                                            <p className="text-xs font-medium text-zinc-500">
                                                Catatan kecocokan
                                            </p>
                                            <ul className="mt-2 list-disc space-y-1 pl-5 text-sm leading-relaxed text-zinc-600">
                                                {gap?.narrative_strengths.map(
                                                    (sentence) => (
                                                        <li key={sentence}>
                                                            {sentence}
                                                        </li>
                                                    ),
                                                )}
                                            </ul>
                                        </div>
                                    ) : null}

                                    {activeTab === 'description' ? (
                                        <div className="space-y-4">
                                            {selectedCareer?.summary ? (
                                                <p className="text-sm leading-relaxed text-zinc-700">
                                                    {selectedCareer.summary}
                                                </p>
                                            ) : null}
                                            {selectedCareer?.description ? (
                                                <p className="text-sm leading-relaxed text-zinc-600">
                                                    {selectedCareer.description}
                                                </p>
                                            ) : null}
                                            <div className="flex flex-wrap gap-2">
                                                <span
                                                    className={`rounded-full px-2.5 py-1 text-[11px] font-medium ${selectedCareer?.is_validated ? 'bg-emerald-50 text-emerald-700' : 'bg-zinc-100 text-zinc-500'}`}
                                                >
                                                    {selectedCareer?.is_validated
                                                        ? 'Tervalidasi'
                                                        : 'Belum tervalidasi'}
                                                </span>
                                                {selectedCareer?.source_note ? (
                                                    <span className="rounded-full border border-zinc-200/80 bg-zinc-50 px-2.5 py-1 text-[11px] text-zinc-500">
                                                        Sumber:{' '}
                                                        {
                                                            selectedCareer.source_note
                                                        }
                                                    </span>
                                                ) : null}
                                            </div>
                                        </div>
                                    ) : null}

                                    {activeTab === 'skills' ? (
                                        <div className="grid gap-5 sm:grid-cols-2">
                                            <div>
                                                <p className="text-[11px] font-semibold tracking-[0.08em] text-zinc-500 uppercase">
                                                    Skill
                                                </p>
                                                <div className="mt-2.5 flex flex-wrap gap-2">
                                                    {(
                                                        selectedCareer?.skills ??
                                                        []
                                                    ).map((skill) => (
                                                        <span
                                                            key={skill}
                                                            className="rounded-md border border-zinc-200/80 bg-zinc-50/60 px-2.5 py-1 text-xs text-zinc-700"
                                                        >
                                                            {skill}
                                                        </span>
                                                    ))}
                                                </div>
                                            </div>
                                            <div>
                                                <p className="text-[11px] font-semibold tracking-[0.08em] text-zinc-500 uppercase">
                                                    Tools
                                                </p>
                                                <div className="mt-2.5 flex flex-wrap gap-2">
                                                    {(
                                                        selectedCareer?.tools ??
                                                        []
                                                    ).map((tool) => (
                                                        <span
                                                            key={tool}
                                                            className="rounded-md border border-zinc-200/80 bg-zinc-50/60 px-2.5 py-1 text-xs text-zinc-700"
                                                        >
                                                            {tool}
                                                        </span>
                                                    ))}
                                                </div>
                                            </div>
                                        </div>
                                    ) : null}

                                    {activeTab === 'roadmap' ? (
                                        <ol className="space-y-3">
                                            {(
                                                selectedCareer?.roadmap ?? []
                                            ).map((step, index) => (
                                                <li
                                                    key={step}
                                                    className="flex items-start gap-3"
                                                >
                                                    <span className="flex size-6 shrink-0 items-center justify-center rounded-full border border-indigo-600/15 bg-indigo-50/70 text-[11px] font-semibold text-indigo-700 tabular-nums">
                                                        {String(
                                                            index + 1,
                                                        ).padStart(2, '0')}
                                                    </span>
                                                    <span className="pt-0.5 text-sm leading-relaxed text-zinc-700">
                                                        {step}
                                                    </span>
                                                </li>
                                            ))}
                                        </ol>
                                    ) : null}
                                </div>
                            </section>
                        </div>

                        <aside className="min-w-0 space-y-6 lg:sticky lg:top-20">
                            <section className="overflow-hidden rounded-xl border border-zinc-200/80 bg-white shadow-[0_1px_2px_rgba(0,0,0,0.03),0_1px_3px_rgba(0,0,0,0.02)]">
                                <div className="border-b border-zinc-200/80 p-4 sm:p-5">
                                    <p className="text-[11px] font-semibold tracking-[0.08em] text-brand uppercase">
                                        Peringkat
                                    </p>
                                    <h2 className="mt-1 text-base font-semibold tracking-[-0.015em] text-zinc-950">
                                        Urutan rekomendasi
                                    </h2>
                                    <p className="mt-1 text-xs text-zinc-500">
                                        Klik karier untuk melihat detail
                                        analisisnya.
                                    </p>
                                </div>
                                <ol className="divide-y divide-zinc-100">
                                    {results.map((item) => {
                                        const isActive =
                                            selectedCode === item.career_code;

                                        return (
                                            <li key={item.career_code}>
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        setSelectedCode(
                                                            item.career_code,
                                                        )
                                                    }
                                                    aria-pressed={isActive}
                                                    className={`flex min-w-0 items-center gap-3 px-4 py-3 text-left transition-colors duration-150 focus-visible:ring-2 focus-visible:ring-indigo-500/40 focus-visible:outline-none focus-visible:ring-inset ${
                                                        isActive
                                                            ? 'bg-gradient-to-b from-indigo-50/70 to-white'
                                                            : 'hover:bg-zinc-50/80'
                                                    }`}
                                                >
                                                    <span
                                                        className={`text-sm font-semibold tabular-nums ${isActive ? 'text-indigo-700' : 'text-zinc-400'}`}
                                                    >
                                                        {String(
                                                            item.rank,
                                                        ).padStart(2, '0')}
                                                    </span>
                                                    <div className="min-w-0 flex-1">
                                                        <p className="truncate text-sm font-medium text-zinc-950">
                                                            {item.career_name}
                                                            {item.is_tied ? (
                                                                <span className="ml-2 inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-2 py-0.5 text-[10px] font-medium text-amber-700">
                                                                    Setara
                                                                </span>
                                                            ) : null}
                                                        </p>
                                                        <div className="mt-1.5 h-1 overflow-hidden rounded-full bg-zinc-100">
                                                            <div
                                                                className={`h-1 rounded-full transition-all duration-300 ${isActive ? 'bg-indigo-600' : 'bg-zinc-400'}`}
                                                                style={{
                                                                    width: `${parseFloat(item.match_percentage)}%`,
                                                                }}
                                                            />
                                                        </div>
                                                    </div>
                                                    <span className="shrink-0 text-sm font-semibold text-zinc-950 tabular-nums">
                                                        {formatPercent(Number(item.match_percentage))}
                                                    </span>
                                                </button>
                                            </li>
                                        );
                                    })}
                                </ol>
                            </section>

                            <section className="rounded-xl border border-zinc-200/80 bg-white p-5 shadow-[0_1px_2px_rgba(0,0,0,0.03),0_1px_3px_rgba(0,0,0,0.02)]">
                                <p className="text-[11px] font-semibold tracking-[0.08em] text-brand uppercase">
                                    Tanggapan
                                </p>
                                <h2 className="mt-1 text-base font-semibold tracking-[-0.015em] text-zinc-950">
                                    Penilaian Pengunjung
                                </h2>
                                <p className="mt-1 text-xs leading-relaxed text-zinc-600">
                                    Bantu kami meningkatkan kualitas analisis
                                    dengan memberikan penilaian singkat.
                                </p>
                                <form
                                    onSubmit={submitFeedback}
                                    className="mt-4 space-y-4"
                                >
                                    <div>
                                        <div className="flex items-center justify-between text-sm">
                                            <label
                                                htmlFor="feedback-score"
                                                className="font-medium text-zinc-950"
                                            >
                                                Skor kepuasan
                                            </label>
                                            <span className="font-semibold text-zinc-950 tabular-nums">
                                                {feedbackForm.data.score}/100
                                            </span>
                                        </div>
                                        <div className="mt-2 grid grid-cols-5 gap-1.5">
                                            {[70, 80, 90, 95, 100].map(
                                                (preset) => (
                                                    <button
                                                        key={preset}
                                                        type="button"
                                                        onClick={() =>
                                                            feedbackForm.setData(
                                                                'score',
                                                                preset,
                                                            )
                                                        }
                                                        aria-pressed={
                                                            feedbackForm.data
                                                                .score ===
                                                            preset
                                                        }
                                                        className={`min-h-9 rounded-lg border text-sm font-semibold tabular-nums transition-all duration-150 focus-visible:ring-2 focus-visible:ring-indigo-500/40 focus-visible:outline-none ${
                                                            feedbackForm.data
                                                                .score ===
                                                            preset
                                                                ? 'border-indigo-600/30 bg-indigo-600 text-white shadow-sm'
                                                                : 'border-zinc-300 bg-white text-zinc-700 hover:border-zinc-400 hover:text-zinc-950'
                                                        }`}
                                                    >
                                                        {preset}
                                                    </button>
                                                ),
                                            )}
                                        </div>
                                        <div className="mt-2 flex items-center gap-2">
                                            <input
                                                id="feedback-score"
                                                name="score"
                                                type="number"
                                                min={0}
                                                max={100}
                                                value={feedbackForm.data.score}
                                                onChange={(event) =>
                                                    feedbackForm.setData(
                                                        'score',
                                                        Number(
                                                            event.target.value,
                                                        ),
                                                    )
                                                }
                                                className="h-10 w-full rounded-lg border border-zinc-300 px-3 text-sm text-zinc-950 tabular-nums transition outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                                            />
                                            <span className="shrink-0 text-xs text-zinc-500">
                                                /100
                                            </span>
                                        </div>
                                        {feedbackForm.errors.score ? (
                                            <p className="mt-2 text-sm text-red-600">
                                                {feedbackForm.errors.score}
                                            </p>
                                        ) : null}
                                    </div>
                                    <div>
                                        <label
                                            htmlFor="feedback-comment"
                                            className="text-sm font-medium text-zinc-950"
                                        >
                                            Tanggapan (opsional)
                                        </label>
                                        <textarea
                                            id="feedback-comment"
                                            name="comment"
                                            rows={3}
                                            maxLength={1000}
                                            value={feedbackForm.data.comment}
                                            onChange={(event) =>
                                                feedbackForm.setData(
                                                    'comment',
                                                    event.target.value,
                                                )
                                            }
                                            className="mt-2 w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm text-zinc-950 transition outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            htmlFor="feedback-suggestion"
                                            className="text-sm font-medium text-zinc-950"
                                        >
                                            Saran perbaikan (opsional)
                                        </label>
                                        <textarea
                                            id="feedback-suggestion"
                                            name="suggestion"
                                            rows={3}
                                            maxLength={1000}
                                            value={feedbackForm.data.suggestion}
                                            onChange={(event) =>
                                                feedbackForm.setData(
                                                    'suggestion',
                                                    event.target.value,
                                                )
                                            }
                                            className="mt-2 w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm text-zinc-950 transition outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                                        />
                                    </div>
                                    <div className="flex flex-wrap items-center gap-3">
                                        <Button
                                            type="submit"
                                            disabled={feedbackForm.processing}
                                            className="w-full sm:w-auto"
                                        >
                                            {feedbackForm.processing
                                                ? 'Mengirim...'
                                                : 'Kirim Penilaian'}
                                        </Button>
                                        {feedbackSubmitted ? (
                                            <p className="text-sm font-medium text-brand">
                                                Penilaian terkirim. Terima
                                                kasih.
                                            </p>
                                        ) : null}
                                    </div>
                                </form>
                            </section>
                        </aside>
                    </div>
                </div>
            </PublicLayout>
        </>
    );
}
