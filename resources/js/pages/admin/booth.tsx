import { Head } from '@inertiajs/react';
import { Check, Copy, Database, Download, FlaskConical, QrCode, Server } from 'lucide-react';
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
                        <h1 className="text-2xl font-semibold tracking-tight text-zinc-950">QR & Booth</h1>
                        <p className="mt-1 text-sm text-zinc-600">
                            Siapkan akses pengunjung dan periksa kesiapan sistem sebelum pameran.
                        </p>
                    </div>

                    <div className="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
                        <section className="rounded-xl border border-zinc-200 bg-white p-6">
                            <div className="flex items-center gap-2">
                                <QrCode className="size-5 text-brand" />
                                <h2 className="text-lg font-semibold text-zinc-950">Kartu QR Pameran</h2>
                            </div>
                            <p className="mt-1 text-sm text-zinc-600">
                                QR dibuat lokal di browser. Tidak ada permintaan ke layanan pihak ketiga.
                            </p>

                            <div className="mt-6 flex flex-col items-center gap-4 rounded-xl border border-zinc-200 bg-zinc-50 p-6">
                                <div className="rounded-lg border border-zinc-200 bg-white p-3">
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
                                <p className="max-w-full break-all text-center text-sm font-medium tabular-nums text-zinc-700">
                                    {publicUrl}
                                </p>
                            </div>

                            <div className="mt-5 flex flex-wrap gap-3">
                                <Button type="button" variant="outline" onClick={copyUrl}>
                                    {copied ? <Check className="size-4" /> : <Copy className="size-4" />}
                                    {copied ? 'URL tersalin' : 'Salin URL Pameran'}
                                </Button>
                                <Button type="button" onClick={downloadQr} disabled={!qrDataUrl}>
                                    <Download className="size-4" />
                                    Unduh QR (PNG)
                                </Button>
                            </div>
                        </section>

                        <section className="rounded-xl border border-zinc-200 bg-white p-6">
                            <div className="flex items-center gap-2">
                                <Server className="size-5 text-brand" />
                                <h2 className="text-lg font-semibold text-zinc-950">Status Sistem</h2>
                            </div>

                            <div className="mt-4 space-y-2">
                                <StatusRow
                                    icon={<Database className="size-4" />}
                                    label="Koneksi database"
                                    value={status.database_ready ? 'Siap' : 'Gagal'}
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
                                    value={status.demo_mode ? 'Aktif' : 'Nonaktif'}
                                    ok={status.demo_mode}
                                />
                            </div>

                            <dl className="mt-6 grid grid-cols-3 gap-3 border-t border-zinc-200 pt-4">
                                <div>
                                    <dt className="text-xs text-zinc-500">Kriteria</dt>
                                    <dd className="mt-1 text-lg font-semibold tabular-nums text-zinc-950">
                                        {status.active_criteria}
                                    </dd>
                                </div>
                                <div>
                                    <dt className="text-xs text-zinc-500">Karier</dt>
                                    <dd className="mt-1 text-lg font-semibold tabular-nums text-zinc-950">
                                        {status.active_careers}
                                    </dd>
                                </div>
                                <div>
                                    <dt className="text-xs text-zinc-500">Sesi selesai</dt>
                                    <dd className="mt-1 text-lg font-semibold tabular-nums text-zinc-950">
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
        <div className="flex items-center justify-between gap-3 rounded-lg border border-zinc-200 px-3 py-2.5">
            <div className="flex min-w-0 items-center gap-2 text-sm text-zinc-700">
                <span className="text-zinc-500">{icon}</span>
                <span className="truncate">{label}</span>
            </div>
            <div className="text-right">
                <span
                    className={`rounded-md px-2 py-0.5 text-xs font-medium ${ok ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'}`}
                >
                    {value}
                </span>
                {hint ? <p className="mt-1 text-[10px] text-zinc-500">{hint}</p> : null}
            </div>
        </div>
    );
}
