import { Head, Link, usePage } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import type { PageProps } from '../../types';
import { formatDate, listingStatusLabel } from './Index';

type ListingDetail = {
    id: number;
    title: string;
    status: string;
    external_id: string | null;
    last_synced_at: string | null;
    updated_at: string | null;
    product: {
        id: number;
        name: string;
    };
    marketplaceAccount: {
        id: number;
        name: string;
        marketplace: string;
    };
    variants: Array<{
        id: number;
        sku: string;
        external_id: string | null;
        status: string;
        productVariant: {
            id: number;
            name: string | null;
            sku: string;
        };
    }>;
    payloads: Array<{
        id: number;
        payload_type: string;
        payload: Record<string, unknown>;
        generated_at: string | null;
    }>;
    statusHistories: Array<{
        id: number;
        old_status: string | null;
        new_status: string;
        message: string | null;
        created_at: string | null;
    }>;
    errors: Array<{
        id: number;
        code: string;
        message: string;
        field: string | null;
        resolved_at: string | null;
        variant_sku: string | null;
    }>;
};

type ListingsShowProps = PageProps<{
    listing: ListingDetail;
}>;

export default function Show() {
    const { listing } = usePage<ListingsShowProps>().props;
    const preview = listing.payloads[0];

    return (
        <AdminLayout title="İlan Detayı">
            <Head title="İlan Detayı" />

            <div className="mb-6">
                <Link className="text-sm font-medium text-blue-700 hover:underline" href="/listings">
                    İlanlara dön
                </Link>
            </div>

            <div className="space-y-6">
                <section className="rounded-lg border border-slate-200 bg-white p-6">
                    <div className="grid gap-4 md:grid-cols-2">
                        <Info label="Ürün" value={listing.product.name} />
                        <Info
                            label="Pazaryeri hesabı"
                            value={`${listing.marketplaceAccount.name} - ${listing.marketplaceAccount.marketplace}`}
                        />
                        <Info label="Durum" value={listingStatusLabel(listing.status)} />
                        <Info label="Son güncelleme" value={formatDate(listing.last_synced_at ?? listing.updated_at)} />
                    </div>
                </section>

                <section className="overflow-hidden rounded-lg border border-slate-200 bg-white">
                    <SectionTitle title="Varyantlar" />
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm">
                            <thead className="border-y border-slate-200 bg-slate-50 text-slate-600">
                                <tr>
                                    <th className="px-4 py-3 font-medium">SKU</th>
                                    <th className="px-4 py-3 font-medium">Varyant</th>
                                    <th className="px-4 py-3 font-medium">Durum</th>
                                    <th className="px-4 py-3 font-medium">Dış ID</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {listing.variants.map((variant) => (
                                    <tr key={variant.id}>
                                        <td className="px-4 py-3 font-medium text-slate-950">{variant.sku}</td>
                                        <td className="px-4 py-3 text-slate-700">{variant.productVariant.name ?? '-'}</td>
                                        <td className="px-4 py-3 text-slate-700">{listingStatusLabel(variant.status)}</td>
                                        <td className="px-4 py-3 text-slate-700">{variant.external_id ?? '-'}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </section>

                <section className="rounded-lg border border-slate-200 bg-white">
                    <SectionTitle title="Payload Önizleme" />
                    <pre className="overflow-x-auto p-4 text-sm text-slate-800">
                        {preview ? JSON.stringify(preview.payload, null, 2) : 'Payload kaydı yok.'}
                    </pre>
                </section>

                <section className="rounded-lg border border-slate-200 bg-white">
                    <SectionTitle title="Durum Geçmişi" />
                    <div className="divide-y divide-slate-100">
                        {listing.statusHistories.length > 0 ? (
                            listing.statusHistories.map((history) => (
                                <div key={history.id} className="px-4 py-3 text-sm">
                                    <p className="font-medium text-slate-950">
                                        {history.old_status ? listingStatusLabel(history.old_status) : '-'} →{' '}
                                        {listingStatusLabel(history.new_status)}
                                    </p>
                                    <p className="mt-1 text-slate-600">{history.message ?? '-'}</p>
                                    <p className="mt-1 text-xs text-slate-500">{formatDate(history.created_at)}</p>
                                </div>
                            ))
                        ) : (
                            <p className="px-4 py-6 text-sm text-slate-600">Durum geçmişi yok.</p>
                        )}
                    </div>
                </section>

                <section className="rounded-lg border border-slate-200 bg-white">
                    <SectionTitle title="Hatalar" />
                    <div className="divide-y divide-slate-100">
                        {listing.errors.length > 0 ? (
                            listing.errors.map((error) => (
                                <div key={error.id} className="px-4 py-3 text-sm">
                                    <p className="font-medium text-slate-950">{error.code}</p>
                                    <p className="mt-1 text-slate-600">{error.message}</p>
                                    <p className="mt-1 text-xs text-slate-500">
                                        {error.field ?? '-'} / {error.variant_sku ?? '-'}
                                    </p>
                                </div>
                            ))
                        ) : (
                            <p className="px-4 py-6 text-sm text-slate-600">Hata kaydı yok.</p>
                        )}
                    </div>
                </section>
            </div>
        </AdminLayout>
    );
}

type InfoProps = {
    label: string;
    value: string;
};

function Info({ label, value }: InfoProps) {
    return (
        <div>
            <p className="text-xs font-medium uppercase text-slate-500">{label}</p>
            <p className="mt-1 text-sm font-medium text-slate-950">{value}</p>
        </div>
    );
}

function SectionTitle({ title }: { title: string }) {
    return <h2 className="px-4 py-3 text-base font-semibold text-slate-950">{title}</h2>;
}
