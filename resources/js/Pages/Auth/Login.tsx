import { Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        post('/login');
    }

    return (
        <main className="flex min-h-screen items-center justify-center bg-slate-50 px-6">
            <section className="w-full max-w-md rounded-lg border border-slate-200 bg-white p-8 shadow-sm">
                <h1 className="text-2xl font-semibold text-slate-950">MarketHub</h1>
                <p className="mt-2 text-sm text-slate-500">
                    Pazaryeri entegrasyon yönetim paneline giriş yapın.
                </p>

                <form className="mt-8 space-y-5" onSubmit={submit}>
                    <div>
                        <label className="block text-sm font-medium text-slate-700" htmlFor="email">
                            E-posta
                        </label>
                        <input
                            id="email"
                            className="mt-2 w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100"
                            type="email"
                            value={data.email}
                            onChange={(event) => setData('email', event.target.value)}
                            autoComplete="email"
                        />
                        {errors.email && <p className="mt-2 text-sm text-red-600">{errors.email}</p>}
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-slate-700" htmlFor="password">
                            Şifre
                        </label>
                        <input
                            id="password"
                            className="mt-2 w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100"
                            type="password"
                            value={data.password}
                            onChange={(event) => setData('password', event.target.value)}
                            autoComplete="current-password"
                        />
                        {errors.password && <p className="mt-2 text-sm text-red-600">{errors.password}</p>}
                    </div>

                    <label className="flex items-center gap-2 text-sm text-slate-600">
                        <input
                            type="checkbox"
                            checked={data.remember}
                            onChange={(event) => setData('remember', event.target.checked)}
                        />
                        Beni hatırla
                    </label>

                    <button
                        className="w-full rounded-md bg-blue-600 px-4 py-2 font-medium text-white hover:bg-blue-700 disabled:opacity-60"
                        type="submit"
                        disabled={processing}
                    >
                        Giriş Yap
                    </button>
                </form>

                <p className="mt-6 text-center text-sm text-slate-500">
                    Yeni çalışma alanı mı oluşturuyorsunuz?{' '}
                    <Link className="font-medium text-blue-700 hover:underline" href="/register">
                        Çalışma alanı oluştur
                    </Link>
                </p>
            </section>
        </main>
    );
}
