import { Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { useState } from 'react';

export type MappingOption = {
    id: number;
    name: string;
    marketplace?: string;
    code?: string;
};

export type StatusOption = {
    value: string;
    label: string;
};

export type MappingRow = {
    id: number;
    status: string;
    notes: string | null;
    local: MappingOption;
    marketplaceTarget: MappingOption & {
        path?: string | null;
    };
};

type MappingPageProps = {
    description: string;
    emptyTitle: string;
    localLabel: string;
    marketplaceLabel: string;
    localField: string;
    marketplaceField: string;
    storeAction: string;
    updateAction: (id: number) => string;
    mappings: MappingRow[];
    localOptions: MappingOption[];
    marketplaceOptions: MappingOption[];
    statuses: StatusOption[];
};

type FormData = Record<string, string>;

export default function MappingPage({
    description,
    emptyTitle,
    localLabel,
    marketplaceLabel,
    localField,
    marketplaceField,
    storeAction,
    updateAction,
    mappings,
    localOptions,
    marketplaceOptions,
    statuses,
}: MappingPageProps) {
    const [editingId, setEditingId] = useState<number | null>(null);
    const { data, setData, post, put, processing, errors, reset, clearErrors } = useForm<FormData>({
        [localField]: '',
        [marketplaceField]: '',
        status: 'mapped',
        notes: '',
    });

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        if (editingId) {
            put(updateAction(editingId), {
                onSuccess: () => {
                    resetForm();
                },
            });
            return;
        }

        post(storeAction, {
            onSuccess: () => {
                resetForm();
            },
        });
    }

    function edit(mapping: MappingRow) {
        clearErrors();
        setEditingId(mapping.id);
        setData({
            [localField]: String(mapping.local.id),
            [marketplaceField]: String(mapping.marketplaceTarget.id),
            status: mapping.status,
            notes: mapping.notes ?? '',
        });
    }

    function resetForm() {
        setEditingId(null);
        reset(localField, marketplaceField, 'status', 'notes');
    }

    return (
        <>
            <div className="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <p className="max-w-2xl text-sm text-slate-600">{description}</p>
                <div className="flex flex-wrap gap-2">
                    <Link
                        className="rounded-md px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100"
                        href="/marketplace-mappings/categories"
                    >
                        Kategoriler
                    </Link>
                    <Link
                        className="rounded-md px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100"
                        href="/marketplace-mappings/brands"
                    >
                        Markalar
                    </Link>
                    <Link
                        className="rounded-md px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100"
                        href="/marketplace-mappings/attributes"
                    >
                        Attribute'lar
                    </Link>
                </div>
            </div>

            <section className="mb-6 rounded-lg border border-slate-200 bg-white p-6">
                <h2 className="mb-4 text-base font-semibold text-slate-950">
                    {editingId ? 'Eşleştirmeyi Güncelle' : 'Yeni Eşleştirme'}
                </h2>
                <form className="grid gap-5 lg:grid-cols-2" onSubmit={submit}>
                    <SelectField
                        label={localLabel}
                        name={localField}
                        value={data[localField] ?? ''}
                        error={errors[localField]}
                        placeholder={`${localLabel} seçin`}
                        options={localOptions}
                        onChange={(value) => setData(localField, value)}
                    />

                    <SelectField
                        label={marketplaceLabel}
                        name={marketplaceField}
                        value={data[marketplaceField] ?? ''}
                        error={errors[marketplaceField]}
                        placeholder={`${marketplaceLabel} seçin`}
                        options={marketplaceOptions}
                        onChange={(value) => setData(marketplaceField, value)}
                    />

                    <SelectField
                        label="Durum"
                        name="status"
                        value={data.status ?? 'mapped'}
                        error={errors.status}
                        options={statuses}
                        onChange={(value) => setData('status', value)}
                    />

                    <div>
                        <label className="block text-sm font-medium text-slate-700" htmlFor="notes">
                            Not
                        </label>
                        <input
                            id="notes"
                            className="mt-2 w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100"
                            value={data.notes ?? ''}
                            onChange={(event) => setData('notes', event.target.value)}
                        />
                        {errors.notes && <p className="mt-2 text-sm text-red-600">{errors.notes}</p>}
                    </div>

                    <div className="flex flex-wrap items-center gap-3 lg:col-span-2">
                        <button
                            className="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-60"
                            type="submit"
                            disabled={processing}
                        >
                            {editingId ? 'Güncelle' : 'Kaydet'}
                        </button>
                        {editingId && (
                            <button
                                className="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100"
                                type="button"
                                onClick={resetForm}
                            >
                                Vazgeç
                            </button>
                        )}
                    </div>
                </form>
            </section>

            <section className="overflow-hidden rounded-lg border border-slate-200 bg-white">
                {mappings.length > 0 ? (
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm">
                            <thead className="border-b border-slate-200 bg-slate-50 text-slate-600">
                                <tr>
                                    <th className="px-4 py-3 font-medium">{localLabel}</th>
                                    <th className="px-4 py-3 font-medium">{marketplaceLabel}</th>
                                    <th className="px-4 py-3 font-medium">Pazaryeri</th>
                                    <th className="px-4 py-3 font-medium">Durum</th>
                                    <th className="px-4 py-3 font-medium">Not</th>
                                    <th className="px-4 py-3 font-medium">İşlem</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {mappings.map((mapping) => (
                                    <tr key={mapping.id}>
                                        <td className="px-4 py-3 font-medium text-slate-950">{optionName(mapping.local)}</td>
                                        <td className="px-4 py-3 text-slate-700">
                                            {mapping.marketplaceTarget.path ?? optionName(mapping.marketplaceTarget)}
                                        </td>
                                        <td className="px-4 py-3 text-slate-700">{mapping.marketplaceTarget.marketplace}</td>
                                        <td className="px-4 py-3 text-slate-700">{statusLabel(mapping.status, statuses)}</td>
                                        <td className="px-4 py-3 text-slate-700">{mapping.notes ?? '-'}</td>
                                        <td className="px-4 py-3">
                                            <button
                                                className="text-sm font-medium text-blue-700 hover:underline"
                                                type="button"
                                                onClick={() => edit(mapping)}
                                            >
                                                Düzenle
                                            </button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                ) : (
                    <div className="px-6 py-10 text-center">
                        <h2 className="text-lg font-semibold text-slate-950">{emptyTitle}</h2>
                        <p className="mt-2 text-sm text-slate-600">İlk eşleştirmeyi form üzerinden oluşturabilirsiniz.</p>
                    </div>
                )}
            </section>
        </>
    );
}

type SelectFieldProps = {
    label: string;
    name: string;
    value: string;
    error?: string;
    placeholder?: string;
    options: MappingOption[] | StatusOption[];
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
                    <option key={optionValue(option)} value={optionValue(option)}>
                        {optionLabel(option)}
                    </option>
                ))}
            </select>
            {error && <p className="mt-2 text-sm text-red-600">{error}</p>}
        </div>
    );
}

function optionValue(option: MappingOption | StatusOption) {
    return 'value' in option ? option.value : String(option.id);
}

function optionLabel(option: MappingOption | StatusOption) {
    if ('value' in option) {
        return option.label;
    }

    return option.marketplace ? `${option.name} - ${option.marketplace}` : optionName(option);
}

function optionName(option: MappingOption) {
    return option.code ? `${option.name} (${option.code})` : option.name;
}

function statusLabel(status: string, statuses: StatusOption[]) {
    return statuses.find((option) => option.value === status)?.label ?? status;
}
