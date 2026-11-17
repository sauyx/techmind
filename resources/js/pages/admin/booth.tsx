import { Head } from '@inertiajs/react';
import {
    Check,
    Copy,
    Database,
    Download,
    FlaskConical,
    QrCode,
    Server,
} from 'lucide-react';
import QRCodeGenerator from 'qrcode';
import { useEffect, useState } from 'react';
import AdminLayout from '@/layouts/admin-layout';
import { Button } from '@/components/ui/button';

type Status = {
    active_criteria: number;
    active_careers: number;
    ai_driver: string;
    ai_mode: string;
    total_sessions: number;
    app_url: string;
    demo_mode: boolean;
    database_ready: boolean;
};

type Props = {
    status: Status;
};

export default function AdminBooth({ status }: Props) {
    const [publicUrl, setPublicUrl] = useState(status.app_url);
    const [qrDataUrl, setQrDataUrl] = useState('');
    const [copied, setCopied] = useState(false);

    useEffect(() => {
        const origin = window.location.origin;
        setPublicUrl(origin);

        QRCodeGenerator.toDataURL(origin, {
            width: 1024,
            margin: 2,
            color: { dark: '#18181B', light: '#FFFFFF' },
        })
            .then((dataUrl) => setQrDataUrl(dataUrl))
            .catch(() => setQrDataUrl(''));
    }, []);

    async function copyUrl() {
        try {
            await navigator.clipboard.writeText(publicUrl);
            setCopied(true);
            window.setTimeout(() => setCopied(false), 2000);
        } catch {
            setCopied(false);
        }
    }

    function downloadQr() {
        if (!qrDataUrl) {
            return;
        }

        const anchor = document.createElement('a');
        anchor.href = qrDataUrl;
        anchor.download = 'techmind-booth-qr.png';
        anchor.click();
    }

    return (
        <>
            <Head title="QR & Booth" />
            <AdminLayout>
                <div className="space-y-6">
                    <div>
                        <p className="text-[11px] font-semibold tracking-[0.08em] text-brand uppercase">
                            Booth
                        </p>
                        <h1 className="mt-2 text-2xl font-semibold tracking-[-0.025em] text-zinc-950">
                            QR & Booth
                        </h1>
                        <p className="mt-1 text-sm leading-relaxed text-zinc-600">
                            Siapkan akses pengunjung dan periksa kesiapan sistem
                            sebelum pameran.
                        </p>
                    </div>

                    <div className="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
                        <section className="overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-[0_1px_2px_rgba(0,0,0,0.03),0_1px_3px_rgba(0,0,0,0.02)]">
                            <div className="flex items-center gap-3 border-b border-zinc-200/80 bg-zinc-50/60 px-6 py-4">
                                <span className="flex size-10 items-center justify-center rounded-lg border border-zinc-200/80 bg-white text-brand shadow-sm">
                                    <QrCode className="size-5" />
                                </span>
                                <div>
                                    <h2 className="text-base font-semibold tracking-[-0.015em] text-zinc-950">
                                        Kartu QR Pameran
                                    </h2>
                                    <p className="mt-0.5 text-xs leading-relaxed text-zinc-600">
                                        QR dibuat lokal di browser. Tidak ada
                                        permintaan ke layanan pihak ketiga.
                                    </p>
                                </div>
                            </div>

                            <div className="p-6">
                                <div className="mx-auto flex max-w-sm flex-col items-center gap-4 rounded-2xl border border-zinc-200/80 bg-zinc-50/60 p-6">
                                    <div className="rounded-xl border border-zinc-200/80 bg-white p-4 shadow-sm">
                                        {qrDataUrl ? (
                                            <img
                                                src={qrDataUrl}
                                                alt="QR Code halaman TechMind"
                                                className="h-56 w-56"
                                            />
                                        ) : (
                                            <div className="flex h-56 w-56 items-center justify-center text-sm text-zinc-500">
                                                Menyiapkan QR...
                                            </div>
                                        )}
                                    </div>
                                    <div className="w-full rounded-lg border border-zinc-200/80 bg-white px-3 py-2 text-center text-xs font-medium text-zinc-700 tabular-nums">
                                        {publicUrl}
                                    </div>
                                </div>

                                <div className="mt-5 flex flex-wrap gap-3">
                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={copyUrl}
                                        className="gap-1.5"
                                    >
                                        {copied ? (
                                            <Check className="size-4" />
                                        ) : (
                                            <Copy className="size-4" />
                                        )}
                                        {copied
                                            ? 'URL tersalin'
                                            : 'Salin URL Pameran'}
                                    </Button>
                                    <Button
                                        type="button"
                                        onClick={downloadQr}
                                        disabled={!qrDataUrl}
                                        className="gap-1.5"
                                    >
                                        <Download className="size-4" />
                                        Unduh QR (PNG)
                                    </Button>
                                </div>
                            </div>
                        </section>

                        <section className="rounded-xl border border-zinc-200/80 bg-white p-6 shadow-[0_1px_2px_rgba(0,0,0,0.03),0_1px_3px_rgba(0,0,0,0.02)]">
                            <div className="flex items-center gap-2.5">
                                <span className="flex size-9 items-center justify-center rounded-lg border border-zinc-200/80 bg-zinc-50 text-brand">
                                    <Server className="size-4" />
                                </span>
                                <div>
                                    <h2 className="text-base font-semibold tracking-[-0.015em] text-zinc-950">
                                        Status Sistem
                                    </h2>
                                    <p className="text-xs text-zinc-500">
                                        Kesiapan sebelum pameran
                                    </p>
                                </div>
                            </div>

                            <div className="mt-4 space-y-2">
                                <StatusRow
                                    icon={<Database className="size-4" />}
                                    label="Koneksi database"
                                    value={
                                        status.database_ready ? 'Siap' : 'Gagal'
                                    }
                                    ok={status.database_ready}
                                />
                                <StatusRow
                                    icon={<Server className="size-4" />}
                                    label="Driver AI"
                                    value={status.ai_mode}
                                    ok
                                    hint={status.ai_driver}
                                />
                                <StatusRow
                                    icon={<FlaskConical className="size-4" />}
                                    label="Mode Demo"
                                    value={
                                        status.demo_mode ? 'Aktif' : 'Nonaktif'
                                    }
                                    ok={status.demo_mode}
                                />
                            </div>

                            <dl className="mt-6 grid grid-cols-3 gap-3 border-t border-zinc-200/80 pt-4">
                                <div className="rounded-lg border border-zinc-200/80 bg-zinc-50/60 p-3">
                                    <dt className="text-[11px] font-semibold tracking-[0.08em] text-zinc-500 uppercase">
                                        Kriteria
                                    </dt>
                                    <dd className="mt-1 text-xl font-semibold text-zinc-950 tabular-nums">
                                        {status.active_criteria}
                                    </dd>
                                </div>
                                <div className="rounded-lg border border-zinc-200/80 bg-zinc-50/60 p-3">
                                    <dt className="text-[11px] font-semibold tracking-[0.08em] text-zinc-500 uppercase">
                                        Karier
                                    </dt>
                                    <dd className="mt-1 text-xl font-semibold text-zinc-950 tabular-nums">
                                        {status.active_careers}
                                    </dd>
                                </div>
                                <div className="rounded-lg border border-zinc-200/80 bg-zinc-50/60 p-3">
                                    <dt className="text-[11px] font-semibold tracking-[0.08em] text-zinc-500 uppercase">
                                        Sesi selesai
                                    </dt>
                                    <dd className="mt-1 text-xl font-semibold text-zinc-950 tabular-nums">
                                        {status.total_sessions}
                                    </dd>
                                </div>
                            </dl>
                        </section>
                    </div>
                </div>
            </AdminLayout>
        </>
    );
}

function StatusRow({
    icon,
    label,
    value,
    ok,
    hint,
}: {
    icon: React.ReactNode;
    label: string;
    value: string;
    ok: boolean;
    hint?: string;
}) {
    return (
        <div className="flex items-center justify-between gap-3 rounded-lg border border-zinc-200/80 px-3 py-2.5 transition-colors hover:bg-zinc-50/60">
            <div className="flex min-w-0 items-center gap-2 text-sm text-zinc-700">
                <span className="text-zinc-500">{icon}</span>
                <span className="truncate">{label}</span>
            </div>
            <div className="text-right">
                <span
                    className={`rounded-full px-2.5 py-0.5 text-xs font-medium ${ok ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'}`}
                >
                    {value}
                </span>
                {hint ? (
                    <p className="mt-1 text-[10px] text-zinc-500">{hint}</p>
                ) : null}
            </div>
        </div>
    );
}
