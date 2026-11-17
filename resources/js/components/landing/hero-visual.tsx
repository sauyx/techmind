export function HeroVisual() {
    const criteria = [
        { label: 'Logika', value: 5 },
        { label: 'Pemrograman', value: 5 },
        { label: 'Desain', value: 2 },
    ];

    const ranking = [
        { name: 'Backend Developer', score: '88.4%' },
        { name: 'DevOps Engineer', score: '81.2%' },
        { name: 'Data Analyst', score: '76.5%' },
    ];

    return (
        <div className="w-full max-w-md">
            <div className="rounded-2xl border border-zinc-200/80 bg-white p-5 sm:p-6">
                <div className="flex items-center justify-between gap-3 border-b border-zinc-200/80 pb-4">
                    <div>
                        <p className="text-[11px] font-medium uppercase tracking-wide text-zinc-500">
                            Preview Kartu Analisis
                        </p>
                        <p className="mt-1 text-sm font-semibold tracking-tight text-zinc-950">
                            TechMind
                        </p>
                    </div>
                    <span className="rounded-md bg-brand/10 px-2 py-1 text-[11px] font-semibold text-brand">
                        Contoh Skor
                    </span>
                </div>

                <div className="space-y-3 py-4">
                    {criteria.map((item) => (
                        <div key={item.label} className="grid grid-cols-[88px_1fr_40px] items-center gap-3">
                            <span className="truncate text-xs font-medium text-zinc-600">
                                {item.label}
                            </span>
                            <div className="h-2 overflow-hidden rounded-full bg-zinc-200">
                                <div
                                    className="h-2 rounded-full bg-brand"
                                    style={{ width: `${(item.value / 5) * 100}%` }}
                                />
                            </div>
                            <span className="text-right text-xs font-semibold tabular-nums text-zinc-950">
                                {item.value}/5
                            </span>
                        </div>
                    ))}
                </div>

                <div className="rounded-xl border border-zinc-200 bg-zinc-50 p-4">
                    <p className="text-[11px] font-medium uppercase tracking-wide text-zinc-500">
                        Peringkat Simulasi
                    </p>
                    <div className="mt-3 space-y-2.5">
                        {ranking.map((item, index) => (
                            <div key={item.name} className="grid grid-cols-[24px_1fr_52px] items-center gap-3">
                                <span className="text-xs font-semibold tabular-nums text-zinc-500">
                                    {index + 1}
                                </span>
                                <div className="min-w-0">
                                    <p className="truncate text-xs font-medium text-zinc-950">
                                        {item.name}
                                    </p>
                                    <div className="mt-1 h-1.5 overflow-hidden rounded-full bg-zinc-200">
                                        <div
                                            className="h-1.5 rounded-full bg-brand"
                                            style={{ width: item.score }}
                                        />
                                    </div>
                                </div>
                                <span className="text-right text-xs font-semibold tabular-nums text-zinc-950">
                                    {item.score}
                                </span>
                            </div>
                        ))}
                    </div>
                </div>

                <p className="mt-4 text-[11px] leading-5 text-zinc-500">
                    Ilustrasi perhitungan SAW menggunakan data contoh, bukan hasil pengunjung.
                </p>
            </div>
        </div>
    );
}
