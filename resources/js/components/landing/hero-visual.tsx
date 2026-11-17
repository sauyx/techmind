import { useState } from 'react';

type ProfileKey = 'logic' | 'visual' | 'data';

type Profile = {
    label: string;
    shortLabel: string;
    criteria: { label: string; value: number }[];
    ranking: { name: string; score: number }[];
};

const profiles: Record<ProfileKey, Profile> = {
    logic: {
        label: 'Logika & Kode',
        shortLabel: 'Logika',
        criteria: [
            { label: 'Logika', value: 5 },
            { label: 'Pemrograman', value: 5 },
            { label: 'Data', value: 4 },
            { label: 'Desain', value: 2 },
        ],
        ranking: [
            { name: 'Backend Developer', score: 88.4 },
            { name: 'DevOps Engineer', score: 81.2 },
            { name: 'Data Analyst', score: 76.5 },
        ],
    },
    visual: {
        label: 'Visual & Empati',
        shortLabel: 'Visual',
        criteria: [
            { label: 'Desain', value: 5 },
            { label: 'Empati', value: 5 },
            { label: 'Kreativitas', value: 5 },
            { label: 'Logika', value: 3 },
        ],
        ranking: [
            { name: 'UI/UX Designer', score: 91.8 },
            { name: 'System Analyst', score: 79.3 },
            { name: 'Web Developer', score: 74.1 },
        ],
    },
    data: {
        label: 'Data & Statistik',
        shortLabel: 'Data',
        criteria: [
            { label: 'Data', value: 5 },
            { label: 'Statistik', value: 5 },
            { label: 'Detail', value: 5 },
            { label: 'Bisnis', value: 4 },
        ],
        ranking: [
            { name: 'Data Analyst', score: 89.7 },
            { name: 'QA Engineer', score: 83.4 },
            { name: 'System Analyst', score: 78.2 },
        ],
    },
};

const tabOrder: ProfileKey[] = ['logic', 'visual', 'data'];

export function HeroVisual() {
    const [active, setActive] = useState<ProfileKey>('logic');
    const profile = profiles[active];

    return (
        <div className="relative w-full">
            <div
                aria-hidden="true"
                className="absolute -inset-4 -z-10 rounded-[2rem] bg-gradient-to-b from-indigo-50/70 via-white/40 to-transparent blur-2xl"
            />
            <div className="rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-[0_1px_2px_rgba(0,0,0,0.03),0_24px_60px_-32px_rgba(24,24,27,0.18)] transition-all duration-200 hover:border-zinc-300 sm:p-6">
                <div className="flex items-start justify-between gap-3 border-b border-zinc-200/80 pb-4">
                    <div>
                        <p className="text-[11px] font-semibold tracking-[0.08em] text-zinc-500 uppercase">
                            Simulasi hasil
                        </p>
                        <p className="mt-1 text-sm font-semibold tracking-[-0.015em] text-zinc-950">
                            Pratinjau kartu analisis
                        </p>
                    </div>
                    <span className="rounded-full border border-indigo-600/15 bg-indigo-50/70 px-2.5 py-1 text-[10px] font-semibold tracking-[0.08em] text-indigo-700 uppercase">
                        Interaktif
                    </span>
                </div>

                <div
                    className="mt-4 flex flex-wrap gap-1.5"
                    role="tablist"
                    aria-label="Pilih profil simulasi"
                >
                    {tabOrder.map((key) => {
                        const isActive = key === active;

                        return (
                            <button
                                key={key}
                                type="button"
                                role="tab"
                                aria-selected={isActive}
                                onClick={() => setActive(key)}
                                className={`rounded-full px-3 py-1.5 text-xs font-medium transition-all duration-200 focus-visible:ring-2 focus-visible:ring-indigo-500/40 focus-visible:outline-none ${
                                    isActive
                                        ? 'bg-zinc-950 text-white shadow-sm'
                                        : 'border border-zinc-200/80 bg-white text-zinc-600 hover:border-zinc-300 hover:text-zinc-950'
                                }`}
                            >
                                {profiles[key].shortLabel}
                            </button>
                        );
                    })}
                </div>

                <div className="space-y-3 py-4">
                    {profile.criteria.map((item) => (
                        <div
                            key={item.label}
                            className="grid grid-cols-[84px_1fr_40px] items-center gap-3"
                        >
                            <span className="truncate text-xs font-medium text-zinc-600">
                                {item.label}
                            </span>
                            <div className="h-2 overflow-hidden rounded-full bg-zinc-100">
                                <div
                                    className="h-2 rounded-full bg-gradient-to-r from-indigo-500 to-indigo-600 transition-[width] duration-500 ease-out"
                                    style={{
                                        width: `${(item.value / 5) * 100}%`,
                                    }}
                                />
                            </div>
                            <span className="text-right text-xs font-semibold text-zinc-950 tabular-nums">
                                {item.value}/5
                            </span>
                        </div>
                    ))}
                </div>

                <div className="rounded-xl border border-zinc-200/80 bg-zinc-50/80 p-4 transition-all duration-200">
                    <div className="flex items-center justify-between">
                        <p className="text-[11px] font-semibold tracking-[0.08em] text-zinc-500 uppercase">
                            Peringkat simulasi
                        </p>
                        <span className="text-[10px] font-medium text-zinc-400">
                            {profile.label}
                        </span>
                    </div>
                    <div className="mt-3 space-y-3">
                        {profile.ranking.map((item, index) => (
                            <div
                                key={item.name}
                                className="grid grid-cols-[22px_1fr_48px] items-center gap-3"
                            >
                                <span className="text-xs font-semibold text-zinc-400 tabular-nums">
                                    {String(index + 1).padStart(2, '0')}
                                </span>
                                <div className="min-w-0">
                                    <p className="truncate text-xs font-medium text-zinc-950">
                                        {item.name}
                                    </p>
                                    <div className="mt-1 h-1.5 overflow-hidden rounded-full bg-zinc-200/80">
                                        <div
                                            className="h-1.5 rounded-full bg-brand/80 transition-[width] duration-500 ease-out"
                                            style={{ width: `${item.score}%` }}
                                        />
                                    </div>
                                </div>
                                <span className="text-right text-xs font-semibold text-zinc-950 tabular-nums">
                                    {item.score}%
                                </span>
                            </div>
                        ))}
                    </div>
                </div>

                <p className="mt-4 text-[11px] leading-relaxed text-zinc-500">
                    Ilustrasi perhitungan SAW menggunakan data contoh, bukan
                    hasil pengunjung.
                </p>
            </div>
        </div>
    );
}
