import { Head, usePage } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import type { PageProps } from '../../types';
import MappingPage from './MappingPage';
import type { MappingOption, MappingRow, StatusOption } from './MappingPage';

type BrandMapping = {
    id: number;
    status: string;
    notes: string | null;
    brand: MappingOption;
    marketplaceBrand: MappingOption & {
        marketplace: string;
    };
};

type BrandsProps = PageProps<{
    mappings: BrandMapping[];
    brands: MappingOption[];
    marketplaceBrands: MappingOption[];
    statuses: StatusOption[];
}>;

export default function Brands() {
    const { mappings, brands, marketplaceBrands, statuses } = usePage<BrandsProps>().props;

    return (
        <AdminLayout title="Marka Eşleştirmeleri">
            <Head title="Marka Eşleştirmeleri" />
            <MappingPage
                description="Tenant markalarınızı pazaryeri marka metadata kayıtlarıyla eşleştirin."
                emptyTitle="Henüz marka eşleştirmesi yok"
                localLabel="Marka"
                marketplaceLabel="Pazaryeri markası"
                localField="brand_id"
                marketplaceField="marketplace_brand_id"
                storeAction="/marketplace-mappings/brands"
                updateAction={(id) => `/marketplace-mappings/brands/${id}`}
                mappings={mappings.map((mapping): MappingRow => ({
                    id: mapping.id,
                    status: mapping.status,
                    notes: mapping.notes,
                    local: mapping.brand,
                    marketplaceTarget: mapping.marketplaceBrand,
                }))}
                localOptions={brands}
                marketplaceOptions={marketplaceBrands}
                statuses={statuses}
            />
        </AdminLayout>
    );
}
