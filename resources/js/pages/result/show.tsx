import { Head } from '@inertiajs/react';
import {
    PolarAngleAxis,
    PolarGrid,
    PolarRadiusAxis,
    Radar,
    RadarChart,
    Legend,
    ResponsiveContainer,
} from 'recharts';
import PublicLayout from '@/layouts/public-layout';

type Criterion = { code: string; name: string };

type RankingItem = {
    rank: number;
    career_code: string;
    career_name: string;
    score: number;
    display_score: number;
};

type GapItem = { criterion_code: string; rating: number; profile: number };

type CareerDetail = {
    code: string;
    name: string;
    summary: string | null;
    description: string | null;
    skills: string[] | null;
    tools: string[] | null;
    roadmap: string[] | null;
} | null;

type Props = {
    session: { token: string; visitor_name: string | null };
    result: {
        method_code: string;
        ranking: RankingItem[];
        top_career_code: string;
        top_career_name: string;
    };
    criteria: Criterion[];
    ratings: Record<string, number>;
    top_profile: Record<string, number>;
    gap_analysis: {
        strengths: GapItem[];
        needs_strengthening: GapItem[];
        underused: GapItem[];
    };
    explanation: string;
    career: CareerDetail;
};

function criterionName(criteria: Criterion[], code: string): string {
    return criteria.find((item) => item.code === code)?.name ?? code;
}

export default function ResultShow({
    session,
    result,
    criteria,
    ratings,
    top_profile,
    gap_analysis,
    explanation,
    career,
}: Props) {
    const top = result.ranking[0];
    const radarData = criteria.map((criterion) => ({
        criterion: criterion.name,
        Anda: ratings[criterion.code] ?? 0,
        Karier: top_profile[criterion.code] ?? 0,
    }));

    return (
        <>
            <Head title="Hasil Rekomendasi" />
            <PublicLayout>
                <div className="mx-auto max-w-4xl space-y-8">
                    <section>
                        <p className="text-sm font-medium text-brand">
                            Hasil rekomendasi
                        </p>
                        <h1 className="mt-2 text-3xl font-semibold tracking-tight text-zinc-950">
                            {result.top_career_name}
                        </h1>
                        <p className="mt-2 text-lg text-zinc-600">
                            Skor kecocokan: {top.display_score}%
                        </p>
                        <p className="mt-4 max-w-2xl text-sm leading-6 text-zinc-600">
                            {explanation}
                        </p>
                    </section>

                    <section className="rounded-xl border border-zinc-200 bg-white p-4 sm:p-6">
                        <h2 className="text-lg font-semibold text-zinc-950">
                            Profil Anda vs profil karier
                        </h2>
                        <div className="mt-4 h-80">
                            <ResponsiveContainer width="100%" height="100%">
                                <RadarChart data={radarData} outerRadius="75%">
                                    <PolarGrid />
                                    <PolarAngleAxis dataKey="criterion" tick={{ fontSize: 11 }} />
                                    <PolarRadiusAxis domain={[0, 5]} tickCount={6} />
                                    <Radar
                                        name="Profil Anda"
                                        dataKey="Anda"
                                        stroke="#4F46E5"
                                        fill="#4F46E5"
                                        fillOpacity={0.18}
                                    />
                                    <Radar
                                        name={result.top_career_name}
                                        dataKey="Karier"
                                        stroke="#71717a"
                                        fill="#71717a"
                                        fillOpacity={0.12}
                                    />
                                    <Legend />
                                </RadarChart>
                            </ResponsiveContainer>
                        </div>
                    </section>

                    <section>
                        <h2 className="text-lg font-semibold text-zinc-950">
                            Urutan rekomendasi
                        </h2>
                        <div className="mt-4 space-y-2">
                            {result.ranking.map((item) => (
                                <div
                                    key={item.career_code}
                                    className="flex items-center justify-between rounded-lg border border-zinc-200 px-4 py-3"
                                >
                                    <div className="flex items-center gap-3">
                                        <span className="text-sm font-semibold text-zinc-500">
                                            {item.rank}
                                        </span>
                                        <span className="text-sm font-medium text-zinc-950">
                                            {item.career_name}
                                        </span>
                                    </div>
                                    <span className="text-sm text-zinc-600">
                                        {item.display_score}%
                                    </span>
                                </div>
                            ))}
                        </div>
                    </section>

                    <section className="grid gap-4 sm:grid-cols-3">
                        <div className="rounded-xl border border-zinc-200 p-4">
                            <h3 className="text-sm font-semibold text-zinc-950">
                                Kekuatan
                            </h3>
                            <ul className="mt-2 space-y-1 text-sm text-zinc-600">
                                {gap_analysis.strengths.length > 0 ? (
                                    gap_analysis.strengths.map((item) => (
                                        <li key={item.criterion_code}>
                                            {criterionName(criteria, item.criterion_code)}
                                        </li>
                                    ))
                                ) : (
                                    <li>Tidak ada kekuatan dominan.</li>
                                )}
                            </ul>
                        </div>
                        <div className="rounded-xl border border-zinc-200 p-4">
                            <h3 className="text-sm font-semibold text-zinc-950">
                                Perlu diperkuat
                            </h3>
                            <ul className="mt-2 space-y-1 text-sm text-zinc-600">
                                {gap_analysis.needs_strengthening.length > 0 ? (
                                    gap_analysis.needs_strengthening.map((item) => (
                                        <li key={item.criterion_code}>
                                            {criterionName(criteria, item.criterion_code)}
                                        </li>
                                    ))
                                ) : (
                                    <li>Tidak ada gap besar.</li>
                                )}
                            </ul>
                        </div>
                        <div className="rounded-xl border border-zinc-200 p-4">
                            <h3 className="text-sm font-semibold text-zinc-950">
                                Belum banyak digunakan
                            </h3>
                            <ul className="mt-2 space-y-1 text-sm text-zinc-600">
                                {gap_analysis.underused.length > 0 ? (
                                    gap_analysis.underused.map((item) => (
                                        <li key={item.criterion_code}>
                                            {criterionName(criteria, item.criterion_code)}
                                        </li>
                                    ))
                                ) : (
                                    <li>Tidak ada.</li>
                                )}
                            </ul>
                        </div>
                    </section>

                    {career ? (
                        <section className="rounded-xl border border-zinc-200 bg-white p-6">
                            <h2 className="text-lg font-semibold text-zinc-950">
                                Detail karier: {career.name}
                            </h2>
                            {career.summary ? (
                                <p className="mt-2 text-sm text-zinc-600">
                                    {career.summary}
                                </p>
                            ) : null}
                            {career.description ? (
                                <p className="mt-3 text-sm leading-6 text-zinc-600">
                                    {career.description}
                                </p>
                            ) : null}

                            {career.skills && career.skills.length > 0 ? (
                                <div className="mt-4">
                                    <h3 className="text-sm font-semibold text-zinc-950">
                                        Skill
                                    </h3>
                                    <div className="mt-2 flex flex-wrap gap-2">
                                        {career.skills.map((skill) => (
                                            <span
                                                key={skill}
                                                className="rounded-md border border-zinc-200 px-2 py-1 text-xs text-zinc-700"
                                            >
                                                {skill}
                                            </span>
                                        ))}
                                    </div>
                                </div>
                            ) : null}

                            {career.tools && career.tools.length > 0 ? (
                                <div className="mt-4">
                                    <h3 className="text-sm font-semibold text-zinc-950">
                                        Tools
                                    </h3>
                                    <div className="mt-2 flex flex-wrap gap-2">
                                        {career.tools.map((tool) => (
                                            <span
                                                key={tool}
                                                className="rounded-md border border-zinc-200 px-2 py-1 text-xs text-zinc-700"
                                            >
                                                {tool}
                                            </span>
                                        ))}
                                    </div>
                                </div>
                            ) : null}

                            {career.roadmap && career.roadmap.length > 0 ? (
                                <div className="mt-4">
                                    <h3 className="text-sm font-semibold text-zinc-950">
                                        Roadmap belajar
                                    </h3>
                                    <ol className="mt-2 list-decimal space-y-1 pl-5 text-sm text-zinc-600">
                                        {career.roadmap.map((step) => (
                                            <li key={step}>{step}</li>
                                        ))}
                                    </ol>
                                </div>
                            ) : null}
                        </section>
                    ) : null}
                </div>
            </PublicLayout>
        </>
    );
}
