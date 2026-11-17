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
        <section id="faq" className="scroll-mt-20 border-t border-zinc-200 bg-zinc-50">
            <div className="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:py-20">
                <div className="max-w-2xl">
                    <h2 className="text-3xl font-semibold tracking-tight text-zinc-950 sm:text-4xl">
                        Pertanyaan yang sering diajukan
                    </h2>
                </div>

                <div className="mt-10 space-y-3">
                    {items.map((item) => (
                        <details
                            key={item.question}
                            className="group rounded-xl border border-zinc-200 bg-white p-5"
                        >
                            <summary className="cursor-pointer list-none text-sm font-semibold text-zinc-950 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2">
                                {item.question}
                            </summary>
                            <p className="mt-3 text-sm leading-6 text-zinc-600">
                                {item.answer}
                            </p>
                        </details>
                    ))}
                </div>
            </div>
        </section>
    );
}
