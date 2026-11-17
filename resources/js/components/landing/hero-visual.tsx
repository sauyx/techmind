export function HeroVisual() {
    return (
        <div className="relative mx-auto w-full max-w-md">
            <div className="absolute -inset-4 rounded-3xl bg-brand/5 blur-2xl" aria-hidden="true" />
            <div className="relative rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
                <svg
                    viewBox="0 0 360 280"
                    className="h-auto w-full"
                    role="img"
                    aria-label="Ilustrasi eksplorasi jalur karier IT"
                >
                    <rect x="20" y="20" width="320" height="240" rx="18" fill="#fafafa" />
                    <path
                        d="M60 200 C90 150 120 120 160 110 S230 95 300 70"
                        fill="none"
                        stroke="#4F46E5"
                        strokeWidth="3"
                        strokeLinecap="round"
                    />
                    <circle cx="60" cy="200" r="6" fill="#4F46E5" />
                    <circle cx="160" cy="110" r="6" fill="#4F46E5" />
                    <circle cx="300" cy="70" r="6" fill="#4F46E5" />
                    <rect x="45" y="45" width="90" height="42" rx="8" fill="#ffffff" stroke="#e4e4e7" />
                    <rect x="225" y="150" width="90" height="42" rx="8" fill="#ffffff" stroke="#e4e4e7" />
                    <rect x="135" y="118" width="90" height="42" rx="8" fill="#ffffff" stroke="#e4e4e7" />
                </svg>
            </div>
        </div>
    );
}
