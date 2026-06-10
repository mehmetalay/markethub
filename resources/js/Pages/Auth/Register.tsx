import { Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';

export default function Register() {
    const { data, setData, post, processing, errors } = useForm({
        tenant_name: '',
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        post('/register');
    }

    return (
        <main className="flex min-h-screen items-center justify-center bg-slate-50 px-6 py-10">
            <section className="w-full max-w-lg rounded-lg border border-slate-200 bg-white p-8 shadow-sm">
                <h1 className="text-2xl font-semibold text-slate-950">MarketHub çalışma alanı oluştur</h1>
                <p className="mt-2 text-sm text-slate-500">
                    İşletmeniz için bir çalışma alanı ve yönetici kullanıcı oluşturun.
                </p>

                <form className="mt-8 space-y-5" onSubmit={submit}>
                    <Field
                        label="Çalışma alanı adı"
                        name="tenant_name"
                        value={data.tenant_name}
                        error={errors.tenant_name}
                        onChange={(value) => setData('tenant_name', value)}
                    />
                    <Field
                        label="Adınız"
                        name="name"
                        value={data.name}
                        error={errors.name}
                        onChange={(value) => setData('name', value)}
                    />
                    <Field
                        label="E-posta"
                        name="email"
                        type="email"
                        value={data.email}
                        error={errors.email}
                        onChange={(value) => setData('email', value)}
                    />
                    <Field
                        label="Şifre"
                        name="password"
                        type="password"
                        value={data.password}
                        error={errors.password}
                        onChange={(value) => setData('password', value)}
                    />
                    <Field
                        label="Şifreyi onayla"
                        name="password_confirmation"
                        type="password"
                        value={data.password_confirmation}
                        error={errors.password_confirmation}
                        onChange={(value) => setData('password_confirmation', value)}
                    />

                    <button
                        className="w-full rounded-md bg-blue-600 px-4 py-2 font-medium text-white hover:bg-blue-700 disabled:opacity-60"
                        type="submit"
                        disabled={processing}
                    >
                        Çalışma Alanı Oluştur
                    </button>
                </form>

                <p className="mt-6 text-center text-sm text-slate-500">
                    Zaten hesabınız var mı?{' '}
                    <Link className="font-medium text-blue-700 hover:underline" href="/login">
                        Giriş yap
                    </Link>
                </p>
            </section>
        </main>
    );
}

type FieldProps = {
    label: string;
    name: string;
    value: string;
    error?: string;
    type?: string;
    onChange: (value: string) => void;
};

function Field({ label, name, value, error, type = 'text', onChange }: FieldProps) {
    return (
        <div>
            <label className="block text-sm font-medium text-slate-700" htmlFor={name}>
                {label}
            </label>
            <input
                id={name}
                className="mt-2 w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100"
                type={type}
                value={value}
                onChange={(event) => onChange(event.target.value)}
                autoComplete={name}
            />
            {error && <p className="mt-2 text-sm text-red-600">{error}</p>}
        </div>
    );
}
