import { Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';

type Option = {
    id: number;
    name: string;
};

type StatusOption = {
    value: string;
    label: string;
};

export type ProductFormValues = {
    id?: number;
    category_id: number | string | null;
    brand_id: number | string | null;
    name: string;
    description: string | null;
    status: string;
    sku: string | null;
    barcode: string | null;
    variant_name: string | null;
    currency: string;
    sale_price: string | number | null;
    list_price: string | number | null;
    quantity: string | number | null;
};

type ProductFormProps = {
    action: string;
    method: 'post' | 'put';
    submitLabel: string;
    categories: Option[];
    brands: Option[];
    statuses: StatusOption[];
    product?: ProductFormValues;
};

type ProductFormData = {
    category_id: string;
    brand_id: string;
    name: string;
    description: string;
    status: string;
    sku: string;
    barcode: string;
    variant_name: string;
    currency: string;
    sale_price: string;
    list_price: string;
    quantity: string;
};

export default function ProductForm({
    action,
    method,
    submitLabel,
    categories,
    brands,
    statuses,
    product,
}: ProductFormProps) {
    const { data, setData, post, put, processing, errors } = useForm<ProductFormData>({
        category_id: stringValue(product?.category_id),
        brand_id: stringValue(product?.brand_id),
        name: product?.name ?? '',
        description: product?.description ?? '',
        status: product?.status ?? 'draft',
        sku: product?.sku ?? '',
        barcode: product?.barcode ?? '',
        variant_name: product?.variant_name ?? '',
        currency: product?.currency ?? 'TRY',
        sale_price: stringValue(product?.sale_price),
        list_price: stringValue(product?.list_price),
        quantity: stringValue(product?.quantity ?? 0),
    });

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        if (method === 'put') {
            put(action);
            return;
        }

        post(action);
    }

    return (
        <section className="max-w-4xl rounded-lg border border-slate-200 bg-white p-6">
            <form className="space-y-6" onSubmit={submit}>
                <div className="grid gap-5 md:grid-cols-2">
                    <Field
                        label="Ürün adı"
                        name="name"
                        value={data.name}
                        error={errors.name}
                        onChange={(value) => setData('name', value)}
                    />

                    <SelectField
                        label="Durum"
                        name="status"
                        value={data.status}
                        error={errors.status}
                        options={statuses}
                        onChange={(value) => setData('status', value)}
                    />

                    <SelectField
                        label="Kategori"
                        name="category_id"
                        value={data.category_id}
                        error={errors.category_id}
                        placeholder="Kategori seçin"
                        options={categories.map((category) => ({
                            value: String(category.id),
                            label: category.name,
                        }))}
                        onChange={(value) => setData('category_id', value)}
                    />

                    <SelectField
                        label="Marka"
                        name="brand_id"
                        value={data.brand_id}
                        error={errors.brand_id}
                        placeholder="Marka seçin"
                        options={brands.map((brand) => ({
                            value: String(brand.id),
                            label: brand.name,
                        }))}
                        onChange={(value) => setData('brand_id', value)}
                    />
                </div>

                <TextArea
                    label="Açıklama"
                    name="description"
                    value={data.description}
                    error={errors.description}
                    onChange={(value) => setData('description', value)}
                />

                <div className="border-t border-slate-200 pt-6">
                    <h2 className="text-base font-semibold text-slate-950">Varyant, fiyat ve stok</h2>
                    <div className="mt-4 grid gap-5 md:grid-cols-2">
                        <Field
                            label="SKU"
                            name="sku"
                            value={data.sku}
                            error={errors.sku}
                            onChange={(value) => setData('sku', value)}
                        />

                        <Field
                            label="Barkod"
                            name="barcode"
                            value={data.barcode}
                            error={errors.barcode}
                            onChange={(value) => setData('barcode', value)}
                        />

                        <Field
                            label="Varyant adı"
                            name="variant_name"
                            value={data.variant_name}
                            error={errors.variant_name}
                            onChange={(value) => setData('variant_name', value)}
                        />

                        <Field
                            label="Para birimi"
                            name="currency"
                            value={data.currency}
                            error={errors.currency}
                            maxLength={3}
                            onChange={(value) => setData('currency', value.toUpperCase())}
                        />

                        <Field
                            label="Satış fiyatı"
                            name="sale_price"
                            type="number"
                            value={data.sale_price}
                            error={errors.sale_price}
                            min="0"
                            step="0.01"
                            onChange={(value) => setData('sale_price', value)}
                        />

                        <Field
                            label="Liste fiyatı"
                            name="list_price"
                            type="number"
                            value={data.list_price}
                            error={errors.list_price}
                            min="0"
                            step="0.01"
                            onChange={(value) => setData('list_price', value)}
                        />

                        <Field
                            label="Stok miktarı"
                            name="quantity"
                            type="number"
                            value={data.quantity}
                            error={errors.quantity}
                            min="0"
                            step="1"
                            onChange={(value) => setData('quantity', value)}
                        />
                    </div>
                </div>

                <div className="flex flex-wrap items-center gap-3 border-t border-slate-200 pt-6">
                    <button
                        className="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-60"
                        type="submit"
                        disabled={processing}
                    >
                        {submitLabel}
                    </button>
                    <Link className="text-sm font-medium text-slate-600 hover:text-slate-950" href="/products">
                        İptal
                    </Link>
                </div>
            </form>
        </section>
    );
}

type FieldProps = {
    label: string;
    name: string;
    value: string;
    error?: string;
    type?: string;
    min?: string;
    step?: string;
    maxLength?: number;
    onChange: (value: string) => void;
};

function Field({ label, name, value, error, type = 'text', min, step, maxLength, onChange }: FieldProps) {
    return (
        <div>
            <label className="block text-sm font-medium text-slate-700" htmlFor={name}>
                {label}
            </label>
            <input
                id={name}
                className="mt-2 w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100"
                type={type}
                min={min}
                step={step}
                maxLength={maxLength}
                value={value}
                onChange={(event) => onChange(event.target.value)}
            />
            {error && <p className="mt-2 text-sm text-red-600">{error}</p>}
        </div>
    );
}

type SelectFieldProps = {
    label: string;
    name: string;
    value: string;
    error?: string;
    placeholder?: string;
    options: StatusOption[];
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
                {placeholder && <option value="">{placeholder}</option>}
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

type TextAreaProps = {
    label: string;
    name: string;
    value: string;
    error?: string;
    onChange: (value: string) => void;
};

function TextArea({ label, name, value, error, onChange }: TextAreaProps) {
    return (
        <div>
            <label className="block text-sm font-medium text-slate-700" htmlFor={name}>
                {label}
            </label>
            <textarea
                id={name}
                className="mt-2 min-h-32 w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100"
                value={value}
                onChange={(event) => onChange(event.target.value)}
            />
            {error && <p className="mt-2 text-sm text-red-600">{error}</p>}
        </div>
    );
}

function stringValue(value: string | number | null | undefined) {
    if (value === null || value === undefined) {
        return '';
    }

    return String(value);
}
