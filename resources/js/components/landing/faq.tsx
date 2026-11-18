import { ChevronDown } from 'lucide-react';
import { useState } from 'react';
import { cn } from '@/lib/utils';
import { SectionHeading } from './section-heading';

const items = [
    {
        question: 'Apa itu TechMind?',
        answer: 'TechMind adalah aplikasi yang membantu mahasiswa mengenali potensi diri dan mengeksplorasi pilihan karier di bidang teknologi informasi.',
    },
    {
        question: 'Siapa yang dapat menggunakan TechMind?',
        answer: 'Mahasiswa atau siapa pun yang ingin mengeksplorasi jalur karier IT dapat menggunakan TechMind.',
    },
    {
        question: 'Apakah harus login?',
        answer: 'Tidak. Pengunjung dapat mulai menggunakan TechMind tanpa membuat akun.',
    },
    {
        question: 'Berapa lama proses pengisian?',
        answer: 'Waktu pengisian bergantung pada kecepatan pengguna. Pertanyaan dirancang agar dapat dijawab secara singkat.',
    },
    {
        question: 'Apakah hasil menjamin karier tertentu?',
        answer: 'Tidak. Hasil merupakan indeks kecocokan yang membantu eksplorasi, bukan jaminan karier atau probabilitas keberhasilan.',
    },
    {
        question: 'Apakah hasil dapat berubah jika jawaban berubah?',
        answer: 'Ya. Hasil mengikuti jawaban yang diberikan pengguna.',
    },
];

export function Faq() {
    const [openIndex, setOpenIndex] = useState<number | null>(0);

    return (
        <section
            id="faq"
            className="scroll-mt-20 border-t border-zinc-200/80 bg-zinc-50/80"
        >
            <div className="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:py-24">
                <SectionHeading
                    eyebrow="FAQ"
                    title="Pertanyaan yang sering diajukan"
                />

                <div className="mt-10 space-y-2.5">
                    {items.map((item, index) => {
                        const isOpen = openIndex === index;

                        return (
                            <div
                                key={item.question}
                                className="overflow-hidden rounded-[20px] border border-zinc-200/80 bg-white shadow-[0_1px_2px_rgba(15,23,42,0.04),0_10px_20px_-18px_rgba(15,23,42,0.25)]"
                            >
                                <button
                                    type="button"
                                    className="flex w-full items-center justify-between gap-4 px-5 py-4 text-left text-sm font-semibold text-zinc-950 transition-colors hover:bg-zinc-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand/50"
                                    aria-expanded={isOpen}
                                    aria-controls={`faq-panel-${index}`}
                                    onClick={() =>
                                        setOpenIndex((current) =>
                                            current === index ? null : index,
                                        )
                                    }
                                >
                                    <span>{item.question}</span>
                                    <ChevronDown
                                        className={cn(
                                            'size-4 shrink-0 text-zinc-400 transition-transform duration-200',
                                            isOpen && 'rotate-180',
                                        )}
                                        aria-hidden="true"
                                    />
                                </button>
                                <div
                                    id={`faq-panel-${index}`}
                                    className={cn(
                                        'grid transition-all duration-300 ease-out',
                                        isOpen
                                            ? 'grid-rows-[1fr] opacity-100'
                                            : 'grid-rows-[0fr] opacity-0',
                                    )}
                                >
                                    <div className="overflow-hidden">
                                        <p className="px-5 pb-4 text-sm leading-relaxed text-zinc-600">
                                            {item.answer}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        );
                    })}
                </div>
            </div>
        </section>
    );
}
