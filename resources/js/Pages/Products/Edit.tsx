import { Head, Link, usePage } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import type { PageProps } from '../../types';
import ProductForm from './ProductForm';
import type { ProductFormValues } from './ProductForm';

type Option = {
    id: number;
    name: string;
};

type StatusOption = {
    value: string;
    label: string;
};

type ProductsEditProps = PageProps<{
    categories: Option[];
    brands: Option[];
    statuses: StatusOption[];
    product: ProductFormValues & { id: number };
}>;

export default function Edit() {
    const { categories, brands, statuses, product } = usePage<ProductsEditProps>().props;

    return (
        <AdminLayout title="Ürün Düzenle">
            <Head title="Ürün Düzenle" />

            <div className="mb-6">
                <Link className="text-sm font-medium text-blue-700 hover:underline" href="/products">
                    Ürünlere dön
                </Link>
            </div>

            <ProductForm
                action={`/products/${product.id}`}
                method="put"
                submitLabel="Değişiklikleri Kaydet"
                categories={categories}
                brands={brands}
                statuses={statuses}
                product={product}
            />
        </AdminLayout>
    );
}
