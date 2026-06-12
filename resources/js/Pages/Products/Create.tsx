import { Head, Link, usePage } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import type { PageProps } from '../../types';
import ProductForm from './ProductForm';

type Option = {
    id: number;
    name: string;
};

type StatusOption = {
    value: string;
    label: string;
};

type ProductsCreateProps = PageProps<{
    categories: Option[];
    brands: Option[];
    statuses: StatusOption[];
}>;

export default function Create() {
    const { categories, brands, statuses } = usePage<ProductsCreateProps>().props;

    return (
        <AdminLayout title="Ürün Oluştur">
            <Head title="Ürün Oluştur" />

            <div className="mb-6">
                <Link className="text-sm font-medium text-blue-700 hover:underline" href="/products">
                    Ürünlere dön
                </Link>
            </div>

            <ProductForm
                action="/products"
                method="post"
                submitLabel="Ürünü Kaydet"
                categories={categories}
                brands={brands}
                statuses={statuses}
            />
        </AdminLayout>
    );
}
