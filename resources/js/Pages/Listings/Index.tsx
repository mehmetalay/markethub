import { Head, Link, usePage } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import type { PageProps } from '../../types';

type ListingRow = {
    id: number;
    title: string;
    status: string;
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
};

type ListingsIndexProps = PageProps<{
    listings: ListingRow[];
}>;

export default function Index() {
    const { listings } = usePage<ListingsIndexProps>().props;

    return (
        <AdminLayout title="İlanlar">
            <Head title="İlanlar" />

            <div className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <p className="max-w-2xl text-sm text-slate-600">
                    Ürünlerinizi pazaryeri hesaplarıyla eşleştiren ilan taslaklarını yönetin.
                </p>
                <Link
                    className="inline-flex items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                    href="/listings/create"
                >
                    Yeni İlan Oluştur
                </Link>
            </div>

            <section className="overflow-hidden rounded-lg border border-slate-200 bg-white">
                {listings.length > 0 ? (
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm">
                            <thead className="border-b border-slate-200 bg-slate-50 text-slate-600">
                                <tr>
                                    <th className="px-4 py-3 font-medium">Ürün</th>
                                    <th className="px-4 py-3 font-medium">Pazaryeri hesabı</th>
                                    <th className="px-4 py-3 font-medium">Durum</th>
                                    <th className="px-4 py-3 font-medium">Son güncelleme</th>
                                    <th className="px-4 py-3 font-medium">İşlem</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {listings.map((listing) => (
                                    <tr key={listing.id}>
                                        <td className="px-4 py-3 font-medium text-slate-950">{listing.product.name}</td>
                                        <td className="px-4 py-3 text-slate-700">
                                            {listing.marketplaceAccount.name} - {listing.marketplaceAccount.marketplace}
                                        </td>
                                        <td className="px-4 py-3 text-slate-700">{listingStatusLabel(listing.status)}</td>
                                        <td className="px-4 py-3 text-slate-700">
                                            {formatDate(listing.last_synced_at ?? listing.updated_at)}
                                        </td>
                                        <td className="px-4 py-3">
                                            <Link
                                                className="text-sm font-medium text-blue-700 hover:underline"
                                                href={`/listings/${listing.id}`}
                                            >
                                                Detay
                                            </Link>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                ) : (
                    <div className="px-6 py-10 text-center">
                        <h2 className="text-lg font-semibold text-slate-950">Henüz ilan yok</h2>
                        <p className="mt-2 text-sm text-slate-600">İlk ilan taslağınızı oluşturarak başlayabilirsiniz.</p>
                        <Link
                            className="mt-5 inline-flex items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                            href="/listings/create"
                        >
                            İlan Oluştur
                        </Link>
                    </div>
                )}
            </section>
        </AdminLayout>
    );
}

export function listingStatusLabel(status: string) {
    const labels: Record<string, string> = {
        draft: 'Taslak',
        ready: 'Hazır',
        pending: 'Beklemede',
        published: 'Yayında',
        failed: 'Hatalı',
        archived: 'Arşiv',
    };

    return labels[status] ?? status;
}

export function formatDate(value: string | null) {
    if (!value) {
        return '-';
    }

    return new Intl.DateTimeFormat('tr-TR', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}
