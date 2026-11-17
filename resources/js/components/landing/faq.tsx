import { ChevronDown } from 'lucide-react';

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
    return (
        <section
            id="faq"
            className="scroll-mt-20 border-t border-zinc-200/80 bg-zinc-50/80"
        >
            <div className="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:py-20">
                <div className="max-w-2xl">
                    <p className="text-[11px] font-semibold tracking-[0.08em] text-brand uppercase">
                        FAQ
                    </p>
                    <h2 className="mt-3 text-3xl font-semibold tracking-[-0.025em] text-zinc-950 sm:text-4xl">
                        Pertanyaan yang sering diajukan
                    </h2>
                </div>

                <div className="mt-10 space-y-2.5">
                    {items.map((item) => (
                        <details
                            key={item.question}
                            className="group rounded-xl border border-zinc-200/80 bg-white px-5 py-4 shadow-[0_1px_2px_rgba(0,0,0,0.03),0_1px_3px_rgba(0,0,0,0.02)] transition-all duration-200 open:border-zinc-300 hover:border-zinc-300"
                        >
                            <summary className="flex cursor-pointer list-none items-center justify-between gap-4 text-sm font-semibold text-zinc-950 focus-visible:ring-2 focus-visible:ring-indigo-500/40 focus-visible:ring-offset-2 focus-visible:outline-none">
                                <span>{item.question}</span>
                                <ChevronDown
                                    className="size-4 shrink-0 text-zinc-400 transition-transform duration-200 group-open:rotate-180"
                                    aria-hidden="true"
                                />
                            </summary>
                            <p className="mt-3 max-w-3xl text-sm leading-relaxed text-zinc-600">
                                {item.answer}
                            </p>
                        </details>
                    ))}
                </div>
            </div>
        </section>
    );
}
