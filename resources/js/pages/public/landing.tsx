import { Head } from '@inertiajs/react';
import { CareerGrid } from '@/components/landing/career-grid';
import { Faq } from '@/components/landing/faq';
import { Footer } from '@/components/landing/footer';
import { Hero } from '@/components/landing/hero';
import { HowItWorks } from '@/components/landing/how-it-works';
import { LandingNav } from '@/components/landing/landing-nav';
import { Trust } from '@/components/landing/trust';
import { ValueProp } from '@/components/landing/value-prop';

export default function Welcome() {
    return (
        <>
            <Head title="TechMind" />
            <div className="min-h-screen overflow-x-hidden bg-zinc-50/80 text-zinc-950">
                <LandingNav />
                <main>
                    <Hero />
                    <ValueProp />
                    <CareerGrid />
                    <HowItWorks />
                    <Trust />
                    <Faq />
                </main>
                <Footer />
            </div>
        </>
    );
}
