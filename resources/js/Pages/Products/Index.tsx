import { Head, Link, usePage } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import type { PageProps } from '../../types';

type ProductRow = {
    id: number;
    name: string;
    status: string;
    sku: string | null;
    sale_price: string | null;
    currency: string | null;
    quantity: number | null;
    created_at: string | null;
    category: {
        id: number;
        name: string;
    } | null;
    brand: {
        id: number;
        name: string;
    } | null;
};

type ProductsIndexProps = PageProps<{
    products: ProductRow[];
}>;

export default function Index() {
    const { products } = usePage<ProductsIndexProps>().props;

    return (
        <AdminLayout title="Ürünler">
            <Head title="Ürünler" />

            <div className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <p className="max-w-2xl text-sm text-slate-600">
                    Çalışma alanınıza ait ürünleri, SKU seviyesindeki fiyat ve stok bilgileriyle yönetin.
                </p>
                <Link
                    className="inline-flex items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                    href="/products/create"
                >
                    Yeni Ürün Oluştur
                </Link>
            </div>

            <section className="overflow-hidden rounded-lg border border-slate-200 bg-white">
                {products.length > 0 ? (
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm">
                            <thead className="border-b border-slate-200 bg-slate-50 text-slate-600">
                                <tr>
                                    <th className="px-4 py-3 font-medium">Ürün</th>
                                    <th className="px-4 py-3 font-medium">SKU</th>
                                    <th className="px-4 py-3 font-medium">Kategori</th>
                                    <th className="px-4 py-3 font-medium">Marka</th>
                                    <th className="px-4 py-3 font-medium">Fiyat</th>
                                    <th className="px-4 py-3 font-medium">Stok</th>
                                    <th className="px-4 py-3 font-medium">Durum</th>
                                    <th className="px-4 py-3 font-medium">İşlem</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {products.map((product) => (
                                    <tr key={product.id}>
                                        <td className="px-4 py-3 font-medium text-slate-950">{product.name}</td>
                                        <td className="px-4 py-3 text-slate-700">{product.sku ?? '-'}</td>
                                        <td className="px-4 py-3 text-slate-700">{product.category?.name ?? '-'}</td>
                                        <td className="px-4 py-3 text-slate-700">{product.brand?.name ?? '-'}</td>
                                        <td className="px-4 py-3 text-slate-700">
                                            {formatPrice(product.sale_price, product.currency)}
                                        </td>
                                        <td className="px-4 py-3 text-slate-700">{product.quantity ?? '-'}</td>
                                        <td className="px-4 py-3 text-slate-700">{productStatusLabel(product.status)}</td>
                                        <td className="px-4 py-3">
                                            <Link
                                                className="text-sm font-medium text-blue-700 hover:underline"
                                                href={`/products/${product.id}/edit`}
                                            >
                                                Düzenle
                                            </Link>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                ) : (
                    <div className="px-6 py-10 text-center">
                        <h2 className="text-lg font-semibold text-slate-950">Henüz ürün yok</h2>
                        <p className="mt-2 text-sm text-slate-600">
                            İlk ürününüzü oluşturarak katalog yönetimine başlayabilirsiniz.
                        </p>
                        <Link
                            className="mt-5 inline-flex items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                            href="/products/create"
                        >
                            Ürün Oluştur
                        </Link>
                    </div>
                )}
            </section>
        </AdminLayout>
    );
}

function productStatusLabel(status: string) {
    const labels: Record<string, string> = {
        draft: 'Taslak',
        active: 'Aktif',
        inactive: 'Pasif',
        archived: 'Arşiv',
    };

    return labels[status] ?? status;
}

function formatPrice(value: string | null, currency: string | null) {
    if (!value || !currency) {
        return '-';
    }

    return `${value} ${currency}`;
}
