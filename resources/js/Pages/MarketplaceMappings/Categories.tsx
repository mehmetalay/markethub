import { Head, usePage } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import type { PageProps } from '../../types';
import MappingPage from './MappingPage';
import type { MappingOption, MappingRow, StatusOption } from './MappingPage';

type CategoryMapping = {
    id: number;
    status: string;
    notes: string | null;
    category: MappingOption;
    marketplaceCategory: MappingOption & {
        path: string | null;
        marketplace: string;
    };
};

type CategoriesProps = PageProps<{
    mappings: CategoryMapping[];
    categories: MappingOption[];
    marketplaceCategories: MappingOption[];
    statuses: StatusOption[];
}>;

export default function Categories() {
    const { mappings, categories, marketplaceCategories, statuses } = usePage<CategoriesProps>().props;

    return (
        <AdminLayout title="Kategori Eşleştirmeleri">
            <Head title="Kategori Eşleştirmeleri" />
            <MappingPage
                description="Tenant kataloğunuzdaki kategorileri pazaryeri kategori metadata kayıtlarıyla eşleştirin."
                emptyTitle="Henüz kategori eşleştirmesi yok"
                localLabel="Kategori"
                marketplaceLabel="Pazaryeri kategorisi"
                localField="category_id"
                marketplaceField="marketplace_category_id"
                storeAction="/marketplace-mappings/categories"
                updateAction={(id) => `/marketplace-mappings/categories/${id}`}
                mappings={mappings.map((mapping): MappingRow => ({
                    id: mapping.id,
                    status: mapping.status,
                    notes: mapping.notes,
                    local: mapping.category,
                    marketplaceTarget: mapping.marketplaceCategory,
                }))}
                localOptions={categories}
                marketplaceOptions={marketplaceCategories}
                statuses={statuses}
            />
        </AdminLayout>
    );
}
