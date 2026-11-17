import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';
import {
    PolarAngleAxis,
    PolarGrid,
    PolarRadiusAxis,
    Radar,
    RadarChart,
    ResponsiveContainer,
} from 'recharts';
import PublicLayout from '@/layouts/public-layout';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

type Criterion = { code: string; name: string; short_label: string };

type ResultItem = {
    id: number;
    rank: number;
    career_code: string;
    career_name: string;
    saw_score: number;
    match_percentage: string;
    contribution_breakdown: Record<string, number>;
    gap_summary: {
        strengths: { criterion_code: string; rating: number; profile: number; sentence: string }[];
        needs_strengthening: { criterion_code: string; rating: number; profile: number; sentence: string }[];
        underused: { criterion_code: string; rating: number; profile: number; sentence: string }[];
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
    explanation: string;
};

export default function ResultPage({
    session,
    results,
    ratings,
    criteria,
    careers,
    explanation,
}: Props) {
    const topResult = results[0];
    const [selectedCode, setSelectedCode] = useState(topResult?.career_code ?? '');
    const [copied, setCopied] = useState(false);
    const [selectedRadarIndex, setSelectedRadarIndex] = useState(0);

    const selectedResult = results.find((item) => item.career_code === selectedCode) ?? topResult;
    const selectedCareer = careers.find((career) => career.code === selectedCode) ?? careers[0];

    const radarData = criteria.map((criterion) => ({
        criterion: criterion.short_label,
        Anda: ratings[criterion.code] ?? 0,
        Karier: selectedCareer?.profiles?.[criterion.code] ?? 0,
    }));

    const topCareer = careers.find((career) => career.code === topResult?.career_code);

    const topTags = (topResult?.gap_summary?.strengths ?? [])
        .slice(0, 3)
        .map((item) => criteria.find((criterion) => criterion.code === item.criterion_code)?.name ?? item.criterion_code);

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

    return (
        <>
            <Head title="Hasil Analisis" />
            <PublicLayout>
                <div className="mx-auto max-w-4xl space-y-8">
                    <section>
                        <p className="text-sm font-medium text-brand">Hasil analisis</p>
                        <h1 className="mt-2 text-3xl font-semibold tracking-tight text-zinc-950">
                            {session.visitor_name
                                ? `Hasil Analisis Karier untuk ${session.visitor_name}`
                                : 'Hasil Analisis Karier TechMind'}
                        </h1>
                        <div className="mt-4 flex flex-wrap gap-3">
                            <Button type="button" variant="outline" onClick={copyLink}>
                                {copied ? 'Link tersalin' : 'Salin Link Hasil'}
                            </Button>
                            <Link href="/tes">
                                <Button type="button" variant="outline">Ulangi Tes</Button>
                            </Link>
                        </div>
                    </section>

                    {topResult ? (
                        <section className="rounded-xl border border-zinc-200 bg-white p-6 sm:p-8">
                            <p className="text-sm font-medium text-brand">Peringkat 1</p>
                            <h2 className="mt-2 text-2xl font-semibold tracking-tight text-zinc-950">
                                {topResult.career_name}
                            </h2>
                            <p className="mt-2 text-lg text-zinc-700">
                                {topResult.match_percentage}% kecocokan
                            </p>
                            {topResult.gap_summary?.narrative_strengths?.[0] ? (
                                <p className="mt-3 text-sm leading-6 text-zinc-600">
                                    {topResult.gap_summary.narrative_strengths[0]}
                                </p>
                            ) : null}
                            {topResult.gap_summary?.narrative_needs?.[0] ? (
                                <p className="mt-2 text-sm leading-6 text-zinc-600">
                                    {topResult.gap_summary.narrative_needs[0]}
                                </p>
                            ) : null}
                            {topResult.gap_summary?.narrative_underused?.[0] ? (
                                <p className="mt-2 text-sm leading-6 text-zinc-600">
                                    {topResult.gap_summary.narrative_underused[0]}
                                </p>
                            ) : null}
                            {topCareer?.summary ? (
                                <p className="mt-3 text-sm leading-6 text-zinc-600">{topCareer.summary}</p>
                            ) : null}
                            {topTags.length > 0 ? (
                                <div className="mt-4 flex flex-wrap gap-2">
                                    {topTags.map((tag) => (
                                        <span key={tag} className="rounded-md border border-zinc-200 px-2 py-1 text-xs text-zinc-700">
                                            {tag}
                                        </span>
                                    ))}
                                </div>
                            ) : null}
                        </section>
                    ) : null}

                    <section className="rounded-xl border border-zinc-200 bg-white p-4 sm:p-6">
                        <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <h2 className="text-lg font-semibold text-zinc-950">Perbandingan profil</h2>
                            <label className="text-sm text-zinc-600" htmlFor="compare-career">
                                Bandingkan dengan
                            </label>
                            <Select value={selectedCode} onValueChange={setSelectedCode}>
                                <SelectTrigger className="w-full sm:w-64">
                                    <SelectValue placeholder="Pilih karier pembanding" />
                                </SelectTrigger>
                                <SelectContent>
                                    {results.map((result) => (
                                        <SelectItem key={result.career_code} value={result.career_code}>
                                            Rank #{result.rank}: {result.career_name} ({result.match_percentage}%)
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="mt-4 h-72">
                            <ResponsiveContainer width="100%" height="100%">
                                <RadarChart data={radarData} outerRadius="70%">
                                    <PolarGrid />
                                    <PolarAngleAxis dataKey="criterion" tick={{ fontSize: 10 }} />
                                    <PolarRadiusAxis domain={[0, 5]} tickCount={6} />
                                    <Radar
                                        name="Profil Anda"
                                        dataKey="Anda"
                                        stroke="#4F46E5"
                                        fill="#4F46E5"
                                        fillOpacity={0.18}
                                    />
                                    <Radar
                                        name={selectedCareer?.name ?? 'Karier'}
                                        dataKey="Karier"
                                        stroke="#64748B"
                                        fill="#64748B"
                                        fillOpacity={0.12}
                                        strokeDasharray="4 4"
                                    />
                                </RadarChart>
                            </ResponsiveContainer>
                        </div>
                        <div className="mt-2 flex gap-4 text-xs text-zinc-600">
                            <span className="flex items-center gap-1">
                                <span className="inline-block h-2 w-2 rounded-full bg-brand" /> Profil Anda
                            </span>
                            <span className="flex items-center gap-1">
                                <span className="inline-block h-2 w-2 rounded-full bg-slate-500" /> Profil Ideal Karier
                            </span>
                        </div>
                        <details className="mt-4 rounded-lg border border-zinc-200">
                            <summary className="cursor-pointer px-4 py-3 text-sm font-medium text-zinc-950">
                                Lihat tabel ringkasan skor
                            </summary>
                            <div className="overflow-x-auto px-4 pb-4">
                                <table className="w-full text-left text-xs text-zinc-700">
                                    <thead className="border-b border-zinc-200 text-zinc-500">
                                        <tr>
                                            <th className="py-2 pr-3 font-medium">Kriteria</th>
                                            <th className="py-2 pr-3 font-medium">Profil Kamu</th>
                                            <th className="py-2 font-medium">Profil Ideal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {criteria.map((criterion) => (
                                            <tr key={criterion.code} className="border-b border-zinc-100 last:border-0">
                                                <td className="py-2 pr-3">{criterion.name}</td>
                                                <td className="py-2 pr-3">{ratings[criterion.code] ?? 0}</td>
                                                <td className="py-2">{selectedCareer?.profiles?.[criterion.code] ?? 0}</td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </details>
                        <p className="mt-3 text-xs text-zinc-500">
                            Skor merupakan indeks kecocokan relatif berbasis metode SAW. Data karier bersifat awal dan belum tervalidasi.
                        </p>
                    </section>

                    <section>
                        <h2 className="text-lg font-semibold text-zinc-950">Urutan rekomendasi</h2>
                        <div className="mt-4 space-y-1.5">
                            {results.map((item) => (
                                <button
                                    key={item.career_code}
                                    type="button"
                                    onClick={() => setSelectedCode(item.career_code)}
                                    className={`flex w-full items-center justify-between rounded-lg border px-4 py-3 text-left transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2 ${selectedCode === item.career_code ? 'border-brand bg-brand/5' : 'border-zinc-200 hover:border-zinc-300'}`}
                                >
                                    <div className="flex items-center gap-3">
                                        <span className="text-sm font-semibold text-zinc-500">{item.rank}</span>
                                        <span className="text-sm font-medium text-zinc-950">{item.career_name}</span>
                                    </div>
                                    <div className="flex items-center gap-3">
                                        <div className="h-2 w-24 rounded-full bg-zinc-200">
                                            <div className="h-2 rounded-full bg-brand" style={{ width: `${item.match_percentage}%` }} />
                                        </div>
                                        <span className="text-sm text-zinc-600">{item.match_percentage}%</span>
                                    </div>
                                </button>
                            ))}
                        </div>
                    </section>

                    <section className="rounded-xl border border-zinc-200 bg-white p-6">
                        <h2 className="text-lg font-semibold text-zinc-950">
                            Detail karier: {selectedCareer?.name}
                        </h2>
                        {selectedCareer?.summary ? (
                            <p className="mt-2 text-sm text-zinc-600">{selectedCareer.summary}</p>
                        ) : null}
                        {selectedCareer?.description ? (
                            <p className="mt-3 text-sm leading-6 text-zinc-600">{selectedCareer.description}</p>
                        ) : null}

                        <div className="mt-5 grid gap-4 sm:grid-cols-3">
                            <div className="rounded-lg border border-zinc-200 p-4">
                                <h3 className="text-sm font-semibold text-zinc-950">Analisis kecocokan</h3>
                                <div className="mt-2 space-y-2 text-sm text-zinc-600">
                                    <div>
                                        <p className="font-medium text-zinc-950">Kekuatan selaras</p>
                                        <ul className="mt-1 list-disc space-y-1 pl-5">
                                            {(gap?.narrative_strengths ?? []).length > 0 ? (
                                                (gap?.narrative_strengths ?? []).map((s) => (
                                                    <li key={s}>{s}</li>
                                                ))
                                            ) : (
                                                <li>Tidak ada kekuatan dominan.</li>
                                            )}
                                        </ul>
                                    </div>
                                    <div>
                                        <p className="font-medium text-zinc-950">Area diperkuat</p>
                                        <ul className="mt-1 list-disc space-y-1 pl-5">
                                            {(gap?.narrative_needs ?? []).length > 0 ? (
                                                (gap?.narrative_needs ?? []).map((s) => (
                                                    <li key={s}>{s}</li>
                                                ))
                                            ) : (
                                                <li>Tidak ada gap besar.</li>
                                            )}
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div className="rounded-lg border border-zinc-200 p-4">
                                <h3 className="text-sm font-semibold text-zinc-950">Skill & tools</h3>
                                <div className="mt-2">
                                    <p className="text-xs font-medium text-zinc-500">Skill</p>
                                    <div className="mt-1 flex flex-wrap gap-2">
                                        {(selectedCareer?.skills ?? []).map((skill) => (
                                            <span key={skill} className="rounded-md border border-zinc-200 px-2 py-1 text-xs text-zinc-700">
                                                {skill}
                                            </span>
                                        ))}
                                    </div>
                                </div>
                                <div className="mt-3">
                                    <p className="text-xs font-medium text-zinc-500">Tools</p>
                                    <div className="mt-1 flex flex-wrap gap-2">
                                        {(selectedCareer?.tools ?? []).map((tool) => (
                                            <span key={tool} className="rounded-md border border-zinc-200 px-2 py-1 text-xs text-zinc-700">
                                                {tool}
                                            </span>
                                        ))}
                                    </div>
                                </div>
                            </div>

                            <div className="rounded-lg border border-zinc-200 p-4">
                                <h3 className="text-sm font-semibold text-zinc-950">Roadmap belajar</h3>
                                <ol className="mt-2 list-decimal space-y-1 pl-5 text-sm text-zinc-600">
                                    {(selectedCareer?.roadmap ?? []).map((step) => (
                                        <li key={step}>{step}</li>
                                    ))}
                                </ol>
                            </div>
                        </div>

                        <p className="mt-6 text-xs text-zinc-500">
                            Skor merupakan indeks kecocokan relatif berbasis metode SAW. Data karier bersifat awal dan belum tervalidasi.
                        </p>
                    </section>

                    <section className="rounded-xl border border-dashed border-zinc-300 bg-zinc-50 p-6">
                        <h2 className="text-lg font-semibold text-zinc-950">Ringkasan Analisis Personal</h2>
                        <p className="mt-2 text-sm leading-6 text-zinc-600">
                            {explanation}
                        </p>
                        <p className="mt-3 text-xs text-zinc-500">
                            Ringkasan ini akan diperkaya oleh mesin penjelasan otomatis pada sesi berikutnya.
                        </p>
                    </section>
                </div>
            </PublicLayout>
        </>
    );
}