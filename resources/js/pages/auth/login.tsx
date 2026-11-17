import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/login';
import PasskeyVerify from '@/components/passkey-verify';

type Props = {
    status?: string;
    canResetPassword?: boolean;
};

export default function Login({ status }: Props) {
    return (
        <>
            <Head title="Masuk" />

            <PasskeyVerify />

            <div className="flex min-h-svh items-center justify-center bg-zinc-50/80 px-4 py-10 text-zinc-900">
                <div className="w-full max-w-sm">
                    <div className="flex flex-col items-center gap-6">
                        <a
                            href="/"
                            className="flex items-center gap-2.5 rounded-lg focus-visible:ring-2 focus-visible:ring-indigo-500/40 focus-visible:ring-offset-2 focus-visible:outline-none"
                        >
                            <svg
                                viewBox="0 0 28 28"
                                fill="none"
                                aria-hidden="true"
                                className="size-8"
                            >
                                <rect
                                    x="1"
                                    y="1"
                                    width="26"
                                    height="26"
                                    rx="8"
                                    className="fill-zinc-950"
                                />
                                <path
                                    d="M8.5 9.75h11M14 9.75v8.5"
                                    stroke="white"
                                    strokeWidth="2"
                                    strokeLinecap="round"
                                />
                                <circle
                                    cx="20"
                                    cy="19"
                                    r="3"
                                    fill="white"
                                    fillOpacity="0.92"
                                />
                                <circle
                                    cx="20"
                                    cy="19"
                                    r="1.1"
                                    className="fill-zinc-950"
                                />
                            </svg>
                            <div className="flex flex-col leading-none">
                                <span className="text-lg font-semibold tracking-[-0.025em] text-zinc-950">
                                    TechMind
                                </span>
                                <span className="mt-1 text-[11px] font-semibold tracking-[0.08em] text-indigo-700 uppercase">
                                    Admin
                                </span>
                            </div>
                        </a>

                        <div className="w-full rounded-2xl border border-zinc-200/80 bg-white p-6 shadow-[0_1px_2px_rgba(0,0,0,0.03),0_1px_3px_rgba(0,0,0,0.02)]">
                            <div className="space-y-1.5 text-center">
                                <h1 className="text-xl font-semibold tracking-[-0.025em] text-zinc-950">
                                    Masuk ke TechMind
                                </h1>
                                <p className="text-sm leading-relaxed text-zinc-600">
                                    Gunakan akun admin untuk mengelola TechMind.
                                </p>
                            </div>

                            <Form
                                {...store.form()}
                                resetOnSuccess={['password']}
                                className="mt-6 flex flex-col gap-5"
                            >
                                {({ processing, errors }) => (
                                    <>
                                        <div className="grid gap-2">
                                            <Label htmlFor="email">Email</Label>
                                            <Input
                                                id="email"
                                                type="email"
                                                name="email"
                                                required
                                                autoFocus
                                                tabIndex={1}
                                                autoComplete="email"
                                                placeholder="email@example.com"
                                                className="h-10 rounded-lg border-zinc-300 bg-white px-3 focus-visible:ring-2 focus-visible:ring-indigo-500/40"
                                            />
                                            <InputError
                                                message={errors.email}
                                            />
                                        </div>

                                        <div className="grid gap-2">
                                            <div className="flex items-center justify-between">
                                                <Label htmlFor="password">
                                                    Password
                                                </Label>
                                            </div>
                                            <PasswordInput
                                                id="password"
                                                name="password"
                                                required
                                                tabIndex={2}
                                                autoComplete="current-password"
                                                placeholder="Password"
                                                className="h-10 rounded-lg border-zinc-300 bg-white px-3 focus-visible:ring-2 focus-visible:ring-indigo-500/40"
                                            />
                                            <InputError
                                                message={errors.password}
                                            />
                                        </div>

                                        <div className="flex items-center space-x-3">
                                            <Checkbox
                                                id="remember"
                                                name="remember"
                                                tabIndex={3}
                                            />
                                            <Label htmlFor="remember">
                                                Ingat saya
                                            </Label>
                                        </div>

                                        <Button
                                            type="submit"
                                            className="h-10 w-full gap-2 rounded-lg bg-zinc-950 text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)] hover:bg-zinc-800 focus-visible:ring-2 focus-visible:ring-indigo-500/40"
                                            tabIndex={4}
                                            disabled={processing}
                                            data-test="login-button"
                                        >
                                            {processing && <Spinner />}
                                            Masuk
                                        </Button>
                                    </>
                                )}
                            </Form>
                        </div>

                        {status && (
                            <div className="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-center text-sm font-medium text-emerald-700">
                                {status}
                            </div>
                        )}

                        <a
                            href="/"
                            className="text-sm font-medium text-zinc-600 transition-colors hover:text-zinc-950 focus-visible:ring-2 focus-visible:ring-indigo-500/40 focus-visible:ring-offset-2 focus-visible:outline-none"
                        >
                            Kembali ke halaman utama
                        </a>
                    </div>
                </div>
            </div>
        </>
    );
}

Login.layout = null;
