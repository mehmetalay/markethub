import { Head, Link, useForm, usePage } from '@inertiajs/react';
import type { FormEvent } from 'react';
import AdminLayout from '../../Layouts/AdminLayout';
import type { PageProps } from '../../types';

type ProductOption = {
    id: number;
    name: string;
};

type MarketplaceAccountOption = {
    id: number;
    name: string;
    marketplace: string;
};

type ListingsCreateProps = PageProps<{
    products: ProductOption[];
    marketplaceAccounts: MarketplaceAccountOption[];
}>;

export default function Create() {
    const { products, marketplaceAccounts } = usePage<ListingsCreateProps>().props;
    const { data, setData, post, processing, errors } = useForm({
        product_id: '',
        marketplace_account_id: '',
    });

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        post('/listings');
    }

    return (
        <AdminLayout title="İlan Oluştur">
            <Head title="İlan Oluştur" />

            <div className="mb-6">
                <Link className="text-sm font-medium text-blue-700 hover:underline" href="/listings">
                    İlanlara dön
                </Link>
            </div>

            <section className="max-w-2xl rounded-lg border border-slate-200 bg-white p-6">
                <form className="space-y-5" onSubmit={submit}>
                    <SelectField
                        label="Ürün"
                        name="product_id"
                        value={data.product_id}
                        error={errors.product_id}
                        placeholder="Ürün seçin"
                        options={products.map((product) => ({
                            value: String(product.id),
                            label: product.name,
                        }))}
                        onChange={(value) => setData('product_id', value)}
                    />

                    <SelectField
                        label="Pazaryeri hesabı"
                        name="marketplace_account_id"
                        value={data.marketplace_account_id}
                        error={errors.marketplace_account_id}
                        placeholder="Pazaryeri hesabı seçin"
                        options={marketplaceAccounts.map((account) => ({
                            value: String(account.id),
                            label: `${account.name} - ${account.marketplace}`,
                        }))}
                        onChange={(value) => setData('marketplace_account_id', value)}
                    />

                    <div className="rounded-md bg-slate-50 p-4 text-sm text-slate-600">
                        Bu işlem sadece ilan taslağı ve payload önizleme oluşturur. Pazaryerine ürün gönderimi yapılmaz.
                    </div>

                    <button
                        className="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-60"
                        type="submit"
                        disabled={processing}
                    >
                        İlanı Kaydet
                    </button>
                </form>
            </section>
        </AdminLayout>
    );
}

type SelectFieldProps = {
    label: string;
    name: string;
    value: string;
    error?: string;
    placeholder: string;
    options: Array<{
        value: string;
        label: string;
    }>;
    onChange: (value: string) => void;
};

function SelectField({ label, name, value, error, placeholder, options, onChange }: SelectFieldProps) {
    return (
        <div>
            <label className="block text-sm font-medium text-slate-700" htmlFor={name}>
                {label}
            </label>
            <select
                id={name}
                className="mt-2 w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100"
                value={value}
                onChange={(event) => onChange(event.target.value)}
            >
                <option value="">{placeholder}</option>
                {options.map((option) => (
                    <option key={option.value} value={option.value}>
                        {option.label}
                    </option>
                ))}
            </select>
            {error && <p className="mt-2 text-sm text-red-600">{error}</p>}
        </div>
    );
}
