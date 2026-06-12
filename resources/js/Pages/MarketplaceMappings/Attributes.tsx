import { Head, usePage } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import type { PageProps } from '../../types';
import MappingPage from './MappingPage';
import type { MappingOption, MappingRow, StatusOption } from './MappingPage';

type AttributeMapping = {
    id: number;
    status: string;
    notes: string | null;
    attribute: MappingOption;
    marketplaceAttribute: MappingOption & {
        marketplace: string;
    };
};

type AttributesProps = PageProps<{
    mappings: AttributeMapping[];
    attributes: MappingOption[];
    marketplaceAttributes: MappingOption[];
    statuses: StatusOption[];
}>;

export default function Attributes() {
    const { mappings, attributes, marketplaceAttributes, statuses } = usePage<AttributesProps>().props;

    return (
        <AdminLayout title="Attribute Eşleştirmeleri">
            <Head title="Attribute Eşleştirmeleri" />
            <MappingPage
                description="Tenant attribute kayıtlarınızı pazaryeri attribute metadata kayıtlarıyla eşleştirin."
                emptyTitle="Henüz attribute eşleştirmesi yok"
                localLabel="Attribute"
                marketplaceLabel="Pazaryeri attribute kaydı"
                localField="attribute_id"
                marketplaceField="marketplace_attribute_id"
                storeAction="/marketplace-mappings/attributes"
                updateAction={(id) => `/marketplace-mappings/attributes/${id}`}
                mappings={mappings.map((mapping): MappingRow => ({
                    id: mapping.id,
                    status: mapping.status,
                    notes: mapping.notes,
                    local: mapping.attribute,
                    marketplaceTarget: mapping.marketplaceAttribute,
                }))}
                localOptions={attributes}
                marketplaceOptions={marketplaceAttributes}
                statuses={statuses}
            />
        </AdminLayout>
    );
}
