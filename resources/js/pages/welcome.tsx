import { Head } from '@inertiajs/react';
import PublicLayout from '@/layouts/public-layout';

export default function Welcome() {
    return (
        <>
            <Head title="TechMind" />
            <PublicLayout>
                <section className="py-12 text-center">
                    <h1 className="text-3xl font-semibold tracking-tight">TechMind</h1>
                    <p className="mx-auto mt-3 max-w-xl text-muted-foreground">
                        Sistem rekomendasi karier IT berbasis Sistem Penunjang Keputusan. Isi kuesioner singkat, dapatkan ranking jalur karier yang cocok untukmu.
                    </p>
                    <button
                        type="button"
                        className="mt-6 inline-flex items-center justify-center rounded-lg bg-brand px-5 py-2.5 text-sm font-medium text-white transition hover:opacity-90"
                    >
                        Mulai Tes
                    </button>
                </section>
            </PublicLayout>
        </>
    );
}
