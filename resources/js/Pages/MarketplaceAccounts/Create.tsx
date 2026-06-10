import { Head, Link, useForm, usePage } from '@inertiajs/react';
import type { FormEvent } from 'react';
import AdminLayout from '../../Layouts/AdminLayout';
import type { PageProps } from '../../types';

type MarketplaceOption = {
    id: number;
    code: string;
    name: string;
};

type MarketplaceAccountsCreateProps = PageProps<{
    marketplaces: MarketplaceOption[];
}>;

export default function Create() {
    const { marketplaces } = usePage<MarketplaceAccountsCreateProps>().props;
    const { data, setData, post, processing, errors } = useForm({
        marketplace_id: '',
        name: '',
        seller_id: '',
        api_key: '',
        api_secret: '',
    });

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        post('/marketplace-accounts');
    }

    return (
        <AdminLayout title="Pazaryeri Hesabı Oluştur">
            <Head title="Pazaryeri Hesabı Oluştur" />

            <div className="mb-6">
                <Link className="text-sm font-medium text-blue-700 hover:underline" href="/marketplace-accounts">
                    Pazaryeri hesaplarına dön
                </Link>
            </div>

            <section className="max-w-2xl rounded-lg border border-slate-200 bg-white p-6">
                <form className="space-y-5" onSubmit={submit}>
                    <div>
                        <label className="block text-sm font-medium text-slate-700" htmlFor="marketplace_id">
                            Pazaryeri
                        </label>
                        <select
                            id="marketplace_id"
                            className="mt-2 w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100"
                            value={data.marketplace_id}
                            onChange={(event) => setData('marketplace_id', event.target.value)}
                        >
                            <option value="">Pazaryeri seçin</option>
                            {marketplaces.map((marketplace) => (
                                <option key={marketplace.id} value={marketplace.id}>
                                    {marketplace.name}
                                </option>
                            ))}
                        </select>
                        {errors.marketplace_id && <p className="mt-2 text-sm text-red-600">{errors.marketplace_id}</p>}
                    </div>

                    <Field
                        label="Hesap adı"
                        name="name"
                        value={data.name}
                        error={errors.name}
                        onChange={(value) => setData('name', value)}
                    />

                    <Field
                        label="Satıcı ID"
                        name="seller_id"
                        value={data.seller_id}
                        error={errors.seller_id}
                        onChange={(value) => setData('seller_id', value)}
                    />

                    <Field
                        label="API anahtarı"
                        name="api_key"
                        value={data.api_key}
                        error={errors.api_key}
                        onChange={(value) => setData('api_key', value)}
                    />

                    <Field
                        label="API gizli anahtarı"
                        name="api_secret"
                        type="password"
                        value={data.api_secret}
                        error={errors.api_secret}
                        onChange={(value) => setData('api_secret', value)}
                    />

                    <div className="rounded-md bg-slate-50 p-4 text-sm text-slate-600">
                        Kimlik bilgileri şifrelenmiş olarak saklanır ve liste ekranında gösterilmez.
                    </div>

                    <button
                        className="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-60"
                        type="submit"
                        disabled={processing}
                    >
                        Hesabı Kaydet
                    </button>
                </form>
            </section>
        </AdminLayout>
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
            />
            {error && <p className="mt-2 text-sm text-red-600">{error}</p>}
        </div>
    );
}
